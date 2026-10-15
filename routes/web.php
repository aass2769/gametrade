<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/{item}', [ItemController::class, 'show'])->whereNumber('item')->name('items.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store']);
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function () {
    return response('허용되지 않는 요청입니다.', 405, ['Allow' => 'POST']);
});

Route::middleware('auth')->group(function () {
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->whereNumber('item')->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->whereNumber('item')->name('items.update');
    Route::get('/items/{item}/delete', [ItemController::class, 'confirmDelete'])->whereNumber('item')->name('items.delete');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->whereNumber('item')->name('items.destroy');
    Route::post('/items/{item}/status', [ItemController::class, 'updateStatus'])->whereNumber('item')->name('items.status');
    Route::get('/my/items', [ItemController::class, 'mine'])->name('items.mine');
});
