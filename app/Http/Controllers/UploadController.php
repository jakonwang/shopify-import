<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('开始处理单文件上传请求', [
            'has_file' => $request->hasFile('file'),
            'template_id' => $request->input('template_id')
        ]);

        // 检查是否有多个文件
        if ($request->hasFile('file') && is_array($request->file('file'))) {
            // 多文件上传，转到uploadFolder方法
            return $this->uploadFolder($request);
        }
        
        try {
            $request->validate([
                'file' => 'required|file|image|max:5120', // 5MB
                'template_id' => 'required|exists:templates,id'
            ]);

            // 获取文件夹名称（作为商品名称）
            $folderName = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
            
            // 保存图片
            $path = $request->file('file')->store('products', 'public');
            
            // 检查是否已存在同名商品
            $existingProduct = Product::where('title', $folderName)->first();
            
            if ($existingProduct) {
                // 如果商品已存在，添加新图片
                $position = $existingProduct->images()->max('position') + 1;
                ProductImage::create([
                    'product_id' => $existingProduct->id,
                    'image_src' => Storage::url($path),
                    'position' => $position,
                    'alt_text' => $existingProduct->title
                ]);

                return response()->json([
                    'message' => '图片已添加到现有商品',
                    'product' => $existingProduct->load('images')
                ]);
            } else {
                // 获取模板信息
                $template = Template::findOrFail($request->template_id);
                
                // 生成商品标题（使用模板的title_format）
                $productTitle = $this->generateProductTitle($template->title_format, $folderName);
                
                // 准备基础商品数据
                $productData = [
                    'handle' => Product::generateHandle($productTitle),
                    'title' => $productTitle,
                    'body_html' => $template->body_html,
                    'vendor' => $template->vendor,
                    'type' => $template->type,
                    'variant_price' => $template->price,
                    'variant_compare_price' => $template->compare_at_price,
                    'status' => $template->status,
                    'published' => true,
                    'collection' => $template->collection,
                    
                    // 产品选项字段
                    'option1_name' => $template->option1_name,
                    'option2_name' => $template->option2_name,
                    'option3_name' => $template->option3_name,
                    
                    // 确保设置option1_value_title
                    'option1_value' => $template->option1_value ?? $folderName, // 使用文件夹名称作为默认值
                    'option2_value' => $template->option2_value,
                    'option3_value' => $template->option3_value,
                    
                    // 变体信息字段
                    'variant_grams' => $template->variant_grams,
                    'variant_inventory_tracker' => $template->variant_inventory_tracker ?? 'shopify',
                    'variant_inventory_qty' => $template->variant_inventory_qty ?? 1000,
                    'variant_inventory_policy' => $template->variant_inventory_policy ?? 'continue',
                    'variant_fulfillment_service' => $template->variant_fulfillment_service ?? 'manual',
                    'variant_requires_shipping' => $template->variant_requires_shipping ?? true,
                    'variant_taxable' => $template->variant_taxable ?? true,
                    'variant_barcode' => $template->variant_barcode,
                    'variant_weight_unit' => $template->variant_weight_unit ?? 'kg',
                    'variant_tax_code' => $template->variant_tax_code,
                    'cost_per_item' => $template->cost_per_item,
                    
                    // 礼品卡
                    'gift_card' => $template->gift_card ?? false,
                    
                    // SEO字段
                    'seo_title' => $template->seo_title,
                    'seo_description' => $template->seo_description,
                    
                    // Google Shopping字段
                    'google_shopping_category' => $template->google_shopping_category,
                    'google_shopping_gender' => $template->google_shopping_gender,
                    'google_shopping_age_group' => $template->google_shopping_age_group,
                    'google_shopping_mpn' => $template->google_shopping_mpn,
                    'google_shopping_adwords_grouping' => $template->google_shopping_adwords_grouping,
                    'google_shopping_adwords_labels' => $template->google_shopping_adwords_labels,
                    'google_shopping_condition' => $template->google_shopping_condition ?? 'new',
                    'google_shopping_custom_product' => $template->google_shopping_custom_product ?? false,
                    'google_shopping_custom_label_0' => $template->google_shopping_custom_label_0,
                    'google_shopping_custom_label_1' => $template->google_shopping_custom_label_1,
                    'google_shopping_custom_label_2' => $template->google_shopping_custom_label_2,
                    'google_shopping_custom_label_3' => $template->google_shopping_custom_label_3,
                    'google_shopping_custom_label_4' => $template->google_shopping_custom_label_4
                ];

                // 处理新的变体数据格式
                if ($template->product_options && $template->variants) {
                    // 使用新的变体数据格式
                    $productData['product_options'] = $template->product_options;
                    $productData['variants'] = $template->variants;
                    
                    // 为兼容性设置选项值
                    if (!empty($template->product_options[0])) {
                        $productData['option1_name'] = $template->product_options[0]['name'] ?? '';
                        $productData['option1_value'] = $template->product_options[0]['values'][0] ?? $folderName;
                    }
                    if (!empty($template->product_options[1])) {
                        $productData['option2_name'] = $template->product_options[1]['name'] ?? '';
                        $productData['option2_value'] = $template->product_options[1]['values'][0] ?? '';
                    }
                    if (!empty($template->product_options[2])) {
                        $productData['option3_name'] = $template->product_options[2]['name'] ?? '';
                        $productData['option3_value'] = $template->product_options[2]['values'][0] ?? '';
                    }
                }

                // 记录日志
                Log::info('创建商品数据', [
                    'title' => $productData['title'],
                    'option1_name' => $productData['option1_name'],
                    'option1_value' => $productData['option1_value'],
                    'has_product_options' => isset($productData['product_options']),
                    'has_variants' => isset($productData['variants'])
                ]);

                try {
                    DB::beginTransaction();
                    
                    $product = Product::create($productData);

                    // 创建商品图片
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_src' => Storage::url($path),
                        'position' => 1,
                        'alt_text' => $product->title
                    ]);

                    DB::commit();

                    return response()->json([
                        'message' => '新商品创建成功',
                        'product' => $product->load('images')
                    ], 201);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('商品创建失败', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'product_data' => $productData
                    ]);
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            Log::error('上传失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => '上传失败: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFolder(Request $request)
    {
        Log::info('开始处理文件夹上传请求', [
            'has_files' => $request->hasFile('files'),
            'has_file_array' => $request->hasFile('file') && is_array($request->file('file')),
            'template_id' => $request->input('template_id'),
            'folder_name' => $request->input('folder_name')
        ]);

        // 兼容两种格式：files[]数组 或 file数组
        $files = null;
        try {
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $request->validate([
                    'files.*' => 'required|file|image|max:5120', // 5MB
                    'template_id' => 'required|exists:templates,id'
                ]);
            } elseif ($request->hasFile('file') && is_array($request->file('file'))) {
                $files = $request->file('file');
                $request->validate([
                    'file.*' => 'required|file|image|max:5120', // 5MB
                    'template_id' => 'required|exists:templates,id'
                ]);
            } else {
                Log::warning('没有找到要上传的文件', [
                    'request_data' => $request->all()
                ]);
                return response()->json([
                    'message' => '没有找到要上传的文件',
                    'error' => 'No files found'
                ], 400);
            }

            $folderName = $request->input('folder_name');
            if (!$folderName) {
                // 如果没有提供文件夹名称，使用第一个文件的文件名（去除扩展名）
                $firstFile = $files[0];
                $folderName = pathinfo($firstFile->getClientOriginalName(), PATHINFO_FILENAME);
            }

            Log::info('处理文件夹', [
                'folder_name' => $folderName,
                'files_count' => count($files)
            ]);

            // 获取模板并生成商品标题
            $template = Template::findOrFail($request->template_id);
            
            // 生成商品标题（使用模板的title_format）
            $productTitle = $this->generateProductTitle($template->title_format, $folderName);
            
            // 准备基础商品数据
            $productData = [
                'handle' => Product::generateHandle($productTitle),
                'title' => $productTitle,
                'body_html' => $template->body_html,
                'vendor' => $template->vendor,
                'type' => $template->type,
                'variant_price' => $template->price,
                'variant_compare_price' => $template->compare_at_price,
                'status' => $template->status,
                'published' => true,
                'collection' => $template->collection,
                
                // 产品选项字段
                'option1_name' => $template->option1_name,
                'option2_name' => $template->option2_name,
                'option3_name' => $template->option3_name,
                
                // 确保设置option1_value_title
                'option1_value' => $template->option1_value ?? $folderName, // 使用文件夹名称作为默认值
                'option2_value' => $template->option2_value,
                'option3_value' => $template->option3_value,
                
                // 变体信息字段
                'variant_grams' => $template->variant_grams,
                'variant_inventory_tracker' => $template->variant_inventory_tracker ?? 'shopify',
                'variant_inventory_qty' => $template->variant_inventory_qty ?? 1000,
                'variant_inventory_policy' => $template->variant_inventory_policy ?? 'continue',
                'variant_fulfillment_service' => $template->variant_fulfillment_service ?? 'manual',
                'variant_requires_shipping' => $template->variant_requires_shipping ?? true,
                'variant_taxable' => $template->variant_taxable ?? true,
                'variant_barcode' => $template->variant_barcode,
                'variant_weight_unit' => $template->variant_weight_unit ?? 'kg',
                'variant_tax_code' => $template->variant_tax_code,
                'cost_per_item' => $template->cost_per_item,
                
                // 礼品卡
                'gift_card' => $template->gift_card ?? false,
                
                // SEO字段
                'seo_title' => $template->seo_title,
                'seo_description' => $template->seo_description,
                
                // Google Shopping字段
                'google_shopping_category' => $template->google_shopping_category,
                'google_shopping_gender' => $template->google_shopping_gender,
                'google_shopping_age_group' => $template->google_shopping_age_group,
                'google_shopping_mpn' => $template->google_shopping_mpn,
                'google_shopping_adwords_grouping' => $template->google_shopping_adwords_grouping,
                'google_shopping_adwords_labels' => $template->google_shopping_adwords_labels,
                'google_shopping_condition' => $template->google_shopping_condition ?? 'new',
                'google_shopping_custom_product' => $template->google_shopping_custom_product ?? false,
                'google_shopping_custom_label_0' => $template->google_shopping_custom_label_0,
                'google_shopping_custom_label_1' => $template->google_shopping_custom_label_1,
                'google_shopping_custom_label_2' => $template->google_shopping_custom_label_2,
                'google_shopping_custom_label_3' => $template->google_shopping_custom_label_3,
                'google_shopping_custom_label_4' => $template->google_shopping_custom_label_4,
            ];
            
            // 处理新的变体数据格式
            if ($template->product_options && $template->variants) {
                // 使用新的变体数据格式
                $productData['product_options'] = $template->product_options;
                $productData['variants'] = $template->variants;
                
                // 为兼容性设置选项值
                if (!empty($template->product_options[0])) {
                    $productData['option1_name'] = $template->product_options[0]['name'] ?? '';
                    $productData['option1_value'] = $template->product_options[0]['values'][0] ?? $folderName;
                }
                if (!empty($template->product_options[1])) {
                    $productData['option2_name'] = $template->product_options[1]['name'] ?? '';
                    $productData['option2_value'] = $template->product_options[1]['values'][0] ?? '';
                }
                if (!empty($template->product_options[2])) {
                    $productData['option3_name'] = $template->product_options[2]['name'] ?? '';
                    $productData['option3_value'] = $template->product_options[2]['values'][0] ?? '';
                }
            }

            // 记录日志
            Log::info('创建商品数据', [
                'title' => $productData['title'],
                'option1_name' => $productData['option1_name'],
                'option1_value' => $productData['option1_value'],
                'has_product_options' => isset($productData['product_options']),
                'has_variants' => isset($productData['variants'])
            ]);

            try {
                DB::beginTransaction();
                
                $product = Product::create($productData);

                // 保存所有图片
                foreach ($files as $index => $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_src' => Storage::url($path),
                        'position' => $index + 1,
                        'alt_text' => $product->title
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => '新商品创建成功',
                    'product' => $product->load('images')
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('商品创建失败', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'product_data' => $productData
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('上传失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => '上传失败: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function generateProductTitle($titleFormat, $folderName)
    {
        // 替换模板中的变量
        $title = $titleFormat;
        
        // 替换文件夹名称
        $title = str_replace('{folder_name}', $folderName, $title);
        
        // 替换前缀和后缀（如果存在）
        $title = str_replace('{prefix}', '', $title); // 默认为空
        $title = str_replace('{suffix}', '', $title); // 默认为空
        
        // 清理多余的空格
        $title = preg_replace('/\s+/', ' ', trim($title));
        
        return $title;
    }
} 