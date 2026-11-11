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

class CartTest extends TestCase
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

    public function test_empty_and_authenticated_cart_sessions_are_supported(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Cart (0)');
        $user = User::forceCreate(['name' => 'Buyer', 'email' => 'buyer@example.invalid']);
        $this->actingAs($user)->withSession(['cart' => [1 => 2]])->getJson('/api/cart')
            ->assertOk()->assertJsonPath('count', 2)->assertJsonPath('total', 2000.26);
        $this->flushSession();
        $this->getJson('/api/cart')->assertOk()->assertJsonPath('count', 0);
    }

    public function test_html_cart_update_uses_the_correct_route(): void
    {
        $this->withSession(['cart' => [1 => 1]])->post('/cart', ['product' => [1], 'quantity' => [3]])
            ->assertRedirect('/cart')->assertSessionHas('cart', [1 => 3]);
        $this->get('/cart')->assertOk()->assertSee('Cart (3)')->assertSee('3,000.39');
    }
}
