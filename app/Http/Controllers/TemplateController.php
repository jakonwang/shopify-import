<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;

class TemplateController extends Controller
{
    public function index()
    {
        try {
            Log::info('开始获取模板列表');
            
            // 检查数据库连接
            try {
                DB::connection()->getPdo();
                Log::info('数据库连接正常');
            } catch (\Exception $e) {
                Log::error('数据库连接失败', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'message' => '数据库连接失败',
                    'data' => []
                ], 500);
            }
            
            // 检查表是否存在
            if (!Schema::hasTable('templates')) {
                Log::error('templates表不存在');
                return response()->json([
                    'status' => 'error',
                    'message' => 'templates表不存在',
                    'data' => []
                ], 500);
            }
            
            $templates = Template::all();
            $templatesArray = $templates->toArray();
            
            Log::info('获取模板列表成功', [
                'count' => count($templatesArray),
                'data' => $templatesArray
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '获取模板列表成功',
                'data' => $templatesArray
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取模板列表失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '获取模板列表失败: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('接收到创建模板请求', [
                'request_data' => $request->except(['body_html']), // 排除大文本字段
                'has_product_options' => $request->has('product_options'),
                'variants_count' => $request->has('variants') ? count($request->variants) : 0
            ]);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'vendor' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255',
                'title_format' => 'required|string|max:255',
                'body_html' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'compare_at_price' => 'nullable|numeric|min:0',
                'status' => 'required|string|in:active,draft',
                'collection' => 'nullable|string|max:255',
                
                // 产品选项和变体
                'product_options' => 'nullable|array',
                'product_options.*.name' => 'required_with:product_options|string|max:255',
                'product_options.*.values' => 'required_with:product_options|array',
                'product_options.*.values.*' => 'required|string|max:255',
                
                'variants' => 'nullable|array',
                'variants.*.option1' => 'nullable|string|max:255',
                'variants.*.option2' => 'nullable|string|max:255',
                'variants.*.option3' => 'nullable|string|max:255',
                'variants.*.sku' => 'nullable|string|max:255',
                'variants.*.price' => 'required_with:variants|numeric|min:0',
                'variants.*.compare_at_price' => 'nullable|numeric|min:0',
                'variants.*.grams' => 'nullable|integer|min:0',
                'variants.*.inventory_quantity' => 'nullable|integer|min:0',
                'variants.*.inventory_policy' => 'nullable|string|in:continue,deny',
                'variants.*.fulfillment_service' => 'nullable|string|in:manual,automatic',
                'variants.*.requires_shipping' => 'nullable|boolean',
                'variants.*.taxable' => 'nullable|boolean',
                'variants.*.barcode' => 'nullable|string|max:255',
                
                // 变体默认值设置
                'variant_grams' => 'nullable|integer|min:0',
                'variant_inventory_tracker' => 'nullable|string|in:shopify,none',
                'variant_inventory_qty' => 'nullable|integer|min:0',
                'variant_inventory_policy' => 'nullable|string|in:continue,deny',
                'variant_fulfillment_service' => 'nullable|string|in:manual,automatic',
                'variant_requires_shipping' => 'nullable|boolean',
                'variant_taxable' => 'nullable|boolean',
                'variant_barcode' => 'nullable|string|max:255',
                'variant_weight_unit' => 'nullable|string|in:g,kg,oz,lb',
                'variant_tax_code' => 'nullable|string|max:255',
                'cost_per_item' => 'nullable|numeric|min:0',
                
                // 礼品卡
                'gift_card' => 'nullable|boolean',
                
                // SEO字段
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string',
                
                // Google Shopping字段
                'google_shopping_category' => 'nullable|string|max:255',
                'google_shopping_gender' => 'nullable|string|max:255',
                'google_shopping_age_group' => 'nullable|string|max:255',
                'google_shopping_mpn' => 'nullable|string|max:255',
                'google_shopping_adwords_grouping' => 'nullable|string|max:255',
                'google_shopping_adwords_labels' => 'nullable|string|max:255',
                'google_shopping_condition' => 'nullable|string|in:new,used,refurbished',
                'google_shopping_custom_product' => 'nullable|boolean',
                'google_shopping_custom_label_0' => 'nullable|string|max:255',
                'google_shopping_custom_label_1' => 'nullable|string|max:255',
                'google_shopping_custom_label_2' => 'nullable|string|max:255',
                'google_shopping_custom_label_3' => 'nullable|string|max:255',
                'google_shopping_custom_label_4' => 'nullable|string|max:255'
            ]);

            // 处理产品选项和变体
            if (!empty($validated['product_options'])) {
                Log::info('处理产品选项', [
                    'options_count' => count($validated['product_options']),
                    'options' => array_map(function($option) {
                        return [
                            'name' => $option['name'],
                            'values_count' => count($option['values'])
                        ];
                    }, $validated['product_options'])
                ]);
            }

            if (!empty($validated['variants'])) {
                Log::info('处理变体数据', [
                    'variants_count' => count($validated['variants']),
                    'first_variant' => array_map(function($variant) {
                        return Arr::except($variant, ['barcode']); // 使用Arr::except替代array_except
                    }, [$validated['variants'][0]])
                ]);
            }

            $template = Template::create($validated);
            
            Log::info('模板创建成功', [
                'template_id' => $template->id,
                'name' => $template->name,
                'options_count' => !empty($template->product_options) ? count($template->product_options) : 0,
                'variants_count' => !empty($template->variants) ? count($template->variants) : 0
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '模板创建成功',
                'data' => $template
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('模板创建验证失败', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['body_html', 'variants', 'product_options'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('模板创建失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['body_html', 'variants', 'product_options'])
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '模板创建失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(Request $request, Template $template)
    {
        try {
            Log::info('接收到更新模板请求', [
                'template_id' => $template->id,
                'request_data' => $request->except(['body_html']),
                'has_product_options' => $request->has('product_options'),
                'variants_count' => $request->has('variants') ? count($request->variants) : 0
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'vendor' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255',
                'title_format' => 'required|string|max:255',
                'body_html' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'compare_at_price' => 'nullable|numeric|min:0',
                'status' => 'required|string|in:active,draft',
                'collection' => 'nullable|string|max:255',
                
                // 产品选项和变体
                'product_options' => 'nullable|array',
                'product_options.*.name' => 'required_with:product_options|string|max:255',
                'product_options.*.values' => 'required_with:product_options|array',
                'product_options.*.values.*' => 'required|string|max:255',
                
                'variants' => 'nullable|array',
                'variants.*.option1' => 'nullable|string|max:255',
                'variants.*.option2' => 'nullable|string|max:255',
                'variants.*.option3' => 'nullable|string|max:255',
                'variants.*.sku' => 'nullable|string|max:255',
                'variants.*.price' => 'required_with:variants|numeric|min:0',
                'variants.*.compare_at_price' => 'nullable|numeric|min:0',
                'variants.*.grams' => 'nullable|integer|min:0',
                'variants.*.inventory_quantity' => 'nullable|integer|min:0',
                'variants.*.inventory_policy' => 'nullable|string|in:continue,deny',
                'variants.*.fulfillment_service' => 'nullable|string|in:manual,automatic',
                'variants.*.requires_shipping' => 'nullable|boolean',
                'variants.*.taxable' => 'nullable|boolean',
                'variants.*.barcode' => 'nullable|string|max:255',
                
                // 变体默认值设置
                'variant_grams' => 'nullable|integer|min:0',
                'variant_inventory_tracker' => 'nullable|string|in:shopify,none',
                'variant_inventory_qty' => 'nullable|integer|min:0',
                'variant_inventory_policy' => 'nullable|string|in:continue,deny',
                'variant_fulfillment_service' => 'nullable|string|in:manual,automatic',
                'variant_requires_shipping' => 'nullable|boolean',
                'variant_taxable' => 'nullable|boolean',
                'variant_barcode' => 'nullable|string|max:255',
                'variant_weight_unit' => 'nullable|string|in:g,kg,oz,lb',
                'variant_tax_code' => 'nullable|string|max:255',
                'cost_per_item' => 'nullable|numeric|min:0',
                
                // 礼品卡
                'gift_card' => 'nullable|boolean',
                
                // SEO字段
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string',
                
                // Google Shopping字段
                'google_shopping_category' => 'nullable|string|max:255',
                'google_shopping_gender' => 'nullable|string|max:255',
                'google_shopping_age_group' => 'nullable|string|max:255',
                'google_shopping_mpn' => 'nullable|string|max:255',
                'google_shopping_adwords_grouping' => 'nullable|string|max:255',
                'google_shopping_adwords_labels' => 'nullable|string|max:255',
                'google_shopping_condition' => 'nullable|string|in:new,used,refurbished',
                'google_shopping_custom_product' => 'nullable|boolean',
                'google_shopping_custom_label_0' => 'nullable|string|max:255',
                'google_shopping_custom_label_1' => 'nullable|string|max:255',
                'google_shopping_custom_label_2' => 'nullable|string|max:255',
                'google_shopping_custom_label_3' => 'nullable|string|max:255',
                'google_shopping_custom_label_4' => 'nullable|string|max:255'
            ]);

            // 处理产品选项和变体
            if (!empty($validated['product_options'])) {
                Log::info('处理产品选项', [
                    'options_count' => count($validated['product_options']),
                    'options' => array_map(function($option) {
                        return [
                            'name' => $option['name'],
                            'values_count' => count($option['values'])
                        ];
                    }, $validated['product_options'])
                ]);
            }

            if (!empty($validated['variants'])) {
                Log::info('处理变体数据', [
                    'variants_count' => count($validated['variants']),
                    'first_variant' => array_map(function($variant) {
                        return Arr::except($variant, ['barcode']); // 使用Arr::except替代array_except
                    }, [$validated['variants'][0]])
                ]);
            }

            $template->update($validated);
            
            Log::info('模板更新成功', [
                'template_id' => $template->id,
                'name' => $template->name,
                'options_count' => !empty($template->product_options) ? count($template->product_options) : 0,
                'variants_count' => !empty($template->variants) ? count($template->variants) : 0
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '模板更新成功',
                'data' => $template
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('模板更新验证失败', [
                'template_id' => $template->id,
                'errors' => $e->errors(),
                'request_data' => $request->except(['body_html', 'variants', 'product_options'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('模板更新失败', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['body_html', 'variants', 'product_options'])
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '模板更新失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy(Template $template)
    {
        try {
            Log::info('接收到删除模板请求', [
                'template_id' => $template->id,
                'name' => $template->name
            ]);
            
            $template->delete();
            
            Log::info('模板删除成功', [
                'template_id' => $template->id,
                'name' => $template->name
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '模板删除成功'
            ]);
            
        } catch (\Exception $e) {
            Log::error('模板删除失败', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '模板删除失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 