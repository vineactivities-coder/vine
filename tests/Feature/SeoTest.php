<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('categories')->insert(['id' => 1, 'name' => 'Activities', 'arabic_name' => 'أنشطة']);
        DB::table('sub_categories')->insert(['id' => 1, 'category_id' => 1, 'name' => 'Art', 'arabic_name' => 'فن']);
        DB::table('products')->insert([
            'id' => 1, 'sub_category_id' => 1, 'name' => 'Bible Activity', 'arabic_name' => 'نشاط الكتاب',
            'desc' => 'Education', 'arabic_desc' => '<b>شرح</b> &amp; تعليم',
            'price' => 1100.255, 'offer' => 100.125, 'material' => 'Paper', 'dimensions' => '10',
            'updated_at' => '2026-11-01 12:00:00',
        ]);
    }

    private function dom(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new \DOMXPath($dom);
    }

    public function test_public_pages_have_metadata_and_staging_is_not_indexed(): void
    {
        $response = $this->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $xpath = $this->dom($response->getContent());
        $this->assertSame('ar', $xpath->evaluate('string(//html/@lang)'));
        $this->assertStringContainsString('مدارس الأحد', $xpath->evaluate('string(//title)'));
        $this->assertNotSame('', $xpath->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame('noindex,nofollow', $xpath->evaluate('string(//meta[@name="robots"]/@content)'));
        foreach (['og:title', 'og:description', 'og:image', 'og:url'] as $property) {
            $this->assertNotSame('', $xpath->evaluate('string(//meta[@property="'.$property.'"]/@content)'));
        }
        $this->get('/robots.txt')->assertOk()->assertSee("User-agent: *\nDisallow: /", false);
    }

    public function test_canonicals_drop_tracking_and_home_or_category_duplicates(): void
    {
        foreach (['/home?utm_source=test' => '/', '/shop/1?utm_source=test' => '/shop', '/product/1?utm_source=test' => '/product/1'] as $url => $path) {
            $response = $this->get($url)->assertOk();
            $xpath = $this->dom($response->getContent());
            $canonical = $xpath->evaluate('string(//link[@rel="canonical"]/@href)');
            $this->assertSame($path, parse_url($canonical, PHP_URL_PATH));
            $this->assertNull(parse_url($canonical, PHP_URL_QUERY));
        }
    }

    public function test_product_schema_has_real_price_image_and_safe_text(): void
    {
        DB::table('prod_images')->insert(['id' => 1, 'product_id' => 1, 'image_url' => 'https://example.com/main.jpg']);
        DB::table('products')->where('id', 1)->update(['product_image_id' => 1]);
        $response = $this->get('/product/1')->assertOk();
        $xpath = $this->dom($response->getContent());
        $this->assertSame('شرح & تعليم', $xpath->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame('https://example.com/main.jpg', $xpath->evaluate('string(//meta[@property="og:image"]/@content)'));
        $this->assertSame('نشاط الكتاب', $xpath->evaluate('string(//h1[@class="ws-item-title"])'));
        $json = $xpath->evaluate('string(//script[@type="application/ld+json"])');
        $schemas = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $product = collect($schemas)->firstWhere('@type', 'Product');
        $this->assertSame('1000.13', $product['offers']['price']);
        $this->assertSame('EGP', $product['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/OutOfStock', $product['offers']['availability']);
        DB::table('products')->where('id', 1)->update(['arabic_name' => '</script><script>alert(1)</script>']);
        $this->get('/product/1')->assertOk()->assertDontSee('</script><script>alert(1)</script>', false);
    }

    public function test_sitemap_is_valid_xml_and_excludes_private_or_new_category_routes(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urls = array_map('strval', $xml->xpath('//s:loc'));
        $this->assertCount(7, $urls);
        $this->assertContains(url('product/1'), $urls);
        foreach (['/cart', '/checkout', '/login', '/admin', '/shop/subcategory'] as $private) {
            foreach ($urls as $url) {
                $this->assertStringNotContainsString($private, $url);
            }
        }
    }

    public function test_only_production_public_pages_allow_indexing(): void
    {
        config(['app.env' => 'production']);
        $response = $this->get('https://vine-activities.com/product/1')->assertOk();
        $xpath = $this->dom($response->getContent());
        $this->assertSame('index,follow', $xpath->evaluate('string(//meta[@name="robots"]/@content)'));
        $this->get('https://vine-activities.com/cart')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('https://test.vine-activities.com/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $robots = $this->get('https://vine-activities.com/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Sitemap: https://vine-activities.com/sitemap.xml', $robots);
        $this->assertStringContainsString('Disallow: /checkout', $robots);
    }

    public function test_existing_catalog_tabs_and_routes_are_preserved(): void
    {
        $this->get('/shop/1')->assertOk()->assertSee('nav nav-tabs', false)->assertDontSee('shop-subcategory', false);
        $this->get('/shop/subcategory/1')->assertNotFound();
    }
}
