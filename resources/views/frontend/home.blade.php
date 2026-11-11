@extends('layouts.site')

@section('content')
    <!-- Hero Content -->
    <div id="ws-hero-fullscreen" class="rev_slider">
        <ul>
            <li data-transition="fade" data-easein="Power4.easeInOut" data-easeout="Power4.easeInOut" data-masterspeed="2000">
                <!-- Background Image -->
                <img src="{{ $site_info->landing_image }}" alt="Alternative Text" data-bgposition="center center"
                    data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="10">

                <!-- Background Overlay -->
                <div class="tp-caption" data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
                    data-y="['middle','middle','middle','middle']" data-voffset="['0','0','0','0']" data-width="full"
                    data-height="full" data-whitespace="nowrap" data-transform_idle="o:1;"
                    data-transform_in="opacity:0;s:1500;e:Power3.easeInOut;" data-transform_out="s:300;s:300;"
                    data-start="750" data-basealign="slide" data-responsive_offset="on" data-responsive="off"
                    style="z-index: 5;background-color:rgba(0, 0, 0, 0.40);border-color:rgba(0, 0, 0, 0.50);">
                </div>

                <!-- Layer -->
                <div class="tp-caption ws-hero-title" data-x="['center','center','center','center']"
                    data-hoffset="['0','0','0','0']" data-y="['middle','middle','middle','middle']"
                    data-voffset="['0','0','0','1']"
                    data-transform_in="y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;s:1500;e:Power3.easeInOut;"
                    data-mask_in="x:0px;y:0px;" data-mask_out="x:0;y:0;" data-start="1000" data-responsive_offset="on"
                    style="z-index: 6;">
                    <h1 style="padding-bottom:0px">أَنَا الْكَرْمَةُ الْحَقِيقِيَّةُ وَأَبِي الْكَرَّامُ </h1>
                    <h1 style="padding-bottom:0px">(يو 15: 1)</h1>
                </div>

                <!-- Layer -->
                <div class="tp-caption ws-hero-description" data-x="['center','center','center','center']"
                    data-hoffset="['0','0','0','0']" data-y="['middle','middle','middle','middle']"
                    data-voffset="['-72','-72','-72','-48']"
                    data-transform_in="y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;s:1500;e:Power3.easeInOut;"
                    data-mask_in="x:0px;y:0px;" data-mask_out="x:0;y:0;" data-start="1000" data-responsive_offset="on"
                    style="z-index: 7;">

                </div>

                <!-- Button -->
                <div class="tp-caption" data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
                    data-y="['middle','middle','middle','middle']" data-voffset="['92','92','92','76']"
                    data-transform_in="y:50px;opacity:0;s:1500;e:Power4.easeInOut;" data-start="1000"
                    data-responsive_offset="on" data-responsive="off" style="z-index: 8;"><a class="btn ws-big-btn"
                        href="{{ url('shop') }}">View Shop</a>
                </div>
            </li>
        </ul>
        <div class="tp-static-layers"></div>
        <div class="tp-bannertimer tp-bottom" style="visibility: hidden !important;"></div>
    </div>
    <!-- Hero Content -->

    <!-- About Section Start -->
    <section class="ws-about-section">
        <div class="container">
            <div class="row">

                <!-- Description -->
                <div class="ws-about-content clearfix">
                    <div class="col-sm-8 col-sm-offset-2">
                        {{-- <h3>Where Art and Faith Shape Young Minds</h3>
                        <div class="ws-separator"></div> --}}
                        <p>Our materials are designed to engage students in meaningful ways, combining creativity with
                            thoughtful content that encourages exploration and personal growth. By making learning visually
                            stimulating and interactive, we aim to cultivate curiosity and critical thinking. Each product
                            fosters a deeper connection to the subjects explored, helping young learners develop skills,
                            values, and insights that stay with them well beyond the classroom.</p>
                        <div class="ws-separator"></div>
                        <p>تم تصميم اعمالنا لإشراك المخدومين بطرق هادفة، حيث تجمع بين الإبداع والمحتوى الفكري الذي يشجع على
                            الاستكشاف والنمو الشخصي. من خلال جعل التعلم محفزًا بصريًا وتفاعليًا، نهدف إلى تنمية الفضول
                            والتفكير النقدي. يعزز كل منتج ارتباطًا أعمق بالموضوعات المستكشفة، مما يساعد المتعلمين الصغار
                            على تطوير المهارات والقيم والرؤى التي تبقى معهم إلى ما بعد الفصل الدراسي.</p>
                    </div>
                </div>

                <!-- Featured Collections -->
                <div class="ws-featured-collections clearfix">
                    @foreach ($slides as $slide)
                        <!-- Item -->
                        <div class="col-sm-{{ 12 / $slides->count() }} featured-collections-item">
                            <a @if ($slide->SLID_BTN_URL) href="{{ $slide->SLID_BTN_URL }}" @endif>
                                <div class="thumbnail">
                                    <img src="{{ $slide->image_url }}">
                                    @if ($slide->SLID_TITL)
                                        <div class="ws-overlay">
                                            <div class="caption">
                                                <h3>{{ $slide->SLID_TITL }}</h3>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach




                </div>

            </div>
        </div>
    </section>
    <!-- About Section End -->

    <!-- New Arrivals Section -->
    <section class="ws-arrivals-section">

        <div class="ws-works-title clearfix">
            <div class="col-sm-12">
                <h3>New Arrivals</h3>
                <div class="ws-separator"></div>
            </div>
        </div>

        <div id="ws-items-carousel">
            @foreach ($new_arrivals as $new_prod)
                <!-- Item -->
                <div class="ws-works-item" data-sr='wait 0.1s, ease-in 20px'>
                    <a href="{{ url('product/' . $new_prod->id) }}">
                        <div class="ws-item-offer">
                            <!-- Image -->
                            <figure>
                                <img src="{{ $new_prod->main_image_url }}" alt="{{ $new_prod->name }}"
                                    class="img-responsive">
                            </figure>
                        </div>

                        <div class="ws-works-caption text-center">
                            <!-- Item Category -->
                            <div class="ws-item-category">{{ $new_prod->subcategory->category->arabic_name }} -
                                {{ $new_prod->subcategory->arabic_name }}</div>

                            <!-- Title -->
                            <h3 class="ws-item-title">{{ $new_prod->arabic_name }}</h3>

                            <div class="ws-item-separator"></div>

                            <!-- Price -->
                            <div class="ws-item-price">
                                @if ($new_prod->offer)
                                    <del>{{ $new_prod->price }}EGP</del>
                                @endif
                                <ins>{{ $new_prod->price - $new_prod->offer }}EGP</ins>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach

        </div>
    </section>
    <!-- End New Arrivals Section -->

    <!-- Locations Section Start -->
    @if($locations && $locations->count() > 0)
    <section class="ws-works-section">
        <div class="container">
            <div class="row">
                <div class="ws-works-title">
                    <div class="col-sm-12">
                        <h3>Find Us / اين تجدنا</h3>
                        <div class="ws-separator"></div>
                    </div>
                </div>

                @foreach($locations as $location)
                <div class="col-sm-6 col-md-4 ws-works-item" data-sr='wait 0.1s, ease-in 20px'>
                    <a href="{{ $location->location_url }}" target="_blank">
                        <div class="ws-item-offer">
                            <!-- Image -->
                            <figure>
                                <img src="{{ $location->image_url }}" alt="{{ $location->title }}" class="img-responsive">
                            </figure>
                        </div>

                        <div class="ws-works-caption text-center">
                            <!-- Title -->
                            <h3 class="ws-item-title">{{ $location->title }}</h3>

                            <div class="ws-item-separator"></div>

                            <!-- Address -->
                            @if($location->address)
                            <div class="ws-item-category">{{ $location->address }}</div>
                            @endif

                            <!-- Telephone -->
                            @if($location->telephone)
                            <div class="ws-item-price">
                                <ins>{{ $location->telephone }}</ins>
                            </div>
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <!-- Locations Section End -->

    <!-- Work Collection Start  -->
    <section class="ws-works-section">
        <div class="container">
            <div class="row">

                <div class="ws-works-title">
                    <div class="col-sm-12">
                        <h3>On Sale Products</h3>
                        <div class="ws-separator"></div>
                    </div>
                </div>

                @foreach ($on_sale_prods as $prod)
                    <!-- Item -->
                    <div class="col-sm-6 col-md-4 ws-works-item" data-sr='wait 0.1s, ease-in 20px'>
                        <a href="{{ url('product/' . $prod->id) }}">
                            <div class="ws-item-offer">
                                <!-- Image -->
                                <figure>
                                    <img src="{{ $prod->main_image_url }}" alt="{{ $prod->name }}"
                                        class="img-responsive">
                                </figure>
                                <!-- Sale Caption -->
                                @if ($prod->offer)
                                    <div class="ws-item-sale">
                                        <span>Sale</span>
                                    </div>
                                @endif
                            </div>

                            <div class="ws-works-caption text-center">
                                <!-- Item Category -->
                                <div class="ws-item-category">{{ $prod->subcategory->category->arabic_name }} -
                                    {{ $prod->subcategory->arabic_name }}</div>

                                <!-- Title -->
                                <h3 class="ws-item-title">{{ $prod->arabic_name }}</h3>

                                <div class="ws-item-separator"></div>

                                <!-- Price -->
                                <div class="ws-item-price">
                                    @if ($prod->offer)
                                        <del>{{ $prod->price }}EGP</del>
                                    @endif
                                    <ins>{{ $prod->price - $prod->offer }}EGP</ins>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
    <!-- Work Collection End  -->
    <!-- Call to Action Section -->
    <section class="ws-call-section" style="background-image: {{ $site_info->footer_large }}">
        <div class="ws-overlay">
            <div class="ws-parallax-caption">
                <div class="ws-parallax-holder">
                    <div class="col-sm-6 col-sm-offset-3">
                        <p>هل تبحث عن المزيد من المواد الملهمة ومناقشة أفكار جديدة؟ قم بزيارة صفحتنا لتصفح مجموعتنا
                            الكاملة! ابق على اطلاع على أحدث المنتجات والعروض والنصائح للاستفادة القصوى من أدواتنا
                            التعليمية الفنية.</p>
                        <div class="ws-separator"></div>
                        <p>Looking for more inspiring materials and discuss new ideas? Visit our page to browse our
                            full collection! Stay updated on the latest products, offers, and tips for making the
                            most of our artistic learning tools.</p>

                        <div class="ws-call-btn">
                            <a href="https://www.fb.com/{{ $site_info['WBST_FB'] }}">Facebook page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @isset($showOrderSubmitted)
        <script>
            $('document').ready(function() {

                alert("Thank you for submiting the order. We will call you to confirm order details")
                // return Swal.fire({
                //     text: "Thank you for submiting the order. We will call you to confirm order details",
                //     icon: "success",
                //     showCancelButton: true,
                // })
            });
        </script>
    @endisset
    <!-- End Call to Action Section -->
@endsection
