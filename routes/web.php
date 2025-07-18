<?php

use Illuminate\Support\Facades\Route;

// API路由前缀
Route::prefix('api')->group(base_path('routes/api.php'));

// 所有非API路由都返回Vue应用
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
