<?php

use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Api\User\SendMoneyController;

Route::controller(GlobalController::class)->prefix('global')->name('global.')->group(function(){
    Route::post('get-states','getStates')->name('country.states');
    Route::post('get-cities','getCities')->name('country.cities');
    Route::post('get-countries','getCountries')->name('countries');
    Route::post('get-timezones','getTimezones')->name('timezones');
});

// FileHolder Routes
Route::post('/fileholder-upload',[FileController::class,'storeFile'])->name('fileholder.upload');
Route::post('/fileholder-remove',[FileController::class,'removeFile'])->name('fileholder.remove');

Route::get("file/download/{path_source}/{name}",function($path_source,$file_name) {
    $file_link = get_files_path($path_source) . "/" . $file_name;
    if(File::exists($file_link)) return response()->download($file_link);
    return back()->with(['error' => ['File doesn\'t exists']]);
})->name('file.download');

//Flutterwave withdraw callback url
Route::controller(GlobalController::class)->group(function(){
    Route::post('flutterwave/withdraw_webhooks','webHookResponse')->name('webhook.response')->withoutMiddleware(['web']);
});

Route::get('api/send-money',[GlobalController::class,'sendMoney']);
Route::post('api/send-money/confirmed',[GlobalController::class,'confirmed']);
Route::get('api/send-money/redirect-url/{identifier}',[GlobalController::class,'redirectUrl'])->name('api.send.money.redirect.url'); 
Route::post('api/send-money/stripe-payment-gateway',[GlobalController::class,'stripePaymentGateway'])->name('api.send.money.stripe.payment.gateway');         
Route::get('api/user/send-money/redirect-url/{identifier}',[SendMoneyController::class,'redirectUrl'])->name('api.user.send.money.redirect.url');          
Route::post('api/stripe-payment-gateway',[SendMoneyController::class,'stripePaymentGateway'])->name('api.user.send.money.stripe.payment.gateway');
