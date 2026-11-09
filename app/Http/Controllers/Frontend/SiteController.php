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
use Illuminate\Support\Str;

class SiteController extends Controller
{

    public function home()
    {
        $data = WSBaseDataManager::getHomePageData();
        $data['seo'] = [
            'title' => 'Vine Activities | وسائل تعليمية مبتكرة لمدارس الأحد',
            'description' => 'وسائل وأنشطة تعليمية فنية مبتكرة تساعد خدام مدارس الأحد على شرح الكتاب المقدس للأطفال بطريقة ممتعة.',
            'canonical' => url('/'),
        ];
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

    public function shop(Request $request, $category_id = null)
    {
        $data = WSBaseDataManager::getSiteData();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['nullable', 'in:price_asc,price_desc'],
        ]);

        $categoryId = $filters['category'] ?? $category_id;
        if ($categoryId !== null && ! Category::whereKey($categoryId)->exists()) {
            abort(404);
        }

        $searchText = trim($filters['q'] ?? '');
        $products = $searchText !== '' ? Product::searchQuery($searchText) : Product::query();

        if ($categoryId !== null) {
            $products->whereHas('subcategory', fn ($query) => $query->where('category_id', $categoryId));
        }

        if (($filters['sort'] ?? null) === 'price_asc') {
            $products->orderByRaw(
                'CASE WHEN products.price - COALESCE(products.offer, 0) < 0 THEN 0 ELSE products.price - COALESCE(products.offer, 0) END ASC'
            );
        } elseif (($filters['sort'] ?? null) === 'price_desc') {
            $products->orderByRaw(
                'CASE WHEN products.price - COALESCE(products.offer, 0) < 0 THEN 0 ELSE products.price - COALESCE(products.offer, 0) END DESC'
            );
        } else {
            $products->orderBy('sub_category_id')->orderBy('id');
        }

        $data['categories'] = Category::withCount('products')->orderBy('id')->get();
        $data['products'] = $products->with('subcategory.category')->get();
        $data['category_id'] = $categoryId;
        $data['search_text'] = $searchText;
        $data['sort_option'] = $filters['sort'] ?? '';
        $data['seo'] = [
            'title' => 'منتجات وأنشطة مدارس الأحد | Vine Activities',
            'description' => 'تصفح وسائل وأنشطة Vine Activities التعليمية بالعربية والإنجليزية واختر المنتج المناسب حسب القسم والسعر.',
            'canonical' => $categoryId === null ? route('shop') : route('shop.category', $categoryId),
            'robots' => $searchText !== '' || isset($filters['sort']) ? 'noindex,follow' : 'index,follow',
        ];

        return view('frontend.catalog.shop', $data);
    }

    public function productPage($id)
    {
        $data = WSBaseDataManager::getSiteData();
        $data['product'] = Product::with("images", "stock", "tags", "subcategory", "subcategory.category")->findOrFail($id);
        $data['related_products'] = $data['product']->subcategory->category->products()
            ->with('subcategory.category')
            ->whereKeyNot($data['product']->getKey())
            ->limit(3)
            ->get();
        $data['seo'] = [
            'title' => "{$data['product']->arabic_name} | Vine Activities",
            'description' => Str::limit(strip_tags($data['product']->arabic_desc ?: $data['product']->desc), 155),
            'image' => $data['product']->main_image_url,
            'type' => 'product',
            'canonical' => route('product', $data['product']),
        ];
        return view('frontend.catalog.product', $data);
    }

    public function sitemap()
    {
        return response()
            ->view('frontend.sitemap', [
                'products' => Product::select('id', 'updated_at')->get(),
                'categories' => Category::select('id')->get(),
            ])
            ->header('Content-Type', 'application/xml');
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
        $data['seo'] = [
            'title' => 'عن Vine Activities | وسائل تعليمية لمدارس الأحد',
            'description' => 'تعرف على Vine Activities ورسالتنا في تقديم وسائل فنية وتعليمية مبتكرة للأطفال وخدام مدارس الأحد.',
        ];
        return view('frontend.aboutus', $data);
    }

    public function delivery()
    {
        $data = WSBaseDataManager::getSiteData();
        $data['seo'] = [
            'title' => 'سياسة الشحن والتوصيل | Vine Activities',
            'description' => 'تعرف على تفاصيل وسياسة شحن وتوصيل منتجات Vine Activities داخل مصر.',
        ];
        return view('frontend.delivery', $data);
    }

    public function paymentPolicy()
    {
        $data = WSBaseDataManager::getSiteData();
        $data['seo'] = [
            'title' => 'سياسة الدفع | Vine Activities',
            'description' => 'تعرف على سياسة وطرق الدفع المتاحة عند طلب منتجات Vine Activities التعليمية.',
        ];
        return view('frontend.payment', $data);
    }

    public function contactus()
    {
        $data = WSBaseDataManager::getSiteData();
        $data['seo'] = [
            'title' => 'تواصل معنا | Vine Activities',
            'description' => 'تواصل مع فريق Vine Activities للاستفسار عن المنتجات والطلبات ووسائل مدارس الأحد التعليمية.',
        ];
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
