@extends('layouts.site')

@section('content')
    <div class="ws-parallax-header parallax-window" data-parallax="scroll"
        data-image-src="{{ $site_info->landing_image }}">
        <div class="ws-overlay">
            <div class="ws-parallax-caption">
                <div class="ws-parallax-holder">
                    <h1>منتجاتنا <small style="color: inherit;">Our Products</small></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="container ws-page-container">
        <div class="row">
            <div class="col-xs-12">
                <form action="{{ route('shop') }}" method="GET" class="well" role="search" aria-label="البحث وتصفية المنتجات">
                    <div class="row">
                        <div class="form-group col-sm-5">
                            <label for="shop-search">البحث / Search</label>
                            <input id="shop-search" name="q" type="search" class="form-control"
                                value="{{ $search_text }}" maxlength="100"
                                placeholder="ابحث باسم المنتج أو القسم / Search products or categories">
                        </div>
                        <div class="form-group col-sm-3">
                            <label for="shop-category">القسم / Category</label>
                            <select id="shop-category" name="category" class="form-control">
                                <option value="">كل الأقسام / All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $category_id === (string) $category->id)>
                                        {{ $category->arabic_name }} / {{ $category->name }} ({{ $category->products_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-3">
                            <label for="shop-sort">الترتيب / Sort</label>
                            <select id="shop-sort" name="sort" class="form-control">
                                <option value="">الافتراضي / Default</option>
                                <option value="price_asc" @selected($sort_option === 'price_asc')>السعر بعد الخصم: الأقل أولاً</option>
                                <option value="price_desc" @selected($sort_option === 'price_desc')>السعر بعد الخصم: الأعلى أولاً</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="hidden-xs">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">بحث</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="ws-shop-page">
                <div class="col-xs-12 shop-results-summary">
                    <p><strong>{{ $products->count() }}</strong> نتيجة / results</p>
                    @if ($search_text !== '' || $category_id !== null || $sort_option !== '')
                        <a href="{{ route('shop') }}" class="btn btn-default btn-sm">مسح التصفية / Clear filters</a>
                    @endif
                </div>
                @forelse ($products as $prod)
                    <div class="col-sm-6 col-md-4 ws-works-item">
                        <a href="{{ route('product', $prod) }}">
                            <div class="ws-item-offer">
                                <figure>
                                    <img src="{{ $prod->main_image_url }}" alt="{{ $prod->arabic_name }} / {{ $prod->name }}" loading="lazy" decoding="async"
                                        class="img-responsive">
                                </figure>
                            </div>
                            <div class="ws-works-caption text-center">
                                <div class="ws-item-category">
                                    {{ $prod->subcategory->arabic_name }} / {{ $prod->subcategory->name }}
                                </div>
                                <h3 class="ws-item-title">{{ $prod->arabic_name }}</h3>
                                <p>{{ $prod->name }}</p>
                                <div class="ws-item-separator"></div>
                                @if ($prod->offer)
                                    <div class="ws-item-price">
                                        <del>{{ number_format($prod->price, 2) }} EGP</del>
                                        <ins>{{ number_format($prod->final_price, 2) }} EGP</ins>
                                    </div>
                                @else
                                    <div class="ws-item-price"><ins>{{ number_format($prod->final_price, 2) }} EGP</ins></div>
                                @endif
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-xs-12 text-center">
                        <h3>لا توجد منتجات مطابقة / No matching products</h3>
                        <p><a href="{{ route('shop') }}">عرض كل المنتجات / View all products</a></p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
