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

    public function exportCsv(Request $request)
    {
        try {
            Log::info('开始导出Shopify格式的商品CSV', ['request' => $request->all()]);
            
            // 检查是否有指定的商品ID
            $productIds = $request->get('ids');
            if ($productIds) {
                $ids = explode(',', $productIds);
                $products = Product::with('images')->whereIn('id', $ids)->get();
                Log::info('导出指定商品', ['ids' => $ids, 'count' => $products->count()]);
            } else {
                $products = Product::with('images')->get();
                Log::info('导出所有商品', ['count' => $products->count()]);
            }
            
            $filename = $productIds ? 
                'shopify-products-selected-' . date('Y-m-d') . '.csv' : 
                'shopify-products-' . date('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
                    
                    // 处理变体数据
                    $variants = is_array($product->variants) ? $product->variants : (json_decode($product->variants ?? '[]', true) ?: []);
                    if (empty($variants)) {
                        // 如果没有变体数据，创建一个默认变体
                        $variants = [[
                            'option1' => $product->option1_value,
                            'option2' => $product->option2_value,
                            'option3' => $product->option3_value,
                            'sku' => $product->variant_sku,
                            'grams' => $product->variant_grams,
                            'price' => $product->variant_price,
                            'compare_at_price' => $product->variant_compare_price,
                            'requires_shipping' => $product->variant_requires_shipping,
                            'taxable' => $product->variant_taxable,
                            'barcode' => $product->variant_barcode
                        ]];
                    }

                    // 为每个变体创建一行
                    foreach ($variants as $variantIndex => $variant) {
                        $isFirstVariant = $variantIndex === 0;
                        
                        // 如果有图片，为每个变体的每张图片创建一行
                        if ($images->count() > 0) {
                            foreach ($images as $imageIndex => $image) {
                                $isFirstRow = $isFirstVariant && $imageIndex === 0;
                                
                                // 确保图片URL是完整的绝对URL
                                $imageUrl = $image->image_src;
                                if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = url($imageUrl);
                                }
                                
                                fputcsv($file, [
                                    $product->handle, // Handle总是需要的
                                    $isFirstRow ? $product->title : '',
                                    $isFirstRow ? $product->body_html : '',
                                    $isFirstRow ? $product->vendor : '',
                                    $isFirstRow ? $product->type : '',
                                    $isFirstRow ? $product->tags : '',
                                    $isFirstRow ? ($product->published ? 'TRUE' : 'FALSE') : '',
                                    $isFirstRow ? ($product->option1_name ?? '') : '',
                                    $isFirstRow ? ($variant['option1'] ?? $product->option1_value ?? '') : '',
                                    $isFirstRow ? ($product->option2_name ?? '') : '',
                                    $isFirstRow ? ($variant['option2'] ?? $product->option2_value ?? '') : '',
                                    $isFirstRow ? ($product->option3_name ?? '') : '',
                                    $isFirstRow ? ($variant['option3'] ?? $product->option3_value ?? '') : '',
                                    $isFirstRow ? ($variant['sku'] ?? $product->variant_sku ?? '') : '',
                                    $isFirstRow ? ($variant['grams'] ?? $product->variant_grams ?? 0) : '',
                                    $isFirstRow ? ($product->variant_inventory_tracker ?? 'shopify') : '',
                                    $isFirstRow ? ($product->variant_inventory_qty ?? 1000) : '',
                                    $isFirstRow ? ($product->variant_inventory_policy ?? 'continue') : '',
                                    $isFirstRow ? ($product->variant_fulfillment_service ?? 'manual') : '',
                                    $isFirstRow ? ($variant['price'] ?? $product->variant_price ?? '') : '',
                                    $isFirstRow ? ($variant['compare_at_price'] ?? $product->variant_compare_price ?? '') : '',
                                    $isFirstRow ? ($variant['requires_shipping'] ?? $product->variant_requires_shipping ? 'TRUE' : 'FALSE') : '',
                                    $isFirstRow ? ($variant['taxable'] ?? $product->variant_taxable ? 'TRUE' : 'FALSE') : '',
                                    $isFirstRow ? ($variant['barcode'] ?? $product->variant_barcode ?? '') : '',
                                    $imageUrl, // 图片URL总是需要的
                                    $image->position, // 图片位置总是需要的
                                    $image->alt_text ?? '', // 图片Alt文本总是需要的
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
                                    $isFirstRow ? ($variant['image'] ?? $product->variant_image ?? '') : '',
                                    $isFirstRow ? ($product->variant_weight_unit ?? 'Kg') : '', // 注意这里使用'Kg'而不是'kg'
                                    $isFirstRow ? ($product->variant_tax_code ?? '') : '',
                                    $isFirstRow ? ($product->cost_per_item ?? '') : '',
                                    $isFirstRow ? $product->status : '',
                                    $isFirstRow ? ($product->collection ?? '') : ''
                                ]);
                            }
                        } else {
                            // 如果没有图片，只为每个变体创建一行
                            fputcsv($file, [
                                $isFirstVariant ? $product->handle : '',
                                $isFirstVariant ? $product->title : '',
                                $isFirstVariant ? $product->body_html : '',
                                $isFirstVariant ? $product->vendor : '',
                                $isFirstVariant ? $product->type : '',
                                $isFirstVariant ? $product->tags : '',
                                $isFirstVariant ? ($product->published ? 'TRUE' : 'FALSE') : '',
                                $isFirstVariant ? ($product->option1_name ?? '') : '',
                                $variant['option1'] ?? $product->option1_value ?? '',
                                $isFirstVariant ? ($product->option2_name ?? '') : '',
                                $variant['option2'] ?? $product->option2_value ?? '',
                                $isFirstVariant ? ($product->option3_name ?? '') : '',
                                $variant['option3'] ?? $product->option3_value ?? '',
                                $variant['sku'] ?? $product->variant_sku ?? '',
                                $variant['grams'] ?? $product->variant_grams ?? '',
                                $isFirstVariant ? ($product->variant_inventory_tracker ?? 'shopify') : '',
                                $isFirstVariant ? ($product->variant_inventory_qty ?? 1000) : '',
                                $isFirstVariant ? ($product->variant_inventory_policy ?? 'continue') : '',
                                $isFirstVariant ? ($product->variant_fulfillment_service ?? 'manual') : '',
                                $variant['price'] ?? $product->variant_price ?? '',
                                $variant['compare_at_price'] ?? $product->variant_compare_price ?? '',
                                $variant['requires_shipping'] ?? $product->variant_requires_shipping ? 'TRUE' : 'FALSE',
                                $variant['taxable'] ?? $product->variant_taxable ? 'TRUE' : 'FALSE',
                                $variant['barcode'] ?? $product->variant_barcode ?? '',
                                '', // Image Src
                                '', // Image Position
                                '', // Image Alt Text
                                $isFirstVariant ? ($product->gift_card ? 'TRUE' : 'FALSE') : '',
                                $isFirstVariant ? ($product->seo_title ?? $product->title) : '',
                                $isFirstVariant ? ($product->seo_description ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_category ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_gender ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_age_group ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_mpn ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_adwords_grouping ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_adwords_labels ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_condition ?? 'new') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_product ? 'TRUE' : 'FALSE') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_label_0 ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_label_1 ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_label_2 ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_label_3 ?? '') : '',
                                $isFirstVariant ? ($product->google_shopping_custom_label_4 ?? '') : '',
                                $variant['image'] ?? $product->variant_image ?? '',
                                $isFirstVariant ? ($product->variant_weight_unit ?? 'kg') : '',
                                $isFirstVariant ? ($product->variant_tax_code ?? '') : '',
                                $isFirstVariant ? ($product->cost_per_item ?? '') : '',
                                $isFirstVariant ? strtolower($product->status) : '',
                                $isFirstVariant ? ($product->collection ?? '') : ''
                            ]);
                        }
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