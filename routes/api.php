<?php

use App\Http\Controllers\cardManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['token', 'permissions'])->prefix('cardManagement')->group(function () {
    Route::put('/addCard', [cardManagement::class, 'addCard']);
    Route::put('/addCollection', [cardManagement::class, 'addCollection']);

});

Route::middleware(['token'])->prefix('users')->group(function () {
    Route::post('/passwordRecovery', [UserController::class, 'passwordRecovery']);
    Route::get('/searchCard', [cardManagement::class, 'searchCard']);


});

//Sin paso por middlewares
Route::prefix('users')->group(function () {
    Route::put('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});
