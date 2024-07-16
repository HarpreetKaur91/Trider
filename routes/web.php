<?php

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
       return redirect('login');
    });
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth','admin'])->group(function () {

    Route::resource('/service',ServiceController::class);
    Route::resource('/faq',FaqController::class);

    Route::get('/setting', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/setting', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/setting', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/provider',[AdminController::class,'provider'])->name('provider');
    Route::get('/provider/{id}/profile',[AdminController::class,'provider_profile'])->name('provider.view');
    Route::delete('/provider/{id}/destroy',[AdminController::class,'provider_destroy'])->name('provider.destroy');
    Route::get('/provider/{id}/verify/{status}',[AdminController::class,'verify_provider_status'])->name('verifyProviderStatus');

    Route::get('/customer',[AdminController::class,'customer'])->name('customer');
    Route::get('/customer/{id}/profile',[AdminController::class,'customer_profile'])->name('customer.view');
    Route::delete('/customer/{id}/destroy',[AdminController::class,'customer_destroy'])->name('customer.destroy');
    Route::get('/customer/{id}/verify/{status}',[AdminController::class,'verify_report_status'])->name('verifyReportStatus');

    Route::match(['get','post'],'/page/{page_name}',[AdminController::class,'page_content'])->name('page.create');
    
    Route::get('message-report',[AdminController::class,'message'])->name('message');
    Route::get('message-report/{id}',[AdminController::class,'message'])->name('message.view');
    Route::delete('message-report/{id}',[AdminController::class,'message'])->name('message.delete');
});

require __DIR__.'/auth.php';
