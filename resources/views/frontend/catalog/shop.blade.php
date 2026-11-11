@extends('layouts.site')

@section('content')
    <!-- Page Parallax Header -->
    <div class="ws-parallax-header parallax-window" data-parallax="scroll"
        data-image-src="{{ $site_info->landing_image }}">
        <div class="ws-overlay">
            <div class="ws-parallax-caption">
                <div class="ws-parallax-holder">
                    <h1>Our Products</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Parallax Header -->

    <!-- Page Content -->
    <div class="container ws-page-container">
        <div class="row">
            <div class="ws-shop-page">
                <!-- Categories Nav -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" @class(['active' => !isset($category_id)])>
                        <a href="#all" aria-controls="all" role="tab" data-toggle="tab">
                            All ({{ $products->count() }})</a>
                    </li>
                    @foreach ($categories as $catg)
                        <li role="presentation" @class(['active' => isset($category_id) && $category_id == $catg->id])>
                            <a href="#{{ str_replace(' ', '-', $catg->name) }}" aria-controls="prints" role="tab"
                                data-toggle="tab">{{ $catg->arabic_name }}
                                ({{ $catg->products->count() }})
                            </a>
                        </li>
                    @endforeach
                </ul>

                <!-- Categories Content -->
                <div class="tab-content">
                    <!-- All -->
                    <div role="tabpanel" @class(['tab-pane fade in', 'active' => !isset($category_id)]) id="all">
                        @foreach ($products as $prod)
                            <!-- Item -->
                            <div class="col-sm-6 col-md-4 ws-works-item">
                                <a href="{{ url("product/$prod->id") }}">
                                    <div class="ws-item-offer">
                                        <!-- Image -->
                                        <figure>
                                            <img src="{{ $prod->main_image_url }}" alt="Alternative Text"
                                                class="img-responsive">
                                        </figure>
                                    </div>

                                    <div class="ws-works-caption text-center">
                                        <!-- Item Category -->
                                        <div class="ws-item-category">{{ $prod->subcategory->arabic_name }}</div>

                                        <!-- Title -->
                                        <h3 class="ws-item-title">{{ $prod->arabic_name }}</h3>

                                        <div class="ws-item-separator"></div>

                                        <!-- Price -->
                                        @if ($prod->offer)
                                            <div class="ws-item-price"><del>{{ number_format($prod->price) }}EGP</del>
                                                <ins>{{ number_format($prod->price - $prod->offer) }}EGP</ins>
                                            </div>
                                        @else
                                            <div class="ws-item-price"><ins>{{ number_format($prod->price) }}EGP</ins>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @foreach ($categories as $catg)
                        <div role="tabpanel" @class([
                            'tab-pane fade in',
                            'active' => isset($category_id) && $category_id == $catg->id,
                        ]) id="{{ str_replace(' ', '-', $catg->name) }}">
                            @foreach ($catg->products as $prod)
                                <div class="col-sm-6 col-md-4 ws-works-item">
                                    <a href="{{ url("product/$prod->id") }}">
                                        <div class="ws-item-offer">
                                            <!-- Image -->
                                            <figure>
                                                <img src="{{ $prod->main_image_url }}" alt="Alternative Text"
                                                    class="img-responsive">
                                            </figure>
                                        </div>

                                        <div class="ws-works-caption text-center">
                                            <!-- Item Category -->
                                            <div class="ws-item-category">{{ $prod->subcategory->arabic_name }}</div>

                                            <!-- Title -->
                                            <h3 class="ws-item-title">{{ $prod->arabic_name }}</h3>

                                            <div class="ws-item-separator"></div>

                                            <!-- Price -->
                                            @if ($prod->offer)
                                                <div class="ws-item-price"><del>{{ number_format($prod->price) }}EGP</del>
                                                    <ins>{{ number_format($prod->price - $prod->offer) }}EGP</ins>
                                                </div>
                                            @else
                                                <div class="ws-item-price"><ins>{{ number_format($prod->price) }}EGP</ins>
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
        <!-- End Page Content -->
    @endsection
