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
            // 添加产品选项JSON字段
            $table->json('product_options')->nullable()->after('variant_image')->comment('产品选项配置JSON格式');
            
            // 添加变体JSON字段
            $table->json('variants')->nullable()->after('product_options')->comment('产品变体配置JSON格式');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_options', 'variants']);
        });
    }
}; 