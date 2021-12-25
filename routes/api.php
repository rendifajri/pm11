<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

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
Route::get   ('/employee', [EmployeeController::class, 'index']);
Route::get   ('/employee/{id}', [EmployeeController::class, 'detail']);
Route::post  ('/employee', [EmployeeController::class, 'create']);
Route::put   ('/employee/{id}', [EmployeeController::class, 'update']);
Route::delete('/employee/{id}', [EmployeeController::class, 'delete']);
