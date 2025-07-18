<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        try {
            Log::info('开始获取商品列表');
            $products = Product::all();
            
            Log::info('获取商品列表成功', [
                'count' => $products->count(),
                'data' => $products->toArray()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '获取商品列表成功',
                'data' => $products->toArray()
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取商品列表失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取商品列表失败: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('接收到创建商品请求', ['data' => $request->all()]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'handle' => 'required|string|max:255|unique:products',
                'body_html' => 'nullable|string',
                'vendor' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255',
                'tags' => 'nullable|string',
                'published' => 'boolean',
                'variant_sku' => 'nullable|string|max:255',
                'variant_price' => 'required|numeric|min:0',
                'variant_compare_price' => 'nullable|numeric|min:0',
                'status' => 'required|string|in:active,draft'
            ]);

            Log::info('商品数据验证通过', ['validated' => $validated]);

            $product = Product::create($validated);
            Log::info('商品创建成功', ['product' => $product]);
            
            return response()->json([
                'status' => 'success',
                'message' => '商品创建成功',
                'data' => $product
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('商品创建验证失败', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('商品创建失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => '商品创建失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            Log::info('接收到更新商品请求', [
                'product_id' => $product->id,
                'data' => $request->all()
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body_html' => 'nullable|string',
                'vendor' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255',
                'tags' => 'nullable|string',
                'published' => 'boolean',
                'variant_sku' => 'nullable|string|max:255',
                'variant_price' => 'required|numeric|min:0',
                'variant_compare_price' => 'nullable|numeric|min:0',
                'status' => 'required|string|in:active,draft'
            ]);

            Log::info('商品数据验证通过', ['validated' => $validated]);

            $product->update($validated);
            Log::info('商品更新成功', ['product' => $product]);

            return response()->json([
                'status' => 'success',
                'message' => '商品更新成功',
                'data' => $product
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('商品更新验证失败', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('商品更新失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => '商品更新失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            Log::info('接收到删除商品请求', ['product_id' => $product->id]);
            $product->delete();
            Log::info('商品删除成功', ['product_id' => $product->id]);
            
            return response()->json([
                'status' => 'success',
                'message' => '商品删除成功',
                'data' => null
            ], 204);
            
        } catch (\Exception $e) {
            Log::error('商品删除失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => '商品删除失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function exportCsv()
    {
        try {
            Log::info('开始导出Shopify格式的商品CSV');
            $products = Product::with('images')->get();
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="shopify-products-' . date('Y-m-d') . '.csv"',
            ];
            
            $callback = function() use ($products) {
                $file = fopen('php://output', 'w');
                
                // 添加UTF-8 BOM以确保Excel正确显示中文
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // 添加完整的Shopify格式CSV头（与示例CSV文件一致）
                fputcsv($file, [
                    'Handle',
                    'Title', 
                    'Body (HTML)',
                    'Vendor',
                    'Type',
                    'Tags',
                    'Published',
                    'Option1 Name',
                    'Option1 Value',
                    'Option2 Name',
                    'Option2 Value',
                    'Option3 Name',
                    'Option3 Value',
                    'Variant SKU',
                    'Variant Grams',
                    'Variant Inventory Tracker',
                    'Variant Inventory Qty',
                    'Variant Inventory Policy',
                    'Variant Fulfillment Service',
                    'Variant Price',
                    'Variant Compare At Price',
                    'Variant Requires Shipping',
                    'Variant Taxable',
                    'Variant Barcode',
                    'Image Src',
                    'Image Position',
                    'Image Alt Text',
                    'Gift Card',
                    'SEO Title',
                    'SEO Description',
                    'Google Shopping / Google Product Category',
                    'Google Shopping / Gender',
                    'Google Shopping / Age Group',
                    'Google Shopping / MPN',
                    'Google Shopping / AdWords Grouping',
                    'Google Shopping / AdWords Labels',
                    'Google Shopping / Condition',
                    'Google Shopping / Custom Product',
                    'Google Shopping / Custom Label 0',
                    'Google Shopping / Custom Label 1',
                    'Google Shopping / Custom Label 2',
                    'Google Shopping / Custom Label 3',
                    'Google Shopping / Custom Label 4',
                    'Variant Image',
                    'Variant Weight Unit',
                    'Variant Tax Code',
                    'Cost per item',
                    'Status',
                    'Collection'
                ]);
                
                // 为每个商品添加数据行
                foreach ($products as $product) {
                    $images = $product->images->sortBy('position');
                    
                    if ($images->count() > 0) {
                        // 如果有图片，为每张图片创建一行（第一行包含所有商品信息，后续行只包含图片信息）
                        foreach ($images as $index => $image) {
                            $isFirstRow = $index === 0;
                            
                            // 确保图片URL是完整的绝对URL
                            $imageUrl = $image->image_src;
                            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                                $imageUrl = url($imageUrl);
                            }
                            
                            fputcsv($file, [
                                $isFirstRow ? $product->handle : '',
                                $isFirstRow ? $product->title : '',
                                $isFirstRow ? $product->body_html : '',
                                $isFirstRow ? $product->vendor : '',
                                $isFirstRow ? $product->type : '',
                                $isFirstRow ? $product->tags : '',
                                $isFirstRow ? ($product->published ? 'TRUE' : 'FALSE') : '',
                                $isFirstRow ? ($product->option1_name ?? '') : '',
                                '', // Option1 Value - 将在商品变体中设置
                                $isFirstRow ? ($product->option2_name ?? '') : '',
                                '', // Option2 Value - 将在商品变体中设置
                                $isFirstRow ? ($product->option3_name ?? '') : '',
                                '', // Option3 Value - 将在商品变体中设置
                                $isFirstRow ? $product->variant_sku : '',
                                $isFirstRow ? ($product->variant_grams ?? '') : '',
                                $isFirstRow ? ($product->variant_inventory_tracker ?? 'shopify') : '',
                                $isFirstRow ? ($product->variant_inventory_qty ?? 1000) : '',
                                $isFirstRow ? ($product->variant_inventory_policy ?? 'continue') : '',
                                $isFirstRow ? ($product->variant_fulfillment_service ?? 'manual') : '',
                                $isFirstRow ? $product->variant_price : '',
                                $isFirstRow ? $product->variant_compare_price : '',
                                $isFirstRow ? ($product->variant_requires_shipping ? 'TRUE' : 'FALSE') : '',
                                $isFirstRow ? ($product->variant_taxable ? 'TRUE' : 'FALSE') : '',
                                $isFirstRow ? ($product->variant_barcode ?? '') : '',
                                $imageUrl, // 完整的图片URL
                                $image->position, // Image Position
                                $image->alt_text, // Image Alt Text
                                $isFirstRow ? ($product->gift_card ? 'TRUE' : 'FALSE') : '',
                                $isFirstRow ? ($product->seo_title ?? $product->title) : '',
                                $isFirstRow ? ($product->seo_description ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_category ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_gender ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_age_group ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_mpn ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_adwords_grouping ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_adwords_labels ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_condition ?? 'new') : '',
                                $isFirstRow ? ($product->google_shopping_custom_product ? 'TRUE' : 'FALSE') : '',
                                $isFirstRow ? ($product->google_shopping_custom_label_0 ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_custom_label_1 ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_custom_label_2 ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_custom_label_3 ?? '') : '',
                                $isFirstRow ? ($product->google_shopping_custom_label_4 ?? '') : '',
                                $isFirstRow ? ($product->variant_image ?? '') : '',
                                $isFirstRow ? ($product->variant_weight_unit ?? 'kg') : '',
                                $isFirstRow ? ($product->variant_tax_code ?? '') : '',
                                $isFirstRow ? ($product->cost_per_item ?? '') : '',
                                $isFirstRow ? strtolower($product->status) : '',
                                $isFirstRow ? ($product->collection ?? '') : ''
                            ]);
                        }
                    } else {
                        // 如果没有图片，只创建一行商品信息
                        fputcsv($file, [
                            $product->handle,
                            $product->title,
                            $product->body_html,
                            $product->vendor,
                            $product->type,
                            $product->tags,
                            $product->published ? 'TRUE' : 'FALSE',
                            $product->option1_name ?? '',
                            '', // Option1 Value - 将在商品变体中设置
                            $product->option2_name ?? '',
                            '', // Option2 Value - 将在商品变体中设置
                            $product->option3_name ?? '',
                            '', // Option3 Value - 将在商品变体中设置
                            $product->variant_sku,
                            $product->variant_grams ?? '',
                            $product->variant_inventory_tracker ?? 'shopify',
                            $product->variant_inventory_qty ?? 1000,
                            $product->variant_inventory_policy ?? 'continue',
                            $product->variant_fulfillment_service ?? 'manual',
                            $product->variant_price,
                            $product->variant_compare_price,
                            $product->variant_requires_shipping ? 'TRUE' : 'FALSE',
                            $product->variant_taxable ? 'TRUE' : 'FALSE',
                            $product->variant_barcode ?? '',
                            '', // Image Src
                            '', // Image Position
                            '', // Image Alt Text
                            $product->gift_card ? 'TRUE' : 'FALSE',
                            $product->seo_title ?? $product->title,
                            $product->seo_description ?? '',
                            $product->google_shopping_category ?? '',
                            $product->google_shopping_gender ?? '',
                            $product->google_shopping_age_group ?? '',
                            $product->google_shopping_mpn ?? '',
                            $product->google_shopping_adwords_grouping ?? '',
                            $product->google_shopping_adwords_labels ?? '',
                            $product->google_shopping_condition ?? 'new',
                            $product->google_shopping_custom_product ? 'TRUE' : 'FALSE',
                            $product->google_shopping_custom_label_0 ?? '',
                            $product->google_shopping_custom_label_1 ?? '',
                            $product->google_shopping_custom_label_2 ?? '',
                            $product->google_shopping_custom_label_3 ?? '',
                            $product->google_shopping_custom_label_4 ?? '',
                            $product->variant_image ?? '',
                            $product->variant_weight_unit ?? 'kg',
                            $product->variant_tax_code ?? '',
                            $product->cost_per_item ?? '',
                            strtolower($product->status),
                            $product->collection ?? ''
                        ]);
                    }
                }
                
                fclose($file);
            };
            
            Log::info('Shopify格式商品CSV导出成功', ['count' => $products->count()]);
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Shopify格式商品CSV导出失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Shopify格式商品CSV导出失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
} 