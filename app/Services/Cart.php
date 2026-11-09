<?php

namespace App\Services;

use App\Models\Product;

class Cart
{
    public static function cents($value): int
    {
        return max(0, (int) round((float) $value * 100, 0, PHP_ROUND_HALF_UP));
    }

    public static function quantities(): array
    {
        $cart = session('cart', []);
        $quantities = [];
        foreach (is_array($cart) ? $cart : [] as $key => $quantity) {
            // Older sessions used product-color-size keys. Merge valid entries.
            $id = explode('-', (string) $key)[0];
            if (! ctype_digit($id) || (int) $id < 1 || filter_var($quantity, FILTER_VALIDATE_INT) === false || $quantity < 1 || $quantity > 99) {
                continue;
            }
            $quantities[(int) $id] = min(99, ($quantities[(int) $id] ?? 0) + (int) $quantity);
        }

        return $quantities;
    }

    public static function snapshot(): object
    {
        $items = [];
        $total = $discount = $count = 0;
        $quantities = self::quantities();
        foreach (Product::with(['mainImage', 'images'])->whereIn('id', array_keys($quantities))->orderBy('id')->get() as $product) {
            $quantity = $quantities[$product->id];
            $original = self::cents($product->price);
            $unitDiscount = min($original, self::cents($product->offer));
            $price = $original - $unitDiscount;
            $items[] = (object) [
                'id' => $product->id, 'title' => $product->arabic_name ?: $product->name,
                'url' => route('product', $product), 'image_url' => $product->main_image_url,
                'quantity' => $quantity, 'price' => $price / 100,
                'line_total' => $price * $quantity / 100,
            ];
            $total += $price * $quantity;
            $discount += $unitDiscount * $quantity;
            $count += $quantity;
        }

        return (object) ['items' => $items, 'total' => $total / 100,
            'subtotal' => ($total + $discount) / 100, 'discount' => $discount / 100,
            'count' => $count, 'version' => (int) session('cart_version', 0)];
    }

    public static function store(array $quantities): object
    {
        session(['cart' => $quantities, 'cart_version' => (int) session('cart_version', 0) + 1]);

        return self::snapshot();
    }
}
