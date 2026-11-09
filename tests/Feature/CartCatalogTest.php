<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CartCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'First', 'arabic_name' => 'الأول'],
            ['id' => 2, 'name' => 'Second', 'arabic_name' => 'الثاني'],
        ]);
        DB::table('sub_categories')->insert([
            ['id' => 1, 'name' => 'Alpha', 'arabic_name' => 'ألفا', 'category_id' => 1],
            ['id' => 2, 'name' => 'Beta', 'arabic_name' => 'بيتا', 'category_id' => 1],
            ['id' => 3, 'name' => 'Gamma', 'arabic_name' => 'جاما', 'category_id' => 2],
        ]);
        DB::table('areas')->insert([
            ['id' => 1, 'name' => 'Cairo', 'arabic_name' => 'القاهرة', 'rate' => 1200.25, 'is_active' => 1],
            ['id' => 2, 'name' => 'Closed', 'arabic_name' => 'مغلق', 'rate' => 10, 'is_active' => 0],
        ]);
        DB::table('dash_users')->insert(['id' => 1, 'name' => 'Fixture', 'password' => 'unused']);
        $this->product(1, 1, 1100.255, 100.125);
        $this->product(2, 2, 50, 100);
        $this->product(3, 3, 20, 0);
    }

    private function product(int $id, int $subcategory, float $price, float $offer): void
    {
        DB::table('products')->insert([
            'id' => $id, 'sub_category_id' => $subcategory, 'name' => 'Needle '.$id,
            'arabic_name' => 'منتج '.$id, 'desc' => 'Educational', 'arabic_desc' => 'تعليمي',
            'price' => $price, 'offer' => $offer, 'material' => 'Paper', 'dimensions' => '10',
        ]);
    }

    private function details(): array
    {
        return ['guestName' => 'Test Guest', 'area' => 1, 'address' => 'Fixture address', 'phone' => '01000000000'];
    }

    public function test_add_accumulates_and_returns_canonical_totals(): void
    {
        $this->postJson('/cart/add', ['id' => 1, 'quantity' => 2])
            ->assertOk()->assertJsonPath('count', 2)->assertJsonPath('total', 2000.26)
            ->assertJsonPath('discount', 200.26)->assertJsonPath('subtotal', 2200.52)
            ->assertJsonPath('items.0.price', 1000.13)->assertJsonPath('version', 1);
        $this->postJson('/cart/add', ['id' => 1, 'quantity' => 1])
            ->assertOk()->assertJsonPath('count', 3)->assertJsonPath('total', 3000.39);
        $this->get('/cart')->assertOk()->assertSee('Cart (3)')->assertSee('3,000.39');
    }

    public static function invalidQuantities(): array
    {
        return array_map(fn ($q) => [$q], [0, -1, 100, 1.5, '1x', null, [], '1e1']);
    }

    #[DataProvider('invalidQuantities')]
    public function test_invalid_add_leaves_session_unchanged($quantity): void
    {
        $this->withSession(['cart' => [1 => 2]])->postJson('/cart/add', ['id' => 1, 'quantity' => $quantity])
            ->assertUnprocessable()->assertSessionHas('cart', [1 => 2]);
    }

    public function test_accumulated_limit_and_missing_product_are_rejected(): void
    {
        $this->withSession(['cart' => [1 => 98]])->postJson('/cart/add', ['id' => 1, 'quantity' => 2])
            ->assertUnprocessable()->assertSessionHas('cart', [1 => 98]);
        $this->postJson('/cart/add', ['id' => 404, 'quantity' => 1])->assertUnprocessable();
        $this->postJson('/cart/add', ['id' => 1, 'quantity' => 1])->assertOk()->assertJsonPath('count', 99);
    }

    public static function invalidUpdates(): array
    {
        return [
            [[1, 2], [1]], [[1], [1, 2]], [[1, 1], [1, 2]], [[404], [1]],
            [[1], [100]], [[1], [-1]], [[1], [1.2]], [['a' => 1], ['a' => 1]],
        ];
    }

    #[DataProvider('invalidUpdates')]
    public function test_invalid_update_is_atomic(array $products, array $quantities): void
    {
        $this->withSession(['cart' => [1 => 2]])->postJson('/cart', ['product' => $products, 'quantity' => $quantities])
            ->assertUnprocessable()->assertSessionHas('cart', [1 => 2]);
    }

    public function test_update_zero_and_remove_handle_empty_or_deleted_products(): void
    {
        $this->withSession(['cart' => [1 => 2, 2 => 3]])->postJson('/cart', ['product' => [1, 2], 'quantity' => [0, 4]])
            ->assertOk()->assertJsonPath('count', 4)->assertJsonPath('total', 0);
        $this->postJson('/cart/remove', ['id' => 2])->assertOk()->assertJsonPath('items', [])->assertJsonPath('count', 0);
        $this->postJson('/cart/remove', ['id' => 404])->assertOk()->assertJsonPath('items', []);
        $this->postJson('/cart', ['product' => [], 'quantity' => []])->assertOk();
    }

    public function test_old_sessions_are_safe_and_main_image_uses_product_image_id(): void
    {
        DB::table('prod_images')->insert([
            ['id' => 1, 'product_id' => 1, 'image_url' => 'https://example.com/fallback.jpg'],
            ['id' => 2, 'product_id' => 1, 'image_url' => 'https://example.com/main.jpg'],
        ]);
        DB::table('products')->where('id', 1)->update(['product_image_id' => 2]);
        $this->withSession(['cart' => ['1-0-0' => 2, 404 => 1, 2 => 'bad', 3 => -1]])->getJson('/api/cart')
            ->assertOk()->assertJsonPath('count', 2)->assertJsonPath('items.0.image_url', 'https://example.com/main.jpg');
        DB::table('products')->where('id', 1)->update(['product_image_id' => null]);
        $this->assertSame('https://example.com/fallback.jpg', Product::find(1)->main_image_url);
        $this->withSession(['cart' => 'corrupt'])->get('/cart')->assertOk()->assertSee('Cart (0)');
    }

    public function test_empty_checkout_and_orders_are_blocked(): void
    {
        $this->get('/checkout')->assertRedirect('/cart');
        foreach (['/order/submit', '/order/whatsapp/submit'] as $url) {
            $this->postJson($url, $this->details())->assertUnprocessable()->assertJsonValidationErrors('cart');
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_shipping_area_is_validated_before_order_creation(): void
    {
        foreach ([2, 404, 'invalid'] as $area) {
            $this->withSession(['cart' => [1 => 2]])->postJson('/order/submit', array_replace($this->details(), ['area' => $area]))
                ->assertUnprocessable()->assertSessionHas('cart', [1 => 2]);
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_order_and_whatsapp_use_identical_server_totals(): void
    {
        $data = $this->details() + ['user' => 404, 'total' => 1, 'shipping' => 0, 'guest' => 2];
        $response = $this->withSession(['cart' => [1 => 2]])->postJson('/order/whatsapp/submit', $data)->assertOk();
        $message = rawurldecode($response->json('url'));
        $this->assertStringContainsString('2000.26', $message);
        $this->assertStringContainsString('1200.25', $message);
        $this->assertStringContainsString('3200.51', $message);
        $this->assertDatabaseCount('orders', 0);
        $this->post('/order/submit', $data)->assertRedirect(route('home'))->assertSessionHas('cart', []);
        $this->assertDatabaseHas('orders', ['user_id' => null, 'guest_name' => 'Test Guest', 'total' => 3200.51]);
        $this->assertDatabaseHas('order_items', ['product_id' => 1, 'amount' => 2]);
        Mail::assertNothingSent();
    }

    public function test_logged_in_order_uses_authenticated_user_and_renders_one_form(): void
    {
        $user = User::forceCreate(['name' => 'Actual Buyer', 'email' => 'test@example.invalid', 'area_id' => 1]);
        $this->actingAs($user)->withSession(['cart' => [1 => 1]])->get('/checkout')->assertOk()
            ->assertSee('data-products-cents="100013"', false)->assertDontSee('name="user"', false);
        $this->post('/order/submit', array_replace($this->details(), ['user' => 404]))->assertRedirect(route('home'));
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'guest_name' => null, 'total' => 2200.38]);
    }

    public function test_subcategory_search_is_isolated_and_filters_survive_pagination(): void
    {
        for ($id = 4; $id <= 16; $id++) {
            $this->product($id, 1, 100, 10);
        }
        $response = $this->get('/shop/subcategory/1?q=Needle&sort=price_asc')->assertOk();
        $products = $response->viewData('products');
        $this->assertSame(14, $products->total());
        $this->assertSame(12, $products->count());
        $this->assertSame(range(4, 15), $products->pluck('id')->all());
        $this->assertStringContainsString('q=Needle', $products->nextPageUrl());
        $this->assertStringContainsString('sort=price_asc', $products->nextPageUrl());
        $next = $this->get('/shop/subcategory/1?q=Needle&sort=price_asc&page=2')->assertOk();
        $this->assertSame([16, 1], $next->viewData('products')->pluck('id')->all());
        $this->get('/shop/1')->assertOk()->assertViewHas('products', fn ($p) => $p->total() === 15);
        $this->get('/shop?category=1&subcategory=2&q=Needle&sort=price_desc')->assertOk()
            ->assertViewHas('products', fn ($p) => $p->pluck('id')->all() === [2]);
        $this->getJson('/shop?category=2&subcategory=1')->assertUnprocessable();
        $this->get('/shop/subcategory/404')->assertNotFound();
        $this->get('/shop/404')->assertNotFound();
    }
    public function test_catalog_sort_matches_rounded_and_clamped_unit_prices(): void
    {
        $this->product(4, 1, 5.005, -20);
        $this->product(5, 1, -1, 0);
        $this->product(6, 1, 5.004, 0);
        $products = $this->get('/shop?category=1&sort=price_asc')->assertOk()->viewData('products');
        $this->assertSame([2, 5, 6, 4, 1], $products->pluck('id')->all());
        $this->assertSame(5.01, Product::find(4)->final_price);
        $this->assertSame(0.0, Product::find(5)->final_price);
        $this->get('/shop/subcategory/1?subcategory=3')->assertOk()
            ->assertViewHas('products', fn ($p) => $p->every(fn ($product) => $product->sub_category_id === 1));
    }

}
