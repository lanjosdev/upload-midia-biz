<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\LogoutController;
use App\Http\Controllers\Authentication\ProfileController;
use App\Http\Controllers\MediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//login
Route::post('/login', [LoginController::class, 'login']);

//grupo de rotas com a proteção do sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    //logout
    Route::post('/logout', [LogoutController::class, 'logout']);
    //profile
    Route::get('/profile', [ProfileController::class, 'profile']);

    
    //upload-chunks 
    Route::post('/upload-chunks', [MediaController::class, 'chunks']);
    //union-chunks
    Route::post('/union-chunks', [MediaController::class, 'gatheringPiecesAndAddingToTheQueue']);
    
    
    //teste
    Route::post('/upload-teste', [MediaController::class, 'uploadPrimary']);
});

Route::middleware(['token'])->group(function () {
    //get-media
    Route::get('/get-media', [MediaController::class, 'getMedia']);
});