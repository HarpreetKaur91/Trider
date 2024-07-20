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

    Route::get('/freelancer',[AdminController::class,'freelancer'])->name('freelancer');
    Route::get('/freelancer/{id}/profile',[AdminController::class,'freelancer_profile'])->name('freelancer.view');
    Route::delete('/freelancer/{id}/destroy',[AdminController::class,'freelancer_destroy'])->name('freelancer.destroy');

    Route::get('/employee',[AdminController::class,'employee'])->name('employee');
    Route::get('/employee/{id}/profile',[AdminController::class,'employee_profile'])->name('employee.view');
    Route::delete('/employee/{id}/destroy',[AdminController::class,'employee_destroy'])->name('employee.destroy');

    Route::get('/companies',[AdminController::class,'company'])->name('company');
    Route::get('/company/{id}/profile',[AdminController::class,'company_profile'])->name('company.view');
    Route::delete('/company/{id}/destroy',[AdminController::class,'company_destroy'])->name('company.destroy');

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
