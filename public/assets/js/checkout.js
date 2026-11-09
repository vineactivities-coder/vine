(function (root) {
    const calculateTotal = (products, shipping) => (Number(products) + Number(shipping)) / 100;
    root.VineCheckout = {calculateTotal};
    if (typeof document === 'undefined') return;
    const area = document.getElementById('selectedArea');
    const total = document.getElementById('calculatedTotal');
    const money = amount => amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' EGP';
    function update() {
        const shipping = Number(area.selectedOptions[0]?.dataset.shippingCents || 0);
        document.getElementById('shippingCheckout').textContent = area.value ? money(shipping / 100) : '—';
        total.textContent = area.value ? money(calculateTotal(total.dataset.productsCents, shipping)) : '—';
    }
    area.addEventListener('change', update);
    update();
    const form = document.getElementById('checkoutForm');
    form.addEventListener('submit', () => { form.querySelectorAll('button').forEach(button => button.disabled = true); });
    document.getElementById('whatsappOrder').addEventListener('click', async function () {
        if (!form.reportValidity()) return;
        this.disabled = true;
        const popup = root.open('about:blank', '_blank');
        if (popup) popup.opener = null;
        try {
            const response = await fetch('/order/whatsapp/submit', {method: 'POST', body: new FormData(form), headers: {'Accept': 'application/json'}});
            const body = await response.json();
            if (!response.ok) throw new Error(Object.values(body.errors || {}).flat().join(' ') || body.message);
            if (popup) popup.location.href = body.url;
            else root.location.assign(body.url);
        } catch (error) {
            if (popup) popup.close();
            const text = error.message || 'تعذر تجهيز الطلب';
            if (root.Swal) root.Swal.fire({icon: 'error', text});
            else root.alert(text);
        }
        finally { this.disabled = false; }
    });
})(typeof window === 'undefined' ? globalThis : window);
