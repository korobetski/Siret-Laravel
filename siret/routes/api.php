<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::apiResource('/users', UserController::class);
//Route::apiResource('/entreprises', EntrepriseController::class);

// Routes accessibles sans login ni token
Route::get('/entreprises', [EntrepriseController::class, 'index']);
Route::get('/entreprises/{id}', [EntrepriseController::class, 'show']);
Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [LoginController::class, 'authenticate'])->name('login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
// Routes accessibles avec login (session)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tokens/create', [UserController::class, 'createToken']);
    Route::get('/user', [UserController::class, 'user'])->name('user');
});
// Routes accessibles par token
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::post('/entreprises', [EntrepriseController::class, 'store']);
    Route::put('/entreprises/{id}', [EntrepriseController::class, 'update']);
    Route::delete('/entreprises/{id}', [EntrepriseController::class, 'destroy']);
    Route::post('/entreprises/{siret}', [EntrepriseController::class, 'autoCreate']);
    Route::get('/insee/{siret}', [EntrepriseController::class, 'insee']);
});