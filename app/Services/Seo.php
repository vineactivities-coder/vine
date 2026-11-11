<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Seo
{
    public static function publicPath(Request $request): bool
    {
        return (bool) preg_match('~^/(?:|home|shop(?:/[0-9]+)?|product/[0-9]+|aboutus|delivery|payment|contact)$~', $request->getPathInfo());
    }

    public static function productionHost(Request $request): bool
    {
        return config('app.env') === 'production'
            && in_array($request->getHost(), ['vine-activities.com', 'www.vine-activities.com'], true);
    }

    public static function indexable(Request $request): bool
    {
        return self::productionHost($request) && self::publicPath($request);
    }

    public static function page(Request $request, ?Product $product = null): array
    {
        $pages = [
            '/' => ['Vine Activities | وسائل تعليمية مبتكرة لمدارس الأحد', 'وسائل وأنشطة تعليمية مبتكرة تساعد خدام مدارس الأحد على شرح الكتاب المقدس للأطفال بطرق ممتعة.'],
            '/shop' => ['منتجات وأنشطة مدارس الأحد | Vine Activities', 'تصفح وسائل وأنشطة Vine Activities التعليمية بالعربية والإنجليزية لخدمة الأطفال ومدارس الأحد.'],
            '/aboutus' => ['عن Vine Activities', 'تعرف على Vine Activities ورسالتنا في تقديم وسائل فنية وتعليمية مبتكرة للأطفال وخدام مدارس الأحد.'],
            '/delivery' => ['الشحن والتوصيل | Vine Activities', 'تعرف على تفاصيل وسياسة شحن وتوصيل منتجات Vine Activities داخل مصر.'],
            '/payment' => ['سياسة الدفع | Vine Activities', 'تعرف على سياسة وطرق الدفع المتاحة عند طلب منتجات Vine Activities التعليمية.'],
            '/contact' => ['تواصل معنا | Vine Activities', 'تواصل مع فريق Vine Activities للاستفسار عن المنتجات والطلبات والوسائل التعليمية.'],
        ];
        $path = $request->getPathInfo();
        $canonicalPath = $path === '/home' ? '/' : (preg_match('~^/shop/[0-9]+$~', $path) ? '/shop' : $path);
        [$title, $description] = $pages[$canonicalPath] ?? ['Vine Activities', 'وسائل وأنشطة تعليمية للأطفال وخدام مدارس الأحد.'];
        $image = asset('assets/images/vineLogo.png');
        if ($product !== null && preg_match('~^/product/[0-9]+$~', $path)) {
            $title = self::text($product->arabic_name ?: $product->name).' | Vine Activities';
            $description = self::text($product->arabic_desc ?: $product->desc);
            $image = $product->main_image_url;
            $canonicalPath = '/product/'.$product->id;
        }

        return [
            'title' => $title,
            'description' => Str::limit($description ?: 'وسائل وأنشطة تعليمية من Vine Activities.', 155),
            'canonical' => $request->root().($canonicalPath === '/' ? '/' : $canonicalPath),
            'image' => url($image),
            'type' => $product ? 'product' : 'website',
            'robots' => self::indexable($request) ? 'index,follow' : 'noindex,nofollow',
        ];
    }

    private static function text(?string $text): string
    {
        return Str::squish(strip_tags(html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
