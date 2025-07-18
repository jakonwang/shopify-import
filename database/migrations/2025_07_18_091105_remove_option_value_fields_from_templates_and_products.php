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
        // 从templates表删除option值字段
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'option1_value')) {
                $table->dropColumn('option1_value');
            }
            if (Schema::hasColumn('templates', 'option2_value')) {
                $table->dropColumn('option2_value');
            }
            if (Schema::hasColumn('templates', 'option3_value')) {
                $table->dropColumn('option3_value');
            }
        });
        
        // 从products表删除option值字段
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'option1_value')) {
                $table->dropColumn('option1_value');
            }
            if (Schema::hasColumn('products', 'option2_value')) {
                $table->dropColumn('option2_value');
            }
            if (Schema::hasColumn('products', 'option3_value')) {
                $table->dropColumn('option3_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 重新添加option值字段到templates表
        Schema::table('templates', function (Blueprint $table) {
            $table->string('option1_value')->nullable()->after('option1_name');
            $table->string('option2_value')->nullable()->after('option2_name');
            $table->string('option3_value')->nullable()->after('option3_name');
        });
        
        // 重新添加option值字段到products表
        Schema::table('products', function (Blueprint $table) {
            $table->string('option1_value')->nullable()->after('option1_name');
            $table->string('option2_value')->nullable()->after('option2_name');
            $table->string('option3_value')->nullable()->after('option3_name');
        });
    }
};
