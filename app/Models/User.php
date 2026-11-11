<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class User extends Authenticatable
{
    protected $table = "users";
    public $timestamps = true;

    protected $fillable = [
        'email', 'name'
    ];
    protected $hidden = [
        'remember_token',
    ];

    //queries
    public static function createUser(string $name, string $mail, int $area, int $gender, string $mobileNumber, string $address, string $pass): User
    {

        $newUser = new self();
        $newUser->name = $name;
        $newUser->email = $mail;
        $newUser->address = $address;
        $newUser->area_id = $area;
        $newUser->gender_id = $gender;
        $newUser->mobile = $mobileNumber;
        $newUser->password = $pass ? bcrypt($pass) : null;
        $newUser->save();
        return $newUser;
    }

    public static function getUserByMail($mail): ?User
    {
        return self::where("mail", $mail)->first() ?? null;
    }

    public function addToWishlist($productID): bool
    {
        try {
            $this->wishlist()->sync($productID, false);
            return true;
        } catch (Exception $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function updatePassword(string $newpass)
    {
        $this->password = bcrypt($newpass);
        $this->save();
    }

    public static function googleLogin($payload){
        $user = self::firstOrCreate([
            "email" =>  $payload['email']
        ],[
            "name"  =>  $payload['given_name'] . ' ' . $payload['family_name']
        ]);

        Auth::guard('web')->loginUsingId($user->id, true);
        return true;
    }

    public static function getUserCart()
    {
        return \App\Services\Cart::snapshot();
    }

    //accessors
    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    ////relations
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, "wishlist");
    }

    public function wishlistQuery(): Builder
    {
        //using this query builder instead of default BelongsToMany because of bug
        return Product::join('wishlist', "product_id", '=', "products.id")
            ->join('users', "user_id", '=', "users.id")
            ->where('users.id', $this->id)
            ->select('products.*');
    }

    public function previouslyBoughtQuery(): Builder
    {
        return Product::join('order_items', 'product_id', '=', 'products.id')
            ->join('orders', 'order_id', '=', 'orders.id')
            ->select("products.*")
            ->groupBy('products.id')
            ->where('user_id', $this->id)
            ->where('orders.status', 'done');
    }

    public function itemsBought()
    {
        return DB::table('order_items')
            ->join('products', 'product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw("products.name as product_name, SUM(order_items.amount) as itemCount")
            ->groupBy('order_items.id')
            ->where('orders.user_id', $this->id)
            ->where('orders.status', 'done')->get();
    }

    public function moneyPaid()
    {
        return DB::table('orders')->where('orders.user_id', $this->id)->where('orders.status', 'done')
            ->selectRaw('SUM(orders.paid) as paid, SUM(orders.discount) as discount')
            ->get()->first();
    }

    ///relations
    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function getAuthPassword()
    {
        return $this->password;
    }
}
