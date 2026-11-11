<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;
use App\Mail\ContactUsMail;
use App\Models\Category;
use App\Models\Location;
use App\Services\WSBaseDataManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SiteController extends Controller
{

    public function home()
    {
        $data = WSBaseDataManager::getHomePageData();
        $data['on_sale_prods'] = Product::onSale()->with('subcategory', 'subcategory.category')->limit(9)->get();
        $data['new_arrivals'] = Product::latest()->with('subcategory', 'subcategory.category')->limit(6)->get();
        $data['locations'] = Location::active()->get();
        $flag = session('flag');
        switch ($flag) {
            case 'showOrderSubmitted':
                $data['showOrderSubmitted'] = true;
                break;
            case 'showOrderFailed':
                $data['showOrderFailed'] = true;
                break;
            case 'emailSent':
                $data['emailSent'] = true;
                break;
            default:
                break;
        }
        return view('frontend.home', $data);
    }

    public function shop($category_id=null)
    {
        $data = WSBaseDataManager::getSiteData();
        $data['categories'] = Category::with('products')->get();
        $data['products'] = Product::
        select('products.*')
        ->join('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
        ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
        ->orderBy('categories.id', 'asc')
        ->orderBy('sub_categories.id', 'asc')
        ->orderBy('products.id', 'asc')
        ->get();
        $data['category_id'] = $category_id;
        return view('frontend.catalog.shop', $data);
    }

    public function productPage($id)
    {
        $data = WSBaseDataManager::getSiteData();
        $data['product'] = Product::with("images", "stock", "tags", "subcategory", "subcategory.category")->findOrFail($id);
        $data['related_products'] = $data['product']->subcategory->category->products()
        ->limit(3)->get();
        return view('frontend.catalog.product', $data);
    }


    public function subcategory($id, Request $request)
    {
        $subcategory = SubCategory::with('category')->findOrFail($id);

        $applyNewFilters = $request->isMethod('POST');
        //loading applied filters
        if ($applyNewFilters) {
            $priceFilters = $request->price_filters ?? null;
            $sortOption = $request->sort_option ?? null;
        } else {
            $priceFilters =  $request->input('applied_price_filters') ?? null;
            $sortOption =  $request->input('applied_sort_option') ?? null;
        }

        $data = WSBaseDataManager::getCollectionPageData(
            $applyNewFilters,
            WSBaseDataManager::COLLECTION_PAGES[2] /*subcategory page*/,
            Product::ofSubcategory($id) /*base products query*/,
            $priceFilters,
            $sortOption,
            $subcategory,
            $request->per_page ?? ($request->input('per_page') ?? 28) /*perPageValue*/,
        );

        return view("frontend.catalog.collection", $data);
    }

    public function aboutus()
    {
        $data = WSBaseDataManager::getSiteData();
        return view('frontend.aboutus', $data);
    }

    public function delivery()
    {
        $data = WSBaseDataManager::getSiteData();
        return view('frontend.delivery', $data);
    }

    public function paymentPolicy()
    {
        $data = WSBaseDataManager::getSiteData();
        return view('frontend.payment', $data);
    }

    public function contactus()
    {
        $data = WSBaseDataManager::getSiteData();
        return view('frontend.contactus', $data);
    }

    public function sendContactUsEmail(Request $request)
    {
        $request->validate([
            "name"  =>  "required",
            "email"  =>  "required",
            "phone"  =>  "required",
            "message"  =>  "required",
        ]);
        try {
            Mail::to(env('MAIL_TO_ADDRESS'))->send(new ContactUsMail($request->name, $request->email, $request->phone, $request->message));
            return redirect('home')->with("flag", "emailSent");
        } catch (\Exception $e) {
            report($e);
        }
        return redirect('home');
    }

    // public function notfound_404()
    // {
    //     $data = WSBaseDataManager::getSiteData();
    //     return view('frontend.errors.404', $data);
    // }
}
