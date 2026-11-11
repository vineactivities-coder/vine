const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const {JSDOM} = require('jsdom');
const source = fs.readFileSync('public/assets/js/cart.js', 'utf8');
const deferred = () => { let resolve, reject; const promise = new Promise((a,b) => {resolve=a; reject=b;}); return {promise,resolve,reject}; };
const state = (version, count = 1) => ({version, count, total: count * 1200.25, items: count ? [{id:1, quantity:count, price:1200.25, title:'<img src=x onerror=alert(1)>', url:'/product/1', image_url:'/placeholder.jpg'}] : []});
function setup(request) {
    const context = {globalThis:{}};
    vm.runInNewContext(source, context);
    const renders=[], notices=[];
    const client = context.globalThis.VineCart.createClient({request, render: c => renders.push(c), notify:(...v) => notices.push(v)});
    return {client,renders,notices};
}
test('renders accepted add response before success notification', async () => {
    const pending=deferred(), h=setup(() => pending.promise);
    const job=h.client.mutate('/add', {});
    assert.equal(h.notices.length,0);
    pending.resolve(state(1,2)); await job;
    assert.equal(h.renders[0].count,2); assert.equal(h.notices[0][0],'success');
});
test('repeated click while request pending only mutates once', async () => {
    let requests=0; const pending=deferred(); const h=setup(() => {requests++; return pending.promise;});
    const job=h.client.mutate('/add', {});
    assert.equal(await h.client.mutate('/add', {}),false); assert.equal(requests,1);
    pending.resolve(state(1)); await job;
});
test('failed mutation keeps previous state and reports error', async () => {
    const h=setup(async () => {throw new Error('Quantity rejected');});
    assert.equal(await h.client.mutate('/add', {}),false);
    assert.equal(h.renders.length,0); assert.deepEqual(h.notices,[['error','Quantity rejected']]);
});
test('initial read cannot overwrite a subsequent accepted mutation', async () => {
    const old=deferred(); const h=setup(url => url==='/read' ? old.promise : Promise.resolve(state(2,3)));
    const read=h.client.load('/read'); await h.client.mutate('/add', {});
    old.resolve(state(1)); await read;
    assert.deepEqual(h.renders.map(c=>c.count),[3]);
});
test('older server version cannot overwrite newer state', async () => {
    let version=4; const h=setup(async () => state(version));
    await h.client.load('/read'); version=3; await h.client.load('/read');
    assert.deepEqual(h.renders.map(c=>c.version),[4]);
});
test('stale read error does not report failure after success', async () => {
    const old=deferred(); const h=setup(url => url==='/read' ? old.promise : Promise.resolve(state(2)));
    const read=h.client.load('/read'); await h.client.mutate('/add', {});
    old.reject(new Error('offline')); await read;
    assert.equal(h.notices.length,1); assert.equal(h.notices[0][0],'success');
});
test('a new mutation is allowed after network failure', async () => {
    let calls=0; const h=setup(async () => {if(++calls===1) throw new Error('offline'); return state(1);});
    await h.client.mutate('/add', {}); assert.equal(await h.client.mutate('/add', {}),true);
});
function dom(fetch) {
    const d=new JSDOM('<meta name="csrf-token" content="test"><ul class="minicart-content-items"></ul><div class="minicart-content-total"><h3></h3></div><a class="cart-count"></a><input id="prod_id" value="1"><input id="prod_count" type="number" min="1" max="99" step="1" value="1"><button class="btn-add-cart"></button>',{runScripts:'outside-only',url:'https://vine.invalid'});
    const notices=[]; d.window.Swal={fire: v=>notices.push(v)}; d.window.fetch=fetch;
    d.window.eval(source); d.window.VineCart.mount({initial:state(0),api:'/api',add:'/add',remove:'/remove',csrf:'token'});
    return {d,notices};
}
const flush=()=>new Promise(resolve=>setImmediate(resolve));
test('DOM uses safe title text and actual product links', async () => {
    const {d}=dom(async()=>({ok:true,json:async()=>state(1)})); await flush();
    const li=d.window.document.querySelector('.media');
    assert.equal(li.querySelector('h3').textContent,'<img src=x onerror=alert(1)>');
    assert.equal(li.querySelectorAll('img').length,1);
    assert.equal(li.querySelector('h3 a').pathname,'/product/1');
    assert.equal(d.window.document.querySelector('.cart-count').textContent,'Cart (1)'); d.window.close();
});
test('DOM ignores HTTP rejection and reenables button', async () => {
    const {d,notices}=dom(async url=>url==='/api'?{ok:true,json:async()=>state(1)}:{ok:false,json:async()=>({errors:{quantity:['Rejected quantity']}})});
    await flush(); const b=d.window.document.querySelector('button.btn-add-cart'); b.click(); assert.equal(b.disabled,true); await flush();
    assert.equal(b.disabled,false); assert.equal(notices[0].icon,'error');
    assert.equal(d.window.document.querySelector('.cart-count').textContent,'Cart (1)'); d.window.close();
});
test('DOM removal renders canonical empty state', async () => {
    const {d}=dom(async url=>({ok:true,json:async()=>url==='/api'?state(1):state(2,0)})); await flush();
    d.window.document.querySelector('.btn-remove').click(); await flush();
    assert.equal(d.window.document.querySelector('.cart-count').textContent,'Cart (0)');
    assert.match(d.window.document.querySelector('.minicart-content-items').textContent,/Your cart is empty/); d.window.close();
});
test('invalid fractional quantity does not make an add request', async () => {
    let calls=0; const {d}=dom(async()=>{calls++;return{ok:true,json:async()=>state(1)}}); await flush();
    d.window.document.querySelector('#prod_count').value='1.5'; d.window.document.querySelector('.btn-add-cart').click(); await flush();
    assert.equal(calls,1); d.window.close();
});
test('initial rendered version protects against an older API response', async () => {
    const context = {globalThis:{}}; vm.runInNewContext(source, context);
    const renders=[];
    const client=context.globalThis.VineCart.createClient({initialVersion:5,request:async()=>state(4),render:c=>renders.push(c),notify:()=>{}});
    await client.load('/read'); assert.equal(renders.length,0);
});
