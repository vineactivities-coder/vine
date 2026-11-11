<?php

namespace App\Models;

use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $table = "orders";
    public $timestamps = true;
    const STATUSES = [
        'New',
        'Ready',
        'In Delivery',
        'Delivered',
        'Cancelled',
        'Returned'
    ];


    //order functions
    public function recalculateTotal()
    {
        $total = 0;
        foreach ($this->order_items as $item) {
            $product = Inventory::with("product")->where("product_id", $item->product_id)->first()->product;
            $price = $product->price - $product->offer;
            $total += $item->amount * $price;
        }
        $this->total = $total;
        $this->save();
    }

    //static queries
    public static function addNew($user, $mobN, $address, $area, $note, $ItemsArray, $total, $guestName): Order
    {
        $order = new Order();
        DB::transaction(function () use ($order, $user, $guestName, $mobN, $address, $note, $area, $ItemsArray, $total) {
            if (isset($user))
                $order->user_id = $user;
            else {
                $order->guest_name = $guestName;
            }
            $order->guest_mobn = $mobN;
            $order->created_at = date('Y-m-d H:i:s');
            $order->address = $address;
            $order->note = $note;
            $order->area_id = $area;
            // $order->ORDR_PYOP_ID = $option;
            $order->status = 'new'; // new order
            $order->dash_user_id = Auth::user() && is_a(Auth::user(), DashUser::class) ? Auth::id() : null; // new order
            $areaObj = Area::findOrFail($area);
            $orderItemArray = $ItemsArray;
            $order->total = $total + $areaObj->rate;

            $order->save();
            $order->order_items()->saveMany($orderItemArray);
            $invtArr = [];
            foreach ($orderItemArray as $item) {
                array_push($invtArr, [
                    'modelID'   =>  $item->product_id,
                    'count'     =>  $item->amount
                ]);
            }
            Log::info($invtArr);
            Inventory::insertEntry($invtArr, $order->id);
        });
        return $order;
    }

    public static function generateWhatsAppMessage($user, $mobN = null, $address = null, $area = null, $note = null, $ItemsArray = null, $total = null, $guestName = null)
    {
        if (isset($user))
            $userName = User::find($user)->name;
        else
            $userName = $guestName;

            $areaName = Area::find($area)->name;    

        $message = <<<EOD
            اهلا vine،

          اريد ان اطلب منتجات منكم، الرجاء التواصل معي لتأكيد الطلب.

          انا {$userName}، رقم الهاتف: {$mobN}
           العنوان: {$address}
            المنطقة: {$areaName}
            الملاحظات: {$note}
            الاجمالي: {$total} جنيه
            الطلبات:
        EOD;

        foreach ($ItemsArray as $prod) {
            $product = Product::findOrFail($prod->product_id);
            $message .= "\n• {$product->name}: {$prod->amount} ";
        }


        $message .= <<<EOD
                    \n\nبرجاء التواصل لتأكيد الطلب. \n\n
            EOD;

        $encodedMessage = urlencode($message);


        return "https://wa.me/+201200165007?text={$encodedMessage}";
    }

    public static function getOrdersByDate(bool $currentMonth = true, int $month = -1, int $year = -1, int $state = -1)
    {

        $startDate = '';
        $endDate = '';

        if ($currentMonth) {
            $startDate = (new DateTime("first day of this month"))->format('Y-m-d 0:0:0');
            $endDate = (date_add((new DateTime('now')), new DateInterval("P01M")))->format('Y-m-1 0:0:0');
        } elseif ($month != -1 && $year == -1) {
            assert((0 < $month) && ($month < 13), 'Invalid Month');
            $year = date('Y');
            $startDate = $year . '-' . $month . '-01';
            $endDate   = date_add((new DateTime($startDate)), new DateInterval("P01M"))->format('Y-m-1 0:0:0');
        } elseif ($month == -1 && $year != -1) {
            $startDate = $year . '-01-01 00:00:00';
            $endDate = $year . '-12-31 12:59:59';
        } else {
            assert((0 < $month) && ($month < 13), 'Invalid Month');
            $startDate = $year . '-' . $month . '-01';
            $endDate   = date_add((new DateTime($startDate)), new DateInterval("P01M"))->format('Y-m-1 0:0:0');
        }


        $query =  self::tableQuery();

        if ($state > 0 && $state < 7) {
            $query = $query->where("status", "=", $state);
        } else {
            $query = $query->where("status", ">", 3);
        }
        $query->whereBetween("delivery_date", [$startDate, $endDate]);

        return $query->get();
    }

    public static function getOrdersByUser($userID)
    {
        $query = self::tableQuery();
        $query = $query->where('orders.user_id', $userID);
        return $query->get();
    }

    public static function getActiveOrders($state = -1)
    {
        $query = self::tableQuery();
        if ($state > 0 && $state < 6) {
            $query = $query->where("status", "=", $state);
        } else {
            $query = $query->where("status", "=", 1)->orWhere("status", "=", 1)
                ->orWhere("status", "=", 2)
                ->orWhere("status", "=", 3);
        }
        return $query->get();
    }

    public static function getOrderDetails($id)
    {
        $ret['order'] = self::join("areas", "orders.area_id", "=", "areas.id")
            ->Leftjoin("users", "orders.user_id", "=", "users.id")
            ->Leftjoin("dash_users", "orders.dash_user_id", "=", "dash_users.id")
            ->Leftjoin("drivers", "driver_id", "=", "drivers.id")
            ->Leftjoin("order_items", "order_items.order_id", "=", "orders.id")
            // ->join("payment_options", "ORDR_PYOP_ID", "=", "payment_options.id")
            ->select("orders.*", 'drivers.name', "orders.guest_name", "areas.name as name", "rate", "users.name", "users.mobile", "dash_users.name")->selectRaw("SUM(amount) as itemsCount")
            ->groupBy("orders.id", "areas.name", "users.name", "users.mobile")
            ->where('orders.id', $id)->get()->first();

        $ret['items'] = DB::table('order_items')
            ->join("products", "order_items.product_id", "=", "products.id")
            ->select("order_items.id", "products.arabic_name as product_name", "amount", "is_verified", "price", "offer")
            ->where("order_items.order_id", "=", $id)
            ->get();

        $ret['timeline'] = DB::table('timeline')
            ->join('dash_users', 'dash_user_id', '=', 'dash_users.id')
            ->select('name', 'timeline.*')
            ->orderByDesc('timeline.id')
            ->where('order_id', $id)->get();

        return $ret;
    }

    public static function getOrdersCountByState($state, $startDate = null, $endDate = null)
    {
        $query = DB::table("orders")->where("orders.status", $state);

        if (!is_null($startDate) && !is_null($endDate)) {
            $query->whereBetween('delivery_date', [$startDate, $endDate]);
        }

        return $query->get()->count();
    }

    private static function tableQuery()
    {
        return DB::table("orders")
            ->join("areas", "area_id", "=", "areas.id")
            ->Leftjoin("users", "user_id", "=", "users.id")
            ->Leftjoin("dash_users", "dash_user_id", "=", "dash_users.id")
            ->Leftjoin("order_items", "order_id", "=", "orders.id")
            ->select("orders.*", "orders.created_at as open_date", "dash_users.name as user_name", "areas.name as area_name", "users.name as client_name", "users.mobile")->selectRaw("SUM(amount) as itemsCount")
            ->groupBy("orders.id", "areas.name", "users.name", "users.mobile");
    }

    //attribute
    public function getIsNewAttribute()
    {
        return $this->status == 'New';
    }
    public function getIsReadyAttribute()
    {
        return $this->status == 'Ready';
    }
    public function getIsDeliveredAttribute()
    {
        return $this->status == 'Delivered';
    }
    public function getIsCancelledAttribute()
    {
        return $this->status == 'Cancelled';
    }
    public function getIsReturnedAttribute()
    {
        return $this->status == 'Returned';
    }

    //relations
    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function timeline()
    {
        return $this->hasMany("timeline", "order_id", "id");
    }

    public function client()
    {
        return $this->belongsTo("App\Models\User", "user_id", "id");
    }

    public function area()
    {
        return $this->belongsTo("App\Models\Area", "area_id", "id");
    }

    public function driver()
    {
        return $this->belongsTo("App\Models\Driver", "driver_id", "id");
    }

    // public function paymentOption()
    // {
    //     return $this->belongsTo("payment_options", "area_id", "id");
    // }


    public function addTimeline($text)
    {
        $timeline = new Timeline();
        $timeline->dash_user_id = (Auth::user() && is_a(Auth::user(), DashUser::class)) ? Auth::user()->id : 1;
        $timeline->order_id = $this->id;
        $timeline->text    = $text;
        $timeline->save();
    }
}
