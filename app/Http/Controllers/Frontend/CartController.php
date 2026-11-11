<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\WSBaseDataManager;
use App\Services\Cart;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use stdClass;

class CartController extends Controller
{
    public function submitOrder(Request $request)
    {
        $request->validate([
            "user"          =>  "required_if:guest,2|nullable|exists:users,id",
            "guestName"     =>  "required_if:guest,1",
            "area"          =>  "required",
            "address"      =>  "required",
            "phone"      =>  "required",
        ]);

        $loadedCart = User::getUserCart();
        $itemsArray = $this->getOrderItemsObjectArray($loadedCart->items);

        $order = Order::addNew($request->user, $request->phone, $request->address, $request->area, $request->note, $itemsArray, $loadedCart->total, $request->guestName);
        if ($order) {
            $order->addTimeline("Order Opened by client");
            $request->session()->forget('cart');
            $msgClientName = '';
            if (isset($request->user)) {
                $msgClientName = (User::findOrFail($request->user))->name;
            } else {
                $msgClientName = $request->guestName;
            }

            return  redirect('home')->with("flag", "showOrderSubmitted");
        } else {
            return redirect('home')->with("flag", "showOrderFailed");
        }
    }

    public function sendWhatsappOrder(Request $request)
    {
        $request->validate([
            "user"          =>  "required_if:guest,2|nullable|exists:users,id",
            "guestName"     =>  "required_if:guest,1",
            "area"          =>  "required",
            "address"      =>  "required",
            "phone"      =>  "required",
        ]);
        $loadedCart = User::getUserCart();
        $itemsArray = $this->getOrderItemsObjectArray($loadedCart->items);
        $whatsappUrl = Order::generateWhatsAppMessage($request->user, $request->phone, $request->address, $request->area, $request->note, $itemsArray, $loadedCart->total, $request->guestName);
        return response()->json(["url" => $whatsappUrl]);
    }

    public function cartData()
    {
        return response()->json(Cart::snapshot())->header('Cache-Control', 'no-store');
    }

    public function cart()
    {
        $data = WSBaseDataManager::getSiteData(true);
        return view('frontend.cart.cart', $data);
    }

    public function checkout()
    {
        $data = WSBaseDataManager::getSiteData();
        $data['areas']  = Area::all();
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

    private function getOrderItemsObjectArray(array $itemsArray)
    {
        $retArr = array();
        foreach ($itemsArray as $item) {
            array_push($retArr, new OrderItem(
                ["product_id" => $item->id, "amount" => $item->quantity]
            ));
        }
        return $retArr;
    }
}
