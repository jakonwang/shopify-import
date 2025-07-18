<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // 添加选项名称字段
            $table->string('option1_name')->nullable()->after('status');
            $table->string('option2_name')->nullable()->after('option1_name');
            $table->string('option3_name')->nullable()->after('option2_name');
            
            // 添加选项值字段
            $table->string('option1_value')->nullable()->after('option3_name');
            $table->string('option2_value')->nullable()->after('option1_value');
            $table->string('option3_value')->nullable()->after('option2_value');
            
            // 添加索引以提高性能
            $table->index('option1_name');
            $table->index('option1_value');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex(['option1_name']);
            $table->dropIndex(['option1_value']);
            
            // 删除字段
            $table->dropColumn([
                'option1_name',
                'option2_name',
                'option3_name',
                'option1_value',
                'option2_value',
                'option3_value'
            ]);
        });
    }
}; 