<?php

use App\Http\Controllers\SerieController;
use App\Http\Controllers\TemporadaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'Olá';
});

//series
Route::get('/series', [SerieController::class, 'series']);
Route::get('/serie/view/{serie}', [SerieController::class, 'serie']);
Route::get('/serie/create', [SerieController::class, 'create']);
Route::get('/serie/edit/{serie}', [SerieController::class, 'edit']);
Route::post('/serie', [SerieController::class, 'store']);
Route::post('/serie/delete/{serie}', [SerieController::class, 'delete']);

//temporadas
Route::get('/temporadas/{serie}', [TemporadaController::class, 'temporadas']);
Route::get('/temporada/create', [TemporadaController::class, 'create']);
Route::get('/temporada/edit/{temporada}', [TemporadaController::class, 'edit']);
Route::post('/temporada', [TemporadaController::class, 'store']);
Route::post('/temporada/delete/{temporada}', [TemporadaController::class, 'delete']);
