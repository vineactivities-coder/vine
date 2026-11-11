@extends('layouts.site')

@section('content')
    <div class="ws-checkout-content clearfix">
        <div class=container>
            <form method="POST" action="{{ $submitOrderUrl }}" id="checkoutForm">

                @csrf

                <!-- Cart Content -->
                <div class="col-sm-8 ">

                    <!-- Billing Details -->
                    <div class="ws-checkout-billing">
                        <h3>Order Details</h3>

                        <!-- Form Inputs -->
                        <form class="form-inline">
                            <input type="hidden" name=user
                                value="@isset($logged_user) {{ $logged_user->id }} @endisset " />

                            <input type="hidden" name=guest
                                value="@isset($logged_user) 2 @else 1 @endisset" />

                            <input type="hidden" name=area id="selectedAreaID"
                                value=" {{ isset($logged_user) ? $logged_user->area_id : old('area') ?? 1 }}" />

                            <!-- Name -->
                            <div class="col-no-p ws-checkout-input col-sm-12">
                                <label>Name <span> * </span></label><br>
                                <input type="text" name=guestName
                                    @isset($logged_user) value="{{ $logged_user->name }}" disabled @else value="{{ old('name') }}" @endisset
                                    required />

                                @error('guestName')
                                    <small class="text-danger">
                                        {{ $errors->first('guestName') }}
                                    </small>
                                @enderror
                            </div>



                            <!-- Email -->


                            <div class="col-no-p ws-checkout-input col-sm-12">
                                <label>Phone <span> * </span></label><br>
                                <input type="tel" name=phone required
                                    @isset($logged_user) value="{{ $logged_user->mobile }}" @else value="{{ old('phone') }}" @endif />
                                    @error('phone')
                                    <small class="text-danger">
                                        {{ $errors->first('phone') }}
                                    </small>
                                @enderror
                        </div>


                        <!-- Adress -->
                        <div class="col-no-p ws-checkout-input col-sm-12">
                            <label>Address <span> * </span></label><br>
                            <textarea type="text" rows=3 name=address required >@if (isset($logged_user)) {{ $logged_user->address }} @else {{ old('address') }} @endif</textarea>
                                @error('address')
                                    <small class="text-danger">
                                        {{ $errors->first('address') }}
                                    </small>
                                @enderror
                            </div>




                            <div class="col-no-p ws-checkout-input col-sm-12">
                                <label>Area <span> * </span></label><br>
                                <select onchange="loadShipping()" id="selectedArea" required>
                                    @foreach ($areas as $area)
                                        <option value='{{ $area->id }}%%{{ number_format($area->rate, 2) }}'
                                            @selected(isset($logged_user) && $logged_user->area_id == $area->id)>
                                            {{ $area->name }} - {{ $area->arabic_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('area')
                                    <small class="text-danger">
                                        {{ $errors->first('area') }}
                                    </small>
                                @enderror
                            </div>


                            <!-- Order Notes -->
                            <div class="col-no-p ws-checkout-input col-sm-12">
                                <label>Order Notes</label><br>
                                <textarea placeholder="Notes about your order, e.g. special notes for delivery." rows="2" cols="5"
                                    name=note> {{ old('note') }} </textarea>
                            </div>


                    </div>
                </div>

                <!-- Cart Total -->
                <div class="col-sm-4">
                    <div class="ws-checkout-order">
                        <h2>Your Order</h2>
                        <!-- Order Table -->
                        <table>

                            <!-- Title -->
                            <thead>
                                <tr>
                                    <th class="ws-order-product">Product</th>
                                    <th class="ws-order-total">Total</th>
                                </tr>
                            </thead>

                            <!-- Products -->
                            <tbody>
                                @foreach ($cart->items as $item)
                                    <tr>
                                        <th>{{ $item->title }} x {{ $item->quantity }}</th>
                                        <td><span>{{ number_format($item->quantity * $item->price, 2) }} EGP</span></td>
                                    </tr>
                                @endforeach


                                <tr>
                                    <th>Subtotal</th>
                                    <td><span>{{ number_format($cart->total, 2) }} EGP</span></td>
                                </tr>
                                <tr>
                                    <th>Shipping</th>
                                    <td><span id="shippingCheckout"> </span></td>
                                </tr>
                                <tr class="ws-shipping-total">
                                    <th>Total</th>
                                    <td><span id="calculatedTotal"></span></td>
                                </tr>
                            </tbody>

                        </table>

                        <!-- Payment Metod -->
                        <div class="ws-shipping-payment">
                            <div class="radio">
                                <label><input type="radio" name="optradio" checked>Cash</label>
                            </div>

                        </div>
                        <button class="btn ws-btn-fullwidth" type="submit">Confirm Order</button>
                        <button class="btn ws-btn-fullwidth" style="margin-top: 5px" type="button"
                            onclick="sendWhatsappMsg()">
                            <i class="fa fa-whatsapp"></i>
                            Order using Whatsapp</button>
                    </div>
                </div>

            </form>
        </div>
    </div>


    <script>
        function loadShipping() {

            var selectedArea = $('#selectedArea').val().split('%%')
            var shippingPrice = parseFloat(selectedArea[1]);
            var selectedAreaID = selectedArea[0];

            $('#selectedAreaID').val(selectedAreaID)

            $('#shippingCheckout').html(shippingPrice.toLocaleString(undefined, {
                maximumFractionDigits: 2,
                minimumFractionDigits: 2,
            }) + " EGP")
            var cartTotal = parseFloat({{ number_format($cart->total, 2) }})

            $('#calculatedTotal').html((cartTotal + shippingPrice).toLocaleString(
                undefined, {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 2,
                }
            ) + " EGP")

        }

        document.addEventListener('DOMContentLoaded', function() {
            loadShipping()
        })

        function sendWhatsappMsg() {
            if (!$('#checkoutForm')[0].checkValidity()) {
                $('#checkoutForm')[0].reportValidity();
                return;
            }
            var formData = $('#checkoutForm').serialize();

            $.ajax({
                url: "{{ url('order/whatsapp/submit') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    window.open(response.url, '_blank');

                },
                error: function(xhr, status, error) {
                    alert('An error occurred while submitting the order via WhatsApp.');
                }
            });
        }
    </script>
@endsection
