<?php

use App\Http\Controllers\EntrepriseController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/entreprises', EntrepriseController::class);
Route::post('/entreprises/{siret}', [EntrepriseController::class, 'autoCreate']);
Route::get('/insee/{siret}', [EntrepriseController::class, 'insee']);
