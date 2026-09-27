<?php

use App\Http\Controllers\HomeController;
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

// /user أُزيل: لم يستعمله الموقع العام، وكان يعتمد حارس sanctum "web"
// غير المعرَّف في config/auth.php

// Contact form endpoint used by the Next.js public frontend (frontend/)
Route::post('/send', [HomeController::class, 'sendApi'])->middleware('throttle:5,1');
