@extends('layouts.site')

@section('content')
    <!-- Page Content -->
    <div class="container ws-page-container">
        <div class="row">

            <!-- Cart Content -->
            <div class="ws-cart-page">
                <div class="col-sm-8">
                    <div class="ws-mycart-content">
                        <table class="table">
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
                                <form method="POST">
                                    @csrf
                     
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
                                                id="prod-{{ $item->id }}-quantity" name=quantity[] >
                                            <input type="hidden" name="product[]" value="{{ $item->id }}" />
                                        </td>
                                        <td class="cart-item-cell cart-item-subtotal">
                                            <span
                                                class="amount">{{ number_format($item->price * $item->quantity, 2) }}</span>
                                        </td>
                                        <td class="cart-item-cell cart-item-remove"
                                            onclick="setQuantity({{ $item->id }}, '0')">
                                            <span><i class="fa fa-times"></i></span>
                                        </td>
                                    </tr>
                                @endforeach
                                <script>
                                    function setQuantity(id, quantity) {
                                        $('#prod-' + id + '-quantity').val(quantity)
                                    }
                                </script>

                                <tr>
                                    <td colspan="6">

                                        <!-- Update Cart -->
                                        <div class="ws-update-cart">
                                            <button class="btn ws-small-btn-black" type="submit">Update Cart</button>
                                        </div>
                                    </td>
                                </tr>
                                </form>
                            </tbody>
                        </table>
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
                                    <td><span class="amount">{{number_format($cart->total + $cart->discount ?? 0,2)}}EGP</span></td>
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
                        <a class="btn ws-btn-fullwidth" href="{{url('checkout')}}">Check Out</a>
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
