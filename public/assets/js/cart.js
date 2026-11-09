(function (root) {
    'use strict';
    function createClient({request, render, notify, initialVersion = -1}) {
        let serial = 0;
        let version = initialVersion;
        let busy = false;
        function apply(cart, ticket) {
            if (ticket !== serial || cart.version < version) return;
            version = cart.version;
            render(cart);
        }
        return {
            async load(url) {
                const ticket = ++serial;
                try { apply(await request(url), ticket); }
                catch (error) { if (ticket === serial) notify('error', error.message); }
            },
            async mutate(url, data) {
                if (busy) return false;
                busy = true;
                const ticket = ++serial;
                try {
                    apply(await request(url, data), ticket);
                    notify('success', 'تم تحديث السلة / Cart updated');
                    return true;
                } catch (error) { notify('error', error.message); return false; }
                finally { busy = false; }
            }
        };
    }
    function mount(options) {
        const money = value => Number(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const container = document.querySelector('.minicart-content-items');
        function render(cart) {
            container.replaceChildren();
            if (!cart.count) {
                const empty = document.createElement('li');
                empty.textContent = 'السلة فارغة / Your cart is empty';
                container.append(empty);
            }
            cart.items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'media';
                const imageLink = document.createElement('a');
                imageLink.href = item.url;
                imageLink.className = 'media-left';
                const image = document.createElement('img');
                image.src = item.image_url;
                image.alt = item.title;
                image.className = 'media-object';
                imageLink.append(image);
                const details = document.createElement('div');
                details.className = 'minicart-content-details media-body';
                const title = document.createElement('h3');
                const link = document.createElement('a');
                link.href = item.url;
                link.textContent = item.title;
                title.append(link);
                const price = document.createElement('span');
                price.className = 'minicart-content-price';
                price.textContent = `${item.quantity} x ${money(item.price)} EGP`;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'minicart-content-remove btn-remove';
                remove.textContent = '×';
                remove.setAttribute('aria-label', 'حذف ' + item.title);
                remove.addEventListener('click', () => mutate(options.remove, {id: item.id}, remove));
                details.append(title, price, remove);
                li.append(imageLink, details);
                container.append(li);
            });
            document.querySelector('.cart-count').textContent = `Cart (${cart.count})`;
            document.querySelector('.minicart-content-total h3').textContent = `Subtotal: ${money(cart.total)} EGP`;
        }
        const client = createClient({render, initialVersion: options.initial.version, notify: (icon, text) => {
            if (root.Swal) { root.Swal.fire({icon, text}); return; }
            let status = document.getElementById('cart-status');
            if (!status) {
                status = document.createElement('div');
                status.id = 'cart-status';
                status.setAttribute('role', 'status');
                document.body.prepend(status);
            }
            status.className = icon === 'error' ? 'alert alert-danger' : 'alert alert-success';
            status.textContent = text;
        },
            request: async (url, data) => {
                const response = await fetch(url, {
                    method: data ? 'POST' : 'GET', credentials: 'same-origin', cache: 'no-store',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': options.csrf},
                    ...(data ? {body: JSON.stringify(data)} : {})
                });
                const body = await response.json();
                if (!response.ok) throw new Error(Object.values(body.errors || {}).flat().join(' ') || body.message || 'تعذر تحديث السلة');
                if (!Array.isArray(body.items) || !Number.isInteger(body.version)) throw new Error('استجابة السلة غير صالحة');
                return body;
            }
        });
        async function mutate(url, data, button) {
            button.disabled = true;
            try {
                const accepted = await client.mutate(url, data);
                if (accepted && document.querySelector('[data-cart-page]')) root.location.reload();
            } finally { button.disabled = false; }
        }
        document.querySelectorAll('.cart-remove').forEach(button => button.addEventListener('click', () => {
            mutate(options.remove, {id: button.dataset.id}, button);
        }));
        render(options.initial);
        client.load(options.api);
        document.querySelectorAll('.btn-add-cart').forEach(button => button.addEventListener('click', () => {
            const input = document.getElementById('prod_count');
            if (!input.reportValidity()) return;
            mutate(options.add, {id: document.getElementById('prod_id').value, quantity: Number(input.value)}, button);
        }));
    }
    root.VineCart = {createClient, mount};
})(typeof window === 'undefined' ? globalThis : window);
