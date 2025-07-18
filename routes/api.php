<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UploadController;

// 模板路由
Route::get('/templates', [TemplateController::class, 'index']);
Route::post('/templates', [TemplateController::class, 'store']);
Route::put('/templates/{template}', [TemplateController::class, 'update']);
Route::delete('/templates/{template}', [TemplateController::class, 'destroy']);

// 商品路由
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::get('/products/export', [ProductController::class, 'exportCsv']);

// 上传路由
Route::post('/upload', [UploadController::class, 'upload']);
Route::post('/upload-folder', [UploadController::class, 'uploadFolder']); 