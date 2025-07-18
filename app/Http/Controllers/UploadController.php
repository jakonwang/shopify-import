<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // 检查是否有多个文件
        if ($request->hasFile('file') && is_array($request->file('file'))) {
            // 多文件上传，转到uploadFolder方法
            return $this->uploadFolder($request);
        }
        
        $request->validate([
            'file' => 'required|file|image|max:5120', // 5MB
            'template_id' => 'required|exists:templates,id'
        ]);

        try {
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
                $template = \App\Models\Template::findOrFail($request->template_id);
                
                // 生成商品标题（使用模板的title_format）
                $productTitle = $this->generateProductTitle($template->title_format, $folderName);
                
                // 创建新商品，应用模板的所有字段
                $product = Product::create([
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
                    
                    // 产品选项字段（仅选项名称，值留空待后续处理）
                    'option1_name' => $template->option1_name,
                    'option2_name' => $template->option2_name,
                    'option3_name' => $template->option3_name,
                    
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
                ]);

                // 创建商品图片
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_src' => Storage::url($path),
                    'position' => 1,
                    'alt_text' => $product->title
                ]);

                return response()->json([
                    'message' => '新商品创建成功',
                    'product' => $product->load('images')
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => '上传失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFolder(Request $request)
    {
        // 兼容两种格式：files[]数组 或 file数组
        $files = null;
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
            return response()->json([
                'message' => '没有找到要上传的文件',
                'error' => 'No files found'
            ], 400);
        }

        try {
            $folderName = $request->input('folder_name');
            if (!$folderName) {
                // 如果没有提供文件夹名称，使用第一个文件的文件名（去除扩展名）
                $firstFile = $files[0];
                $folderName = pathinfo($firstFile->getClientOriginalName(), PATHINFO_FILENAME);
            }

            // 获取模板并生成商品标题
            $template = \App\Models\Template::findOrFail($request->template_id);
            
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
                
                // 产品选项字段（兼容旧格式）
                'option1_name' => $template->option1_name,
                'option2_name' => $template->option2_name,
                'option3_name' => $template->option3_name,
                
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
                
                // 为兼容性设置第一个选项的名称和值
                if (!empty($template->product_options[0])) {
                    $productData['option1_name'] = $template->product_options[0]['name'] ?? '';
                    if (!empty($template->product_options[0]['values'])) {
                        $productData['option1_value'] = $template->product_options[0]['values'][0] ?? '';
                    }
                }
                if (!empty($template->product_options[1])) {
                    $productData['option2_name'] = $template->product_options[1]['name'] ?? '';
                    if (!empty($template->product_options[1]['values'])) {
                        $productData['option2_value'] = $template->product_options[1]['values'][0] ?? '';
                    }
                }
                if (!empty($template->product_options[2])) {
                    $productData['option3_name'] = $template->product_options[2]['name'] ?? '';
                    if (!empty($template->product_options[2]['values'])) {
                        $productData['option3_value'] = $template->product_options[2]['values'][0] ?? '';
                    }
                }
            }
            
            $product = Product::create($productData);

            // 处理所有图片
            foreach ($files as $index => $file) {
                $path = $file->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_src' => Storage::url($path),
                    'position' => $index + 1,
                    'alt_text' => $product->title
                ]);
            }

            return response()->json([
                'message' => '文件夹上传成功',
                'product' => $product->load('images')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '上传失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 根据模板格式生成商品标题
     */
    private function generateProductTitle($titleFormat, $folderName, $prefix = '', $suffix = '')
    {
        // 定义可替换的变量
        $replacements = [
            '{folder_name}' => $folderName,
            '{prefix}' => $prefix,
            '{suffix}' => $suffix,
        ];

        // 替换模板中的变量
        $title = str_replace(array_keys($replacements), array_values($replacements), $titleFormat);
        
        // 清理多余的空格和特殊字符
        $title = preg_replace('/\s+/', ' ', $title); // 多个空格替换为单个空格
        $title = trim($title, ' -'); // 移除首尾的空格和横线
        
        // 如果处理后的标题为空，则使用文件夹名称作为备用
        if (empty($title)) {
            $title = $folderName;
        }

        return $title;
    }
} 