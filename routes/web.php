<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;
// Route::get('/', function () {
//     return view('welcome');
// });




Route::get('/', [SpeechController::class, 'index']);
Route::post('/save-text', [SpeechController::class, 'saveText']);
