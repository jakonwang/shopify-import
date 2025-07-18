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
            // 添加选项值字段
            $table->string('option1_value')->nullable()->after('option3_name');
            $table->string('option2_value')->nullable()->after('option1_value');
            $table->string('option3_value')->nullable()->after('option2_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'option1_value',
                'option2_value',
                'option3_value'
            ]);
        });
    }
}; 