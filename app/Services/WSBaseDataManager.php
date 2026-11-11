<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteInfo;
use App\Models\Slide;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class WSBaseDataManager
{
    const COLLECTION_PAGES = ['wishlist', 'bought_items', 'category', 'search'];

    public static function getSiteData($loadStockDataOnCart = false): array
    {
        $data['site_info'] = SiteInfo::getSiteInfo();
        $data['categories'] = Category::with('subcategories')->get();
        $data['subcategories'] = SubCategory::all();
        $data['logged_user'] = Auth::user();
        $data['is_logged'] = $data['logged_user'] != null;
        $data['loadProductJsonUrl'] = url('get/product');
        $data['sendEmailUrl'] = url('contactus/sendemail');
        $data['signUpUrl'] = url('register');

        //cart vars
        $data['apiCart'] = url('api/cart');
        $data['addToCartUrl'] = url('cart/add');
        $data['removeFromCartUrl'] = url('cart/remove');
        $data['updateCartUrl'] = url('cart/submit');
        $data['submitOrderUrl'] = url('order/submit');
        $data['cart'] = User::getUserCart($loadStockDataOnCart);

        $data['site_info'] = SiteInfo::getSiteInfo();

        return $data;
    }

    public static function getCollectionPageData(
        bool $applyNewFilters,
        string $page = self::COLLECTION_PAGES[0],
        Builder|Relation $productsQuery,
        array $applied_price_filters = null,
        string $applied_sort_option = null,
        SubCategory $subCategory = null,
        int $perPageValue = null,
        string $search_text = null,
        array $available_price_filters = [150 => "0-300", 450 => "300-600", 750 => "600-900", 1050 => "900-"],
        array $perPageOptions = [12, 20, 28],
    ): array {
        assert(in_array($page, self::COLLECTION_PAGES), "Please make sure to use collection page with one of the collection pages defined in COLLECTION_PAGES const");
        $data = self::getSiteData();
        $data['pageAmountOptions'] = $perPageOptions;

        switch ($page) {
            case self::COLLECTION_PAGES[0]: //wishlist page
                // $data['showFilters'] = true;
                $data['title']  =   "Wishlist";
                $data['breadcumbs'] = [
                    (object)['title'    =>  "Home", 'url'   =>  url('/')],
                    (object)['title'    =>  "Wishlist", 'url'   =>  url('/wishlist')],
                ];
                break;
            case self::COLLECTION_PAGES[1]: //bought items page
                // $data['showFilters'] = false;
                $data['title']  =   "Bought Items";
                $data['breadcumbs'] = [
                    (object)['title'    =>  "Home", 'url'   =>  url('/')],
                    (object)['title'    =>  "Bought Items", 'url'   =>  url('/wishlist')],
                ];
                break;

            case self::COLLECTION_PAGES[2]: //category page
                // $data['showFilters'] = true;
                $data['title']  =   $subCategory->name;
                $data['breadcumbs'] = [
                    (object)['title'    =>  "Home", 'url'   =>  url('/')],
                    (object)['title'    =>  $subCategory->category->name, 'url'   =>  url('/all/' . $subCategory->category->id)],
                    (object)['title'    =>  $subCategory->name, 'url'   =>  url('/subcategory/' .  $subCategory->id)],
                ];
                break;

            case self::COLLECTION_PAGES[3]: //search page
                // $data['showFilters'] = true;
                $data['title']  =   "Searching for '{$search_text}'";
                $data['breadcumbs'] = [
                    (object)['title'    =>  "Home", 'url'   =>  url('/')],
                    (object)['title'    =>  "search", 'url' => url('search')],
                ];
                break;
        }

        //Sorting options to affect the collection query
        $data['sortingOptions'] = [];
        $data['sortingOptions']["default"] = "Default Sorting";
        $data['sortingOptions']["price_asc"] = "Price, low to high";
        $data['sortingOptions']["price_desc"] = "Price, high to low";
        $data['sortingOptions']["name_asc"] = "Alphabetically, A-Z";
        $data['sortingOptions']["name_desc"] = "Alphabetically, Z-A";


        //available filters
        $data['price_filters']  = $available_price_filters;

        //apply price range
        if ($applied_price_filters != null) {
            $data['applied_price_filters'] = array_values($applied_price_filters);
            $prices = $data['applied_price_filters'];
            $productsQuery = $productsQuery->where(function ($query) use ($prices) {
                foreach ($prices as $val) {
                    //val in array is 150 - 450 - 750
                    if ($val == 1050) {
                        //max filter
                        $query->orWhere('price', '>', $val - 150);
                    } else {
                        $query->orWhereBetween('price', [$val - 150, $val + 150]);
                    }
                }
            });
        }

        //apply sort
        if (isset($sortOption) && $sortOption != null) {
            switch ($sortOption) {
                case "price_asc":
                    $productsQuery = $productsQuery->orderBy('price', 'ASC');
                    break;
                case "price_desc":
                    $productsQuery = $productsQuery->orderBy('price', 'DESC');
                    break;
                case "name_asc":
                    $productsQuery = $productsQuery->orderBy('name', 'ASC');
                    break;
                case "name_desc":
                    $productsQuery = $productsQuery->orderBy('name', 'DESC');
                    break;
                default:
                    break;
            }
            $data['applied_sorting'] = $sortOption;
        }

        //loading data
        $currentPage =  $applyNewFilters ? 1 : null; //if filters applied return to page one .. else let laravel redirect to page from url

        $data['products'] = $productsQuery->select("products.*")->paginate($perPageValue, ['*'], 'page', $currentPage);

        //applying old filters to url
        $data['products']->appends('per_page', $perPageValue);
        if (isset($data['applied_color_filters']))
            $data['products']->appends('applied_color_filters', $data['applied_color_filters']);
        if (isset($data['applied_size_filters']))
            $data['products']->appends('applied_size_filters', $data['applied_size_filters']);
        if (isset($data['applied_price_filters']))
            $data['products']->appends('applied_price_filters', $data['applied_price_filters']);
        if (isset($data['applied_sorting']))
            $data['products']->appends('applied_sort_option', $data['applied_sorting']);

        if (isset($subCategory))
            $data['subcategory'] = $subCategory;

        return $data;
    }

    public static function getHomePageData(){
        $data = self::getSiteData();
        $data['is_home'] = true;
        $data['slides']   = Slide::site()->get();
        $data['categories'] = Category::all();
        return $data;
    }
}
