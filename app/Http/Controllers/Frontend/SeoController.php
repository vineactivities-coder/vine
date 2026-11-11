<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Seo;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function sitemap()
    {
        return response()->view('frontend.sitemap', [
            'paths' => ['/', '/shop', '/aboutus', '/delivery', '/payment', '/contact'],
            'products' => Product::select('id', 'updated_at')->orderBy('id')->lazyById(500),
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(Request $request)
    {
        $body = Seo::productionHost($request)
            ? "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /cart\nDisallow: /checkout\nDisallow: /api/\nDisallow: /login\nDisallow: /register\nDisallow: /wishlist\nDisallow: /bought/\nSitemap: ".$request->root()."/sitemap.xml\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
