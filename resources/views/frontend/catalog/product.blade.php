@extends('layouts.site')

@section('content')
    <!-- Breadcrumb -->
    <div class="ws-breadcrumb">
        <div class="container">
            <ol class="breadcrumb">
                <li><a href="{{ url('shop') }}">Shop</a></li>
                <li><a
                        href="{{ url('shop/' . $product->subcategory->category->id) }}">{{ $product->subcategory->category->arabic_name }}</a>
                </li>
                <li><a href="{{ url('shop/' . $product->subcategory->category->id) }}">{{ $product->subcategory->arabic_name }}</a>
                </li>
            </ol>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Product Content -->
    <div class="container ws-page-container">
        <div class="row">

            <!-- Product Image Carousel -->
            <div class="col-sm-7">
                <div id="ws-products-carousel" class="owl-carousel">
                    @foreach ($product->images as $img)
                        <div class="item">
                            <img src="{{ $img->full_image_url }}" class="img-responsive" alt="{{ $product->arabic_name }} / {{ $product->name }}"
                                @if($loop->first) fetchpriority="high" @else loading="lazy" decoding="async" @endif>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Product Information -->
            <div class="col-sm-5">
                <div class="ws-product-content">
                    <header>
                        <!-- Item Category -->
                        <div class="ws-item-category">{{ $product->subcategory->category->arabic_name }} -
                            {{ $product->subcategory->arabic_name }}</div>

                        <!-- Title -->
                        <h1 class="ws-item-title">{{ $product->arabic_name }}</h1>

                        <div class="ws-separator"></div>

                        <!-- Price -->
                        @if ($product->offer)
                            <div class="ws-item-price"><del>{{ number_format($product->price, 2) }}EGP</del>
                                <ins>{{ number_format($product->final_price, 2) }}EGP</ins>
                            </div>
                        @else
                            <div class="ws-item-price"><ins>{{ number_format($product->final_price, 2) }}EGP</ins>
                            </div>
                        @endif
                        <!-- Quantity -->
                        <div class="ws-product-quantity">
                            <button type="button" onclick="subCount()" class="minus" aria-label="تقليل الكمية">-</button>
                            <input id="prod_count" type="number" value="1" min="1" max="99" step="1" inputmode="numeric" size="4" aria-label="الكمية">
                            <button type="button" onclick="addCount()" class="plus" aria-label="زيادة الكمية">+</button>
                        </div>
                    </header>

                    <div class="ws-product-details">
                        {{ $product->arabic_desc }}<br><br>{{ $product->desc }}
                    </div>
                    <input type="hidden" id="prod_id" value="{{$product->id}}" />
                    <!-- Button -->
                    <button type="button" class="btn ws-btn-fullwidth btn-add-cart">Add To Cart</button><br><br><br>
                </div>
            </div>
        </div>
    </div>
    <!-- Product Content -->

    <!-- Products Description -->
    <div class="ws-products-description-content text-center">

        <!-- Item -->
        <div class="ws-product-description">
            <h3>Share</h3>
            <div class="ws-product-social-icon">
                <a href="https://wa.me/?text='Check this Vine Arts Product!\n{{ url('product/' . $product->id) }}'"
                    target="_blank"><i class="fa fa-whatsapp"></i></a>
            </div>
        </div>

        <!-- Item -->
        <div class="ws-product-description">
            <h3>Material</h3>
            <p>{{ $product->material }}</p>
        </div>

        <!-- Item -->
        <div class="ws-product-description">
            <h3>Dimensions</h3>
            <p>{{ $product->dimensions }}</p>
        </div>

        <!-- Item -->
        <div class="ws-product-description">
            <h3>Topics</h3>
            <p>{{ $product->handled_topics }}</p>
        </div>

    </div>
    <!-- End Products Description -->

    <!-- Related Post -->
    <div class="ws-related-section">
        <div class="container">

            <!-- Title -->
            <div class="ws-related-title">
                <h3>Related Products</h3>
            </div>

            @foreach ($related_products as $prod)
                <div class="col-sm-4">
                    <!-- Item -->
                    <div class="ws-works-item">
                        <a href="{{ url('product/' . $prod->id) }}">
                            <!-- Image -->
                            <figure>
                                <img src="{{ $prod->main_image_url }}" alt="{{ $prod->arabic_name }} / {{ $prod->name }}" class="img-responsive" loading="lazy" decoding="async">
                            </figure>
                            <div class="ws-works-caption text-center">
                                <!-- Item Category -->
                                <div class="ws-item-category">{{ $prod->subcategory->category->arabic_name }} -
                                    {{ $prod->subcategory->arabic_name }}</div>

                                <!-- Title -->
                                <h3 class="ws-item-title">{{ $prod->arabic_name }}</h3>

                                <div class="ws-item-separator"></div>

                                <!-- Price -->
                                <div class="ws-item-price">{{ number_format($prod->final_price, 2) }}EGP</div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- End Related Post -->

    <script>
        function addCount() {
            $('#prod_count').val(Math.min(+$('#prod_count').val() + 1, 99))
        }

        function subCount() {
            $('#prod_count').val(Math.max(+$('#prod_count').val() - 1, 1))
        }
    </script>
@endsection
