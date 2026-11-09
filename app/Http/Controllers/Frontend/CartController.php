<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Cart;
use App\Services\WSBaseDataManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private function checkoutData(Request $request): array
    {
        $data = $request->validate([
            'guestName' => [Rule::requiredIf(! $request->user()), 'nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->where('is_active', 1)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $cart = Cart::snapshot();
        if ($cart->count === 0) {
            throw ValidationException::withMessages(['cart' => 'السلة فارغة. أضف منتجات قبل إتمام الطلب.']);
        }
        $area = Area::active()->findOrFail($data['area']);
        $shipping = Cart::cents($area->rate);

        return [$data, $cart, $area, $shipping];
    }

    public function submitOrder(Request $request)
    {
        [$data, $cart, $area] = $this->checkoutData($request);
        DB::transaction(function () use ($request, $data, $cart, $area) {
            $items = array_map(fn ($item) => new OrderItem(['product_id' => $item->id, 'amount' => $item->quantity]), $cart->items);
            $order = Order::addNew($request->user()?->id, $data['phone'], $data['address'], $area->id,
                $data['note'] ?? null, $items, $cart->total, $request->user() ? null : $data['guestName']);
            $order->addTimeline('Order Opened by client');
        });
        Cart::store([]);

        return redirect()->route('home')->with('flag', 'showOrderSubmitted');
    }

    public function sendWhatsappOrder(Request $request)
    {
        [$data, $cart, $area, $shipping] = $this->checkoutData($request);
        $name = $request->user()?->name ?? $data['guestName'];
        $money = fn ($value) => number_format($value, 2, '.', '');
        $message = "اهلا Vine، أريد تأكيد طلب.\nالاسم: {$name}\nالهاتف: {$data['phone']}\nالعنوان: {$data['address']}\nالمنطقة: {$area->arabic_name}\n";
        foreach ($cart->items as $item) {
            $message .= "• {$item->title}: {$item->quantity} x {$money($item->price)} = {$money($item->line_total)} جنيه\n";
        }
        $total = (Cart::cents($cart->total) + $shipping) / 100;
        $message .= "قيمة المنتجات: {$money($cart->total)} جنيه\nالشحن: {$money($shipping / 100)} جنيه\nالإجمالي: {$money($total)} جنيه\nالملاحظات: ".($data['note'] ?? '');

        // Only prepare a link; no message is sent by the server.
        return response()->json(['url' => 'https://wa.me/201200165007?text='.rawurlencode($message)]);
    }

    public function cartData()
    {
        return response()->json(Cart::snapshot())->header('Cache-Control', 'no-store');
    }

    public function cart()
    {
        return view('frontend.cart.cart', WSBaseDataManager::getSiteData());
    }

    public function checkout()
    {
        $data = WSBaseDataManager::getSiteData();
        if (! $data['cart']->count) {
            return redirect()->route('cart')->withErrors(['cart' => 'السلة فارغة.']);
        }
        $data['areas'] = Area::active()->orderBy('id')->get();

        return view('frontend.cart.checkout', $data);
    }

    public function submitCart(Request $request)
    {
        $data = $request->validate([
            'product' => ['present', 'array', 'list'],
            'quantity' => ['present', 'array', 'list'],
            'product.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'quantity.*' => ['required', 'integer', 'between:0,99'],
        ]);
        if (count($data['product']) !== count($data['quantity'])) {
            throw ValidationException::withMessages(['quantity' => 'قوائم المنتجات والكميات غير متطابقة.']);
        }
        $quantities = [];
        foreach ($data['product'] as $index => $id) {
            if ($data['quantity'][$index] > 0) {
                $quantities[(int) $id] = (int) $data['quantity'][$index];
            }
        }
        $cart = Cart::store($quantities);

        return $request->expectsJson() ? response()->json($cart) : redirect()->route('cart');
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'between:1,99'],
        ]);
        $quantities = Cart::quantities();
        $quantity = ($quantities[$data['id']] ?? 0) + (int) $data['quantity'];
        if ($quantity > 99) {
            throw ValidationException::withMessages(['quantity' => 'الحد الأقصى لكمية المنتج 99.']);
        }
        $quantities[$data['id']] = $quantity;

        return response()->json(Cart::store($quantities));
    }

    public function remove(Request $request)
    {
        $data = $request->validate(['id' => ['required', 'integer', 'min:1']]);
        $quantities = Cart::quantities();
        unset($quantities[$data['id']]);

        return response()->json(Cart::store($quantities));
    }
}
