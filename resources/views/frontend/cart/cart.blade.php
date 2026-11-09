@extends('layouts.site')

@section('content')
    <!-- Page Content -->
    <div class="container ws-page-container">
        <div class="row">

            <!-- Cart Content -->
            <div class="ws-cart-page" data-cart-page>
                <div class="col-sm-8">
                    <div class="ws-mycart-content">
                        <form method="POST" action="{{ $updateCartUrl }}">
                        @csrf
                        <div class="table-responsive"><table class="table">
                            <thead>
                                <tr>
                                    <th class="cart-item-head">&nbsp;</th>
                                    <th class="cart-item-head">Item</th>
                                    <th class="cart-item-head">Price</th>
                                    <th class="cart-item-head">Quantity</th>
                                    <th class="cart-item-head">Total</th>
                                    <th class="cart-item-head">&nbsp;</th>
                                </tr>
                            </thead>

                            <tbody>

                     
                                @foreach ($cart->items as $item)
                                    <tr class="cart-item">
                                        <td class="cart-item-cell cart-item-thumb">
                                            <a href="{{ url('product/' . $item->id) }}">
                                                <img src="{{ $item->image_url }}" class="img-responsive"
                                                    alt="Alternative Text">
                                            </a>
                                        </td>
                                        <td class="cart-item-cell cart-item-title">
                                            <h3><a href="{{ url('product/' . $item->id) }}">{{ $item->title }}</a></h3>
                                        </td>
                                        <td class="cart-item-cell cart-item-price">
                                            <span class="amount">{{ number_format($item->price, 2) }}</span>
                                        </td>
                                        <td class="cart-item-cell cart-item-quantity">
                                            <input type="number" value="{{ $item->quantity }}"
                                                id="prod-{{ $item->id }}-quantity" name="quantity[]" min="0" max="99" step="1" required >
                                            <input type="hidden" name="product[]" value="{{ $item->id }}" />
                                        </td>
                                        <td class="cart-item-cell cart-item-subtotal">
                                            <span
                                                class="amount">{{ number_format($item->line_total, 2) }}</span>
                                        </td>
                                        <td class="cart-item-cell cart-item-remove">
                                            <button type="button" class="cart-remove btn btn-default btn-sm" data-id="{{ $item->id }}" aria-label="حذف {{ $item->title }}">×</button>
                                        </td>
                                    </tr>
                                @endforeach


                                <tr>
                                    <td colspan="6">

                                        <!-- Update Cart -->
                                        <div class="ws-update-cart">
                                            <button class="btn ws-small-btn-black" type="submit" @disabled(! $cart->count)>Update Cart</button>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table></div>
                        </form>
                    </div>
                </div>

                <!-- Cart Total -->
                <div class="col-sm-4">
                    <div class="ws-mycart-total">
                        <h2>Cart Totals</h2>
                        <table>
                            <tbody>
                                <tr class="cart-subtotal">
                                    <th>Subtotal</th>
                                    <td><span class="amount">{{number_format($cart->subtotal,2)}}EGP</span></td>
                                </tr>

                                <tr class="shipping">
                                    <th>Discount</th>
                                    <td><span class="amount">{{number_format($cart->discount ?? 0, 2)}}EGP</span></td>
                                </tr>

                                <tr class="order-total">
                                    <th>Total</th>
                                    <td><strong><span class="amount">{{number_format($cart->total, 2)}}EGP</span></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        @if ($cart->count)<a class="btn ws-btn-fullwidth" href="{{ route('checkout') }}">Check Out</a>@else<p>السلة فارغة / Your cart is empty</p>@endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End Page Content -->
@endsection

@section('js_content')
    <script>
        function removeRow(rowID) {
            $("#" + rowID).remove();
        }
    </script>
@endsection
