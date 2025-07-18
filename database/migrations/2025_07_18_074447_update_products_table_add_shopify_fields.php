<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 产品选项字段
            $table->string('option1_name')->nullable()->after('status');
            $table->string('option1_value')->nullable()->after('option1_name');
            $table->string('option2_name')->nullable()->after('option1_value');
            $table->string('option2_value')->nullable()->after('option2_name');
            $table->string('option3_name')->nullable()->after('option2_value');
            $table->string('option3_value')->nullable()->after('option3_name');
            
            // 变体信息字段（扩展现有字段）
            $table->integer('variant_grams')->nullable()->after('option3_value');
            $table->string('variant_inventory_tracker')->default('shopify')->after('variant_grams');
            $table->integer('variant_inventory_qty')->default(1000)->after('variant_inventory_tracker');
            $table->string('variant_inventory_policy')->default('continue')->after('variant_inventory_qty');
            $table->string('variant_fulfillment_service')->default('manual')->after('variant_inventory_policy');
            $table->boolean('variant_requires_shipping')->default(true)->after('variant_fulfillment_service');
            $table->boolean('variant_taxable')->default(true)->after('variant_requires_shipping');
            $table->string('variant_barcode')->nullable()->after('variant_taxable');
            $table->string('variant_weight_unit')->default('kg')->after('variant_barcode');
            $table->string('variant_tax_code')->nullable()->after('variant_weight_unit');
            $table->decimal('cost_per_item', 10, 2)->nullable()->after('variant_tax_code');
            
            // 礼品卡
            $table->boolean('gift_card')->default(false)->after('cost_per_item');
            
            // SEO字段
            $table->string('seo_title')->nullable()->after('gift_card');
            $table->text('seo_description')->nullable()->after('seo_title');
            
            // Google Shopping字段
            $table->string('google_shopping_category')->nullable()->after('seo_description');
            $table->string('google_shopping_gender')->nullable()->after('google_shopping_category');
            $table->string('google_shopping_age_group')->nullable()->after('google_shopping_gender');
            $table->string('google_shopping_mpn')->nullable()->after('google_shopping_age_group');
            $table->string('google_shopping_adwords_grouping')->nullable()->after('google_shopping_mpn');
            $table->string('google_shopping_adwords_labels')->nullable()->after('google_shopping_adwords_grouping');
            $table->string('google_shopping_condition')->default('new')->after('google_shopping_adwords_labels');
            $table->boolean('google_shopping_custom_product')->default(false)->after('google_shopping_condition');
            $table->string('google_shopping_custom_label_0')->nullable()->after('google_shopping_custom_product');
            $table->string('google_shopping_custom_label_1')->nullable()->after('google_shopping_custom_label_0');
            $table->string('google_shopping_custom_label_2')->nullable()->after('google_shopping_custom_label_1');
            $table->string('google_shopping_custom_label_3')->nullable()->after('google_shopping_custom_label_2');
            $table->string('google_shopping_custom_label_4')->nullable()->after('google_shopping_custom_label_3');
            
            // 集合
            $table->string('collection')->nullable()->after('google_shopping_custom_label_4');
            
            // 图片变体字段
            $table->string('variant_image')->nullable()->after('collection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'option1_name', 'option1_value', 'option2_name', 'option2_value', 'option3_name', 'option3_value',
                'variant_grams', 'variant_inventory_tracker', 'variant_inventory_qty', 'variant_inventory_policy',
                'variant_fulfillment_service', 'variant_requires_shipping', 'variant_taxable', 'variant_barcode',
                'variant_weight_unit', 'variant_tax_code', 'cost_per_item', 'gift_card', 'seo_title', 'seo_description',
                'google_shopping_category', 'google_shopping_gender', 'google_shopping_age_group', 'google_shopping_mpn',
                'google_shopping_adwords_grouping', 'google_shopping_adwords_labels', 'google_shopping_condition',
                'google_shopping_custom_product', 'google_shopping_custom_label_0', 'google_shopping_custom_label_1',
                'google_shopping_custom_label_2', 'google_shopping_custom_label_3', 'google_shopping_custom_label_4',
                'collection', 'variant_image'
            ]);
        });
    }
};
