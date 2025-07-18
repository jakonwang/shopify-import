<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_src');
            $table->integer('position')->default(1);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            // 添加索引以提高性能
            $table->index('position');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_images');
    }
}; 