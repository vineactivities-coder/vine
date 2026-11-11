@php echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($paths as $path)
    <url><loc>{{ url($path) }}</loc></url>
    @endforeach
    @foreach ($products as $sitemapProduct)
    <url>
        <loc>{{ url('product/'.$sitemapProduct->id) }}</loc>
        @if ($sitemapProduct->updated_at)<lastmod>{{ $sitemapProduct->updated_at->toAtomString() }}</lastmod>@endif
    </url>
    @endforeach
</urlset>
