@extends('layouts.site')
@section('content')
<div class="container ws-page-container" data-cart-page>
    <form method="POST" action="{{ route('confirmOrder') }}" id="checkoutForm">
        @csrf
        <div class="row">
            <div class="col-sm-8 ws-checkout-billing">
                <h3>Order Details / تفاصيل الطلب</h3>
                <div class="ws-checkout-input"><label for="guestName">Name / الاسم *</label>
                    <input id="guestName" name="guestName" value="{{ old('guestName', $logged_user?->name) }}" maxlength="255" required @readonly($is_logged)></div>
                <div class="ws-checkout-input"><label for="phone">Phone / الهاتف *</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $logged_user?->mobile) }}" maxlength="30" required></div>
                <div class="ws-checkout-input"><label for="address">Address / العنوان *</label>
                    <textarea id="address" name="address" maxlength="255" required>{{ old('address', $logged_user?->address) }}</textarea></div>
                <div class="ws-checkout-input"><label for="selectedArea">Area / منطقة الشحن *</label>
                    <select id="selectedArea" name="area" required>
                        <option value="">اختر منطقة الشحن</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" data-shipping-cents="{{ \App\Services\Cart::cents($area->rate) }}" @selected((string) old('area', $logged_user?->area_id) === (string) $area->id)>{{ $area->name }} - {{ $area->arabic_name }}</option>
                        @endforeach
                    </select></div>
                <div class="ws-checkout-input"><label for="note">Order Notes / ملاحظات</label>
                    <textarea id="note" name="note" maxlength="255">{{ old('note') }}</textarea></div>
            </div>
            <div class="col-sm-4 ws-checkout-order">
                <h2>Your Order / طلبك</h2>
                <table class="table"><tbody>
                    @foreach ($cart->items as $item)
                    <tr><th>{{ $item->title }} x {{ $item->quantity }}</th><td>{{ number_format($item->line_total, 2) }} EGP</td></tr>
                    @endforeach
                    <tr><th>Subtotal / قبل الخصم</th><td>{{ number_format($cart->subtotal, 2) }} EGP</td></tr>
                    <tr><th>Discount / الخصم</th><td>{{ number_format($cart->discount, 2) }} EGP</td></tr>
                    <tr><th>Products / المنتجات</th><td>{{ number_format($cart->total, 2) }} EGP</td></tr>
                    <tr><th>Shipping / الشحن</th><td id="shippingCheckout">—</td></tr>
                    <tr><th>Total / الإجمالي</th><td id="calculatedTotal" data-products-cents="{{ \App\Services\Cart::cents($cart->total) }}">—</td></tr>
                </tbody></table>
                <p>Cash / الدفع نقدًا</p>
                <button class="btn ws-btn-fullwidth" type="submit">Confirm Order / تأكيد الطلب</button>
                <button class="btn ws-btn-fullwidth" type="button" id="whatsappOrder">Order using WhatsApp</button>
            </div>
        </div>
    </form>
</div>
@endsection
@section('js_content')
<script src="{{ asset('assets/js/checkout.js') }}"></script>
@endsection
