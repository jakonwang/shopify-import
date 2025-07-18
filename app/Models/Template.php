<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'vendor',
        'type',
        'title_format',
        'body_html',
        'price',
        'compare_at_price',
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
        
        // 新增：高级变体功能
        'product_options',
        'variants'
    ];

    protected $casts = [
        'variant_requires_shipping' => 'boolean',
        'variant_taxable' => 'boolean',
        'gift_card' => 'boolean',
        'google_shopping_custom_product' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_per_item' => 'decimal:2',
        
        // 新增：JSON字段类型转换
        'product_options' => 'array',
        'variants' => 'array'
    ];
} 