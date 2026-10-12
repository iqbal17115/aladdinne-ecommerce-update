<?php

namespace App\Http\Controllers\Shop;

use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class BulkProductExportController extends Controller
{
    public function index()
    {
        return view('shop.bulk-product.export');
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:all,facebook_catalog_csv,facebook_catalog_xlsx',
        ]);

        $type = $request->type;
        $shop = generaleSetting('shop');

        if (in_array($type, ['facebook_catalog_csv', 'facebook_catalog_xlsx'], true)) {
            return $this->exportFacebookCatalog($shop, $type);
        }

        $products = $shop?->products;

        $exportData = collect(
            [
                [
                    'id',
                    'name',
                    'thumbnails',
                    'category',
                    'sub category',
                    'brand',
                    'colors',
                    'sizes',
                    'price',
                    'discount price',
                    'product sku',
                    'stock qty',
                    'short description',
                    'description',
                ],
            ]
        );

        foreach ($products as $product) {
            $thumbnails = [];

            if (filled($product->product_thumbnail) && Storage::exists($product->product_thumbnail)) {
                $thumbnails[] = basename($product->product_thumbnail);
            } elseif ($product->media && filled($product->media->src) && Storage::exists($product->media->src)) {
                $thumbnails[] = $product->media->original_name;
            }

            foreach ($product->medias as $media) {
                if (filled($media->src) && Storage::exists($media->src)) {
                    $thumbnails[] = $media->original_name;
                }
            }

            $categories = $product->categories->pluck('name')->toArray();
            $colors = $product->colors->pluck('name')->toArray();
            $sizes = $product->sizes->pluck('name')->toArray();
            $subCategories = $product->subcategories->pluck('name')->toArray();

            $exportData[] = [
                $product->id,
                $product->name,
                implode(',', $thumbnails),
                implode(',', $categories),
                implode(',', $subCategories),
                $product->brand?->name,
                implode(',', $colors),
                implode(',', $sizes),
                $product->price,
                $product->discount_price ?? 0,
                $product->code,
                $product->quantity ?? 0,
                $product->short_description,
                $product->description,
            ];
        }

        return Excel::download(new TemplateExport($exportData), 'products.xlsx');
    }

    private function exportFacebookCatalog(?Shop $shop, string $type)
    {
        $currency = generaleSetting('defaultCurrency');
        $currencyCode = strtoupper(trim((string) ($currency?->code ?: $currency?->name)));
        $currencyCode = $currencyCode === 'TAKA' ? 'BDT' : $currencyCode;

        if (! preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            return back()->withErrors([
                'type' => __('Set a valid 3-letter ISO currency code for your default currency before exporting a Facebook catalog feed.'),
            ]);
        }

        $headers = [
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'brand',
            'google_product_category',
            'fb_product_category',
            'quantity_to_sell_on_facebook',
            'sale_price',
            'sale_price_effective_date',
            'item_group_id',
            'gender',
            'color',
            'size',
            'age_group',
            'material',
            'pattern',
            'shipping',
            'shipping_weight',
            'gtin',
            'mpn',
        ];

        $exportData = collect([$headers]);
        $products = $shop?->products()
            ->isActive()
            ->with(['brand', 'media', 'categories', 'colors', 'sizes', 'medias.media'])
            ->get() ?? collect();

        foreach ($products as $product) {
            $colors = $product->colors->isNotEmpty() ? $product->colors : collect([null]);
            $sizes = $product->sizes->isNotEmpty() ? $product->sizes : collect([null]);
            $hasVariants = $product->colors->isNotEmpty() || $product->sizes->isNotEmpty();
            $baseSku = filled($product->code) ? (string) $product->code : (string) $product->id;
            $groupId = $hasVariants ? $baseSku : '';
            $category = $product->categories->first()?->name ?? '';
            $brand = $product->brand?->name ?: ($shop?->name ?? '');
            $description = strip_tags((string) ($product->description ?: $product->short_description ?: $product->name));
            $description = html_entity_decode(preg_replace('/\s+/', ' ', $description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $description = mb_substr(trim($description), 0, 9999);
            $link = url('/products/'.$product->slug.'/details');
            $availability = (int) $product->quantity > 0 ? 'in stock' : 'out of stock';
            $weight = (float) $product->product_weight > 0
                ? rtrim(rtrim(number_format((float) $product->product_weight, 3, '.', ''), '0'), '.').' kg'
                : '';

            if (! filled($product->slug)) {
                return back()->withErrors([
                    'type' => __('Every active product needs a product URL before exporting a Facebook catalog feed.'),
                ]);
            }

            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $variantSuffix = collect([
                        $color ? 'C'.$color->id : null,
                        $size ? 'S'.$size->id : null,
                    ])->filter()->implode('-');
                    $variantSku = $baseSku.($variantSuffix !== '' ? '-'.$variantSuffix : '');
                    $imagePath = $this->facebookCatalogImagePath($product, $color);
                    $imageUrl = $imagePath ? Storage::url($imagePath) : '';
                    if ($imageUrl !== '' && ! preg_match('/^https?:\/\//i', $imageUrl)) {
                        $imageUrl = url($imageUrl);
                    }

                    $variantPrice = (float) $product->price
                        + (float) ($color?->pivot?->price ?? 0)
                        + (float) ($size?->pivot?->price ?? 0);
                    $variantSalePrice = (float) $product->discount_price > 0
                        ? (float) $product->discount_price
                            + (float) ($color?->pivot?->price ?? 0)
                            + (float) ($size?->pivot?->price ?? 0)
                        : 0;
                    $title = $product->name;
                    $variantTitle = collect([$color?->name, $size?->name])->filter()->implode(' / ');

                    $exportData[] = [
                        $variantSku,
                        mb_substr($title, 0, 200),
                        $description,
                        $availability,
                        'new',
                        number_format($variantPrice, 2, '.', '').' '.$currencyCode,
                        $link,
                        $imageUrl,
                        $brand,
                        $category,
                        $category,
                        $hasVariants ? '' : (int) $product->quantity,
                        $variantSalePrice > 0
                            ? number_format($variantSalePrice, 2, '.', '').' '.$currencyCode
                            : '',
                        '',
                        $groupId,
                        '',
                        $color?->name ?? '',
                        $size?->name ?? '',
                        '',
                        '',
                        '',
                        '',
                        $weight,
                        '',
                        $variantSku,
                    ];
                }
            }
        }

        $isCsv = $type === 'facebook_catalog_csv';

        return Excel::download(
            new TemplateExport($exportData),
            $isCsv ? 'facebook_catalog.csv' : 'facebook_catalog.xlsx',
            $isCsv ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    private function facebookCatalogImagePath(Product $product, ?Color $color): ?string
    {
        $imagePaths = collect([
            $color?->pivot?->img,
            $product->product_thumbnail,
            $product->media?->src,
        ])->merge($product->medias->flatMap(fn ($media) => [
            $media->thumbnail,
            $media->media?->src,
        ]));

        foreach ($imagePaths->filter()->unique() as $imagePath) {
            if (Storage::exists($imagePath)) {
                return $imagePath;
            }
        }

        return null;
    }

    // export for demo
    public function demoExport(Request $request)
    {
        $shop = generaleSetting('shop');

        $exportData = collect(
            [
                [
                    'id',
                    'name',
                    'thumbnails',
                    'category',
                    'sub category',
                    'brand',
                    'colors',
                    'sizes',
                    'price',
                    'discount price',
                    'product sku',
                    'stock qty',
                    'short description',
                    'description',
                ],
            ]
        );

        $thumbnails = [];

        // get first product
        $product = $shop?->products()?->first();

        // check if product exists
        if ($product) {

            // check if media exists
            if (filled($product->product_thumbnail) && Storage::exists($product->product_thumbnail)) {
                $thumbnails[] = basename($product->product_thumbnail);
            } elseif ($product->media && filled($product->media->src) && Storage::exists($product->media->src)) {
                $thumbnails[] = $product->media->original_name;
            }

            // check if media exists
            foreach ($product->medias as $media) {
                if (filled($media->src) && Storage::exists($media->src)) {
                    $thumbnails[] = $media->original_name;
                }
            }

            $categories = $product->categories->pluck('name')->toArray();
            $colors = $product->colors->pluck('name')->toArray();
            $sizes = $product->sizes->pluck('name')->toArray();
            $subCategories = $product->subcategories->pluck('name')->toArray();

            $exportData[] = [
                0,
                $product->name,
                implode(',', $thumbnails),
                implode(',', $categories),
                implode(',', $subCategories),
                $product->brand?->name,
                implode(',', $colors),
                implode(',', $sizes),
                $product->price,
                $product->discount_price ?? 0,
                $product->code,
                $product->quantity ?? 0,
                $product->short_description,
                $product->description,
            ];
        }

        return Excel::download(new TemplateExport($exportData), 'demo-template.xlsx');
    }
}
