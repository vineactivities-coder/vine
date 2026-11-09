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

    public static function searchQuery(string $searchText): Builder
    {
        // Treat user supplied % and _ as ordinary characters instead of SQL
        // wildcards. This keeps a search for either character from matching the
        // entire catalogue while still allowing partial-word searches.
        $searchTerm = '%' . addcslashes($searchText, '\\%_') . '%';

        return self::query()
            ->where(function (Builder $query) use ($searchTerm) {
                $query->where('name', 'LIKE', $searchTerm)
                    ->orWhere('arabic_name', 'LIKE', $searchTerm)
                    ->orWhere('desc', 'LIKE', $searchTerm)
                    ->orWhere('arabic_desc', 'LIKE', $searchTerm)
                    ->orWhereHas('subcategory', function (Builder $subcategory) use ($searchTerm) {
                        $subcategory->where('name', 'LIKE', $searchTerm)
                            ->orWhere('arabic_name', 'LIKE', $searchTerm)
                            ->orWhereHas('category', function (Builder $category) use ($searchTerm) {
                                $category->where('name', 'LIKE', $searchTerm)
                                    ->orWhere('arabic_name', 'LIKE', $searchTerm);
                            });
                    });
            });
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

        if ($this->mainImage?->full_image_url) {
            return $this->mainImage->full_image_url;
        }

        // A deterministic fallback keeps product cards, social previews, and
        // structured data stable between requests when no main image is set.
        return $this->images->first()?->full_image_url
            ?? asset('assets/img/works/journals/1.jpg');
    }

    public function getMainImageIdAttribute(): ?int
    {
        return $this->product_image_id;
    }

    public function getFinalPriceAttribute(): ?float
    {
        return max(0, (float) $this->price - (float) $this->offer);
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
