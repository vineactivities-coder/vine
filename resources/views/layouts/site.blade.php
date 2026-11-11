<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang=""> <!--<![endif]-->

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
    <title>Vine Activities</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-signin-client_id"
        content="980156877622-i08tl355o8bd0aks6tuq1a3uper1latf.apps.googleusercontent.com">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('assets/img/favicon.ico') }}">

    <!-- Meta Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2050922418710174');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=2050922418710174&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-66R6DYJHWT');
    </script>
    <!-- Jquery -->
    <script src="{{ url('assets/js/plugins/jquery-1.11.3.min.js') }}"></script>

    <!-- CSS Styles -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/main.css') }}">

    <!-- Animate CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/animate.min.css') }}">

    <!-- Revolution Slider -->
    <link rel="stylesheet" href="{{ url('assets/js/plugins/revolution/css/settings.css') }}">
    <link rel="stylesheet" href="{{ url('assets/js/plugins/revolution/css/layers.css') }}">
    <link rel="stylesheet" href="{{ url('assets/js/plugins/revolution/css/navigation.css') }}">
    <!-- Owl Carousel -->
    <link rel="stylesheet" href="{{ url('assets/js/plugins/owl-carousel/owl.carousel.css') }}">

    <!-- Google Web Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Lato:400,100,300,700,900' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,700,900' rel='stylesheet' type='text/css'>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

    <!-- Slider Revolution -->
    <script src="{{ url('assets/js/plugins/revolution/js/jquery.themepunch.tools.min.js') }}"></script>
    <script src="{{ url('assets/js/plugins/revolution/js/jquery.themepunch.revolution.min.js') }}"></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <!-- Slider Revolution 5.0 Extensions
            (Load Extensions only on Local File Systems !
            The following part can be removed on Server for On Demand Loading) -->
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.actions.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.carousel.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.kenburn.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.layeranimation.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.migration.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.navigation.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.parallax.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.slideanims.min.js') }}"></script>
    <script type="text/javascript"
        src="{{ url('assets/js/plugins/revolution/js/extensions/revolution.extension.video.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-66R6DYJHWT"></script>

</head>

<body>
    <!--[if lt IE 8]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    <!-- Loader Start -->
    <div id="preloader">
        <div class="preloader-container">
            <div class="sk-folding-cube">
                <div class="sk-cube1 sk-cube"></div>
                <div class="sk-cube2 sk-cube"></div>
                <div class="sk-cube4 sk-cube"></div>
                <div class="sk-cube3 sk-cube"></div>
            </div>
        </div>
    </div>
    <!-- End Loader Start -->

    <script src="https://accounts.google.com/gsi/client" async></script>
    <div id="g_id_onload" data-client_id="980156877622-i08tl355o8bd0aks6tuq1a3uper1latf.apps.googleusercontent.com"
        data-login_uri="{{ url('googlelogin') }}" data-use_fedcm_for_prompt="true" data-auto_prompt="true">
    </div>
    </div>

    <!-- Top Bar Start -->
    <div class="ws-topbar">
        <div class="pull-left">
            <div class="ws-topbar-message hidden-xs">
                {{-- <p>Free Shipping on orders over <span>50$ in USA</span></p>  --}}
            </div>
        </div>

        <div class="pull-right">

            <!-- Shop Menu -->
            <ul class="ws-shop-menu">

                <!-- Account -->
                <li class="ws-shop-account">
                    @if ($is_logged)
                        <a class="btn btn-sm">Hello {{ $logged_user->name }}!</a>
                        <a href="{{ url('logout') }}" class="btn btn-sm">Logout</a>
                    @else
                        <a href="{{ url('login') }}" class="btn btn-sm">Login</a>
                    @endif
                </li>

                <!-- Cart -->
                <li class="ws-shop-cart">
                    <a href="{{ url('cart') }}" class="btn btn-sm cart-count">Cart (2)</a>

                    <!-- Cart Popover -->
                    <div class="ws-shop-minicart">
                        <div class="minicart-content">

                            <!-- Added Items -->
                            <ul class="minicart-content-items clearfix">


                            </ul>

                            <!-- Subtotal -->
                            <div class="minicart-content-total">
                                <h3>Subtotal: {{ $cart->total }}</h3>
                            </div>

                            <!-- Checkout -->
                            <div class="ws-shop-menu-checkout">
                                <div class="ws-shop-viewcart pull-left">
                                    <a href="{{ url('cart') }}" class="btn btn-sm">View Cart</a>
                                </div>
                                <div class="ws-shop-checkout pull-right">
                                    <a href="{{ url('checkout') }}" class="btn btn-sm">Checkout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Cart Popover -->

                </li>
            </ul>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Header Start -->
    @isset($is_home)
        <header class="ws-header ws-header-transparent">
        @else
            <header class="ws-header-static">
            @endisset

            <!-- Navbar -->
            <nav class="navbar ws-navbar navbar-default">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <!-- Logo -->
                    <div class="ws-logo ws-center">
                        <a href="{{ url('home') }}">
                            <img src="{{ url('assets/images/vineLogo.png') }}" alt="Alternative Text"
                                class="img-responsive">
                        </a>
                    </div>

                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav navbar-left">
                            <li class="dropdown">
                                <a href="{{ url('home') }}">Home </a>

                            </li>
                            <li><a href="{{ url('shop') }}">Shop</a></li>

                        </ul>

                        <ul class="nav navbar-nav navbar-right">

                            <li class="dropdown">
                                <a href="#x" class="dropdown-toggle" data-toggle="dropdown"
                                    data-hover="dropdown" data-animations="fadeIn">Categories <span
                                        class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    @foreach ($subcategories as $subcatg)
                                        <li><a href="{{ url("shop/{$subcatg->category->id}") }}">{{ $subcatg->category->arabic_name }}
                                                - {{ $subcatg->arabic_name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>

                            <li><a href="{{ url('contact') }}">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <!-- End Header -->

        @yield('content')


        <!-- Instagram Content -->
        <section id="ws-instagram-section" style="margin-top: 20px">
            <div class="container">
                <div class="row vertical-align">

                    <!-- Instagram Information -->
                    <div class="col-sm-3">
                        <div class="ws-instagram-header">
                            <h3>Instagram</h3>


                            <a href="https://instagram.com/{{ $site_info['WBST_INST'] }}" target="_blank"
                                class="ws-instagram-link"> {{ '@' . $site_info['WBST_INST'] }}</a>
                            <p>Follow us on Instagram for our latest ideas and products</p>
                            <br>
                            <h3>Whatsapp</h3>

                            <a href="https://wa.me/+201200165007" target="_blank" class="ws-instagram-link"> <i
                                    class="fa fa-whatsapp fa-lg"></i> +201200165007 </a>
                            <p>Chat with us</p>
                        </div>
                    </div>

                    <!-- Instagram Item -->
                    <div class="col-sm-3 ws-instagram-item" data-sr='wait 0.1s, ease-in 20px'>
                        <img src="{{ $site_info->footer1_url }}"
                            alt="قداسه البابا تواضرس  يبدي اعجابه الشديد بالخط الزمني بعد مراجعته"
                            class="img-responsive"
                            title="قداسه البابا تواضرس  يبدي اعجابه الشديد بالخط الزمني بعد مراجعته">
                    </div>

                    <!-- Instagram Item -->
                    <div class="col-sm-3 ws-instagram-item" data-sr='wait 0.3s, ease-in 20px'>
                        <img src="{{ $site_info->footer2_url }}"
                            alt="نيافه الانبا موسي اسقف الشباب يقوم بشرح الخط الزمني في معرض الكتاب القبطي"
                            class="img-responsive"
                            title="نيافه الانبا موسي اسقف الشباب يقوم بشرح الخط الزمني في معرض الكتاب القبطي">
                    </div>

                    <!-- Instagram Item -->
                    <div class="col-sm-3 ws-instagram-item" data-sr='wait 0.5s, ease-in 20px'>
                        <img src="{{ $site_info->footer3_url }}"
                            alt="القمص تادرس يعقوب ملطي يقوم بمراجعه الخط الزمني للكتاب المقدس و يقوم بعرضه علي قدسه القمص مارك عزيز"
                            class="img-responsive"
                            title="القمص تادرس يعقوب ملطي يقوم بمراجعه الخط الزمني للكتاب المقدس و يقوم بعرضه علي قدسه القمص مارك عزيز">
                    </div>
                </div>
            </div>
        </section>
        <!-- End Instagram Content -->


        <!-- Footer Start -->
        <footer class="ws-footer">
            <div class="container">
                <div class="row">

                    <!-- About -->
                    <div class="col-sm-6 ws-footer-col">
                        <h3>About Us</h3>
                        <div class="ws-footer-separator"></div>
                        <div class="ws-footer-about">

                            <p>مرحباً بكم في Vine Activities! نحن نقدم مواد فنية تعليمية مبتكرة لمدارس الأحد القبطية.
                                منتجاتنا تجمع بين الفن والتعليم لإلهام الفهم وإشعال الإبداع وتعميق المعرفة. انضموا إلينا
                                في رحلة التعلم من خلال الإبداع!</p>

                            <p>Welcome to Vine Activities! We provide innovative artistic educational materials for
                                Coptic Sunday schools. Our products combine art and education to inspire understanding,
                                spark creativity and deepen knowledge. Join us on a journey of learning through
                                creativity!</p>
                        </div>
                    </div>

                    <!-- Support Links -->
                    <div class="col-sm-2 ws-footer-col">
                        <h3>Support</h3>
                        <div class="ws-footer-separator"></div>
                        <ul>
                            <li><a href="{{ url('delivery') }}">Shipping Policy</a></li>
                            <li><a href="{{ url('payment') }}">Privacy Policy</a></li>
                            <li><a href="{{ url('contact') }}">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Social Links -->
                    <div class="col-sm-2 ws-footer-col">
                        <h3>Socials</h3>
                        <div class="ws-footer-separator"></div>
                        <ul class="ws-footer-social">
                            <li><a href="https://wa.me/+201200165007" target="_blank"><i
                                        class="fa fa-whatsapp fa-lg"></i> WhatsApp</a></li>
                            <li><a href="{{ $site_info->fb_url }}" target="_blank"><i
                                        class="fa fa-facebook-square fa-lg"></i> Facebook</a></li>
                            <li><a href="{{ $site_info->insta_url }}" target="_blank"><i
                                        class="fa fa-instagram fa-lg"></i> Instagram</a></li>
                        </ul>
                    </div>

                    <!-- Shop -->
                    <div class="col-sm-2 ws-footer-col">
                        <h3>Shop</h3>
                        <div class="ws-footer-separator"></div>
                        <ul>
                            @foreach ($categories as $catg)
                                <li><a href="{{ url('shop/' . $catg->id) }}">{{ $catg->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </footer>
        <!-- Footer End -->

        <!-- Footer Bar Start -->
        <div class="ws-footer-bar">
            <div class="container">

                <!-- Copyright -->
                <div class="pull-left">
                    <p>Handcrafted with love &copy; 2024 All rights reserved.</p>
                </div>

                <!-- Payments -->

            </div>
        </div>
        <!-- Footer Bar End -->

        <!-- Sricpts -->
        <script src="{{ url('assets/js/plugins/bootstrap.min.js') }}"></script>
        <script src="{{ url('assets/js/plugins/modernizr-2.8.3-respond-1.4.2.min.js') }}"></script>
        <script src="{{ url('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
        <script src="{{ url('assets/js/plugins/parallax.min.js') }}"></script>
        <script src="{{ url('assets/js/plugins/scrollReveal.min.js') }}"></script>
        <script src="{{ url('assets/js/plugins/bootstrap-dropdownhover.min.js') }}"></script>
        <script src="{{ url('assets/js/main.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetchCartData();

                function fetchCartData() {
                    fetch('{{ $apiCart }}')
                        .then(response => response.json())
                        .then(cart => {
                            populateCart(cart);
                        })
                        .catch(error => {
                            console.error('Error fetching cart data:', error);
                        });
                }

                function populateCart(cart) {
                    const minicartContentItems = document.querySelector('.minicart-content-items');
                    const minicartTotal = document.querySelector('.minicart-content-total h3');
                    const minicartCount = document.querySelector('.cart-count');
                    minicartContentItems.innerHTML = ''; // Clear current items

                    cart.items.forEach(item => {
                        minicartContentItems.innerHTML += `


                <li class="media" data-id="${item.id}">
                    <div class="media-left">
                        <a href="#">
                            <img src="${item.image_url}" class="media-object" alt="Alternative Text">
                        </a>
                    </div>
                    <div class="minicart-content-details media-body">
                        <h3><a href="#">${item.quantity > 1 ? `${item.quantity} x ` : ''}${item.title}</a></h3>
                        <span class="minicart-content-price" >${item.quantity} x ${item.price}EGP</span>               
                        <span class="minicart-content-remove btn-remove" data-id="${item.id}"><i class="fa fa-times"></i></span> 
                    </div>
                </li>`;
                    });

                    // Update subtotal
                    minicartTotal.textContent = `Subtotal: ${cart.total}`;
                    minicartCount.textContent = `Cart (${cart.count})`;

                    // Attach event listeners
                    attachEventListeners();

                }

                function attachEventListeners() {


                    document.querySelectorAll('.btn-remove').forEach(button => {
                        button.addEventListener('click', function() {
                            const id = this.dataset.id;
                            updateCart('{{ $removeFromCartUrl }}', {
                                id: id
                            });
                        });
                    });
                }

                function updateCart(url, data) {
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(cart => {
                            fetchCartData();

                        })
                        .catch(error => {
                            console.error('Error updating cart:', error);
                        });


                }

                document.querySelectorAll('.btn-add-cart').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = document.querySelector(`#prod_id`).value;
                        const quantityInput = document.querySelector(`#prod_count`);
                        const quantity = parseInt(quantityInput.value) || 1;
                        updateCart('{{ $addToCartUrl }}', {
                            id: id,
                            quantity: quantity
                        });

                        Swal.fire({
                            text: "Cart Updated",
                            icon: "success",
                        })
                    });
                });
            });
        </script>
</body>

</html>
