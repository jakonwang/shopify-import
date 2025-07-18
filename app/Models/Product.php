<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'handle',
        'title',
        'body_html',
        'vendor',
        'type',
        'tags',
        'published',
        'variant_sku',
        'variant_price',
        'variant_compare_price',
        'status',
        
        // 产品选项字段（仅选项名称）
        'option1_name',
        'option2_name',
        'option3_name',
        
        // 变体信息字段
        'variant_grams',
        'variant_inventory_tracker',
        'variant_inventory_qty',
        'variant_inventory_policy',
        'variant_fulfillment_service',
        'variant_requires_shipping',
        'variant_taxable',
        'variant_barcode',
        'variant_weight_unit',
        'variant_tax_code',
        'cost_per_item',
        
        // 礼品卡
        'gift_card',
        
        // SEO字段
        'seo_title',
        'seo_description',
        
        // Google Shopping字段
        'google_shopping_category',
        'google_shopping_gender',
        'google_shopping_age_group',
        'google_shopping_mpn',
        'google_shopping_adwords_grouping',
        'google_shopping_adwords_labels',
        'google_shopping_condition',
        'google_shopping_custom_product',
        'google_shopping_custom_label_0',
        'google_shopping_custom_label_1',
        'google_shopping_custom_label_2',
        'google_shopping_custom_label_3',
        'google_shopping_custom_label_4',
        
        // 集合
        'collection',
        
        // 图片变体字段
        'variant_image',
        
        // 新增：高级变体功能
        'product_options',
        'variants'
    ];

    protected $casts = [
        'published' => 'boolean',
        'variant_requires_shipping' => 'boolean',
        'variant_taxable' => 'boolean',
        'gift_card' => 'boolean',
        'google_shopping_custom_product' => 'boolean',
        'variant_price' => 'decimal:2',
        'variant_compare_price' => 'decimal:2',
        'cost_per_item' => 'decimal:2',
        
        // 新增：JSON字段类型转换
        'product_options' => 'array',
        'variants' => 'array'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public static function generateHandle($title)
    {
        $handle = strtolower($title);
        $handle = preg_replace('/[^a-z0-9-]/', '-', $handle);
        $handle = preg_replace('/-+/', '-', $handle);
        $handle = trim($handle, '-');
        
        // 确保handle唯一
        $originalHandle = $handle;
        $i = 1;
        while (self::where('handle', $handle)->exists()) {
            $handle = $originalHandle . '-' . $i++;
        }
        
        return $handle;
    }
} 