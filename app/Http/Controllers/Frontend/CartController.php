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
        return response()->json(User::getUserCart());
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

        $request->validate([
            "product"   =>  "required|array",
            "quantity"  =>  "required|array",

        ]);
        $this->updateCartFromCartArray($request);
        return redirect('cart');
    }

    public function add(Request $request)
    {
        $request->validate([
            "id"            =>  "required|exists:products",
            "quantity"      =>  "required|numeric"
        ]);

        $new_cart_item = [
            "details"   =>  Product::findOrFail($request->id),
        ];


        if ($request->session()->has("cart")) {
            //cart found
            $cartArray = $request->session()->get("cart");
            //the cart array key's format is "[prod_id]-[color_id]-[size_id]"
            $alreadyInCart = ($cartArray[$request->id] ?? 0);

            $cartArray[$request->id] =
                $alreadyInCart + $request->quantity;
            $new_cart_item["quantity"] = $cartArray[$request->id];
        } else {

            //new cart created by adding first item
            $cartArray = [
                $request->id => $request->quantity
            ];
            $new_cart_item["quantity"] = $request->quantity;
        }
        $request->session()->put("cart", $cartArray);
        return response()->json(["message" => true]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            "id"        =>  "required|exists:products",
        ]);
        $cartArray = session("cart");

        if (isset($cartArray[$request->id])) {
            unset($cartArray[$request->id]);
        }

        $request->session()->put("cart", $cartArray);

        return response()->json(["message" => true]);
    }

    /**
     * @param array $cart the cart array retrieved from the cart page
     */
    private function updateCartFromCartArray(Request $cartRequest): array
    {
        Session::remove('cart');

        $newCartArr = array();
        foreach ($cartRequest->product as $index => $prodID) {
            if ($cartRequest->quantity[$index])
                $newCartArr[$prodID] = $cartRequest->quantity[$index];
        }
        Session::put('cart', $newCartArr);
        return $newCartArr;
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
