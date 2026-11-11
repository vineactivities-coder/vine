<?php

namespace App\Models;

use App\Services\FileManager;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $table = "products";
    public $timestamps = true;

    protected $appends = [
        "main_image_url", "final_price"
    ];

    //CRUD queries
    public static function create(string $name, string $arbcName, string $desc, string $arbcDesc, int $category, float $price, $material, $dimensions, $handled_topics=null, float $offer=null): Product
    {
        $product = new Product();
        $product->name = $name;
        $product->arabic_name = $arbcName;
        $product->desc = $desc;
        $product->arabic_desc = $arbcDesc;
        $product->sub_category_id = $category;
        $product->price = $price;
        $product->offer = $offer ?? 0;
        $product->material = $material;
        $product->dimensions = $dimensions;
        $product->handled_topics = $handled_topics;
        $product->save();
        return $product;
    }

    public function modify(string $name, string $arbcName, string $desc, string $arbcDesc, int $category, float $price, $material, $dimensions, $handled_topics=null, ?float $offer=null, ?Carbon $created_at=null): bool
    {

        $this->name = $name;
        $this->arabic_name = $arbcName;
        $this->desc = $desc;
        $this->arabic_desc = $arbcDesc;
        $this->sub_category_id = $category;
        $this->price = $price;
        $this->offer = $offer ?? 0;
        $this->material = $material;
        $this->dimensions = $dimensions;
        $this->handled_topics = $handled_topics;
        $this->created_at = $created_at ?? now();
        return $this->save();
    }

    public static function searchQuery(string $search_text) : Builder {
        return self::select('products.*')
            ->join('prod_tag', 'prod_tag.product_id', 'prod_tag.tag_id')
            ->join('tags', 'tag', '=', 'tags.id')
            ->join('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->orWhere('tags.name', 'LIKE', "%{$search_text}%")
            ->orWhere('sub_categories.name', 'LIKE', "%{$search_text}%")
            ->orWhere('products.name', 'LIKE', "%{$search_text}%");
    }

    //function 
    public function addImage($image): ProductImage
    {
        $newImage = new ProductImage();
        $path = FileManager::save($image, "products");
        $newImage->image_url = $path;
        $newImage->product_id = $this->id;

        $newImage->save();
        return $newImage;
    }

    public function setMainImage($imageID)
    {
        $this->product_image_id = $imageID;
        $this->save();
    }

    //accessors
    public function getMainImageUrlAttribute(): ?string
    {
        $this->loadMissing('mainImage', 'images');
        if (is_null($this->mainImage)) {
            return ($this->images->isNotEmpty()) ? $this->images->random()->full_image_url : asset('assets/img/works/journals/1.jpg');
        } else {
            return $this->mainImage->full_image_url ?? asset('assets/img/works/journals/1.jpg');
        }
        return asset('assets/img/works/journals/1.jpg');
    }

    public function getMainImageIdAttribute(): ?int
    {
        return $this->product_image_id;
    }

    public function getFinalPriceAttribute(): ?float
    {
        return $this->price - $this->offer;
    }

    public function getCategoryNameAttribute(): ?string
    {
        $this->loadMissing('subcategory');
        return $this->subcategory->name;
    }

    //relations
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function mainImage()
    {
        return $this->belongsTo(ProductImage::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    public function stock()
    {
        return $this->hasMany(Inventory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, "prod_tag");
    }


    //scopes and queries
    // public static function newArrivals($dateInterval)
    // {
    //     return self::with('subcategory')
    //         ->join("inventory", "product_id", "=", "products.id")
    //         ->select("products.*")->selectRaw("SUM(amount) as stock")
    //         ->groupBy("products.id")
    //         ->where("products.created_at", ">", (new DateTime())->sub(new DateInterval($dateInterval)))
    //         ->get();
    // }

    public function scopeOfSubcategory($query, $subcategoryID)
    {
        return $query->where("sub_category_id", $subcategoryID);
    }

    public function scopeOnSale($query){
        return $query->where('products.offer', '>', 0)->orderBy('products.offer');
    }

    public function scopeNewArrivals($query, $dateInterval){
        return $query->where("products.created_at", ">", (new DateTime())->sub(new DateInterval($dateInterval)));
    }

    private function hasJoin(\Illuminate\Database\Query\Builder $Builder, $table)
    {
        if ($Builder->joins)
            foreach ($Builder->joins as $JoinClause) {
                if ($JoinClause->table == $table) {
                    return true;
                }
            }
        return false;
    }
}
