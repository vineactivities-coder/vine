@php
    $pageSeo = \App\Services\Seo::page(request(), $product ?? null);
    $organization = [
        '@context' => 'https://schema.org', '@type' => 'Organization',
        'name' => 'Vine Activities', 'url' => url('/'), 'logo' => asset('assets/images/vineLogo.png'),
        'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => $site_info->phone ?: '+201200165007', 'contactType' => 'customer service', 'availableLanguage' => ['Arabic', 'English']],
        'sameAs' => array_values(array_filter([$site_info->fb_url, $site_info->insta_url])),
    ];
    $structuredData = [$organization];
    if (isset($product)) {
        $structuredData[] = [
            '@context' => 'https://schema.org', '@type' => 'Product',
            'name' => $product->arabic_name ?: $product->name, 'sku' => (string) $product->id,
            'description' => $pageSeo['description'], 'image' => $pageSeo['image'],
            'brand' => ['@type' => 'Brand', 'name' => 'Vine Activities'],
            'offers' => ['@type' => 'Offer', 'url' => $pageSeo['canonical'], 'priceCurrency' => 'EGP',
                'price' => number_format($product->final_price, 2, '.', ''),
                'availability' => $product->stock->sum('amount') > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition'],
        ];
        $structuredData[] = [
            '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Vine Activities', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'item' => url('shop')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $product->arabic_name ?: $product->name, 'item' => $pageSeo['canonical']],
            ],
        ];
    }
@endphp
<title>{{ $pageSeo['title'] }}</title>
<meta name="description" content="{{ $pageSeo['description'] }}">
<meta name="robots" content="{{ $pageSeo['robots'] }}">
<link rel="canonical" href="{{ $pageSeo['canonical'] }}">
<meta property="og:locale" content="ar_EG">
<meta property="og:site_name" content="Vine Activities">
<meta property="og:type" content="{{ $pageSeo['type'] }}">
<meta property="og:title" content="{{ $pageSeo['title'] }}">
<meta property="og:description" content="{{ $pageSeo['description'] }}">
<meta property="og:url" content="{{ $pageSeo['canonical'] }}">
<meta property="og:image" content="{{ $pageSeo['image'] }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageSeo['title'] }}">
<meta name="twitter:description" content="{{ $pageSeo['description'] }}">
<meta name="twitter:image" content="{{ $pageSeo['image'] }}">
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
