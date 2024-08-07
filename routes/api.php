<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Provider\ProviderAuthController;
use App\Http\Controllers\API\Customer\CustomerAuthController;
use App\Http\Controllers\API\Customer\CustomerApiController;
use App\Http\Controllers\API\Company\CompanyAuthController;
use App\Http\Controllers\API\Booking\BookingController;
use App\Http\Controllers\API\CommonAPIController;
use App\Http\Controllers\API\AuthController;

Route::controller(BookingController::class)->group(function(){
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('booking','booking');
    });
});

Route::controller(CommonAPIController::class)->group(function(){
    Route::get('services','services');
    Route::get('getCompanyList','getCompanyList');

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('contact-us','contact_us');
        Route::get('faq-content','faq_content');
        Route::get('about-us-content','about_us_content');
        Route::get('privacy-policy-content','privacy_policy_content');
        Route::get('term-of-condition-content','term_of_condition_content');
    });
});

Route::group(['prefix'=>'provider'],function()
{
    Route::controller(AuthController::class)->group(function(){
        Route::post('register','register');
        Route::post('verifyOtp','verify_otp');
        Route::post('resetPassword','reset_password');
        Route::post('forgotPassword','forgot_password');
        Route::post('verifyPhoneNumberOtp','verifyPhoneNumberOtp');
        Route::post('addBusinessProfile','addBusinessProfile');
    });

    Route::controller(ProviderAuthController::class)->group(function(){
        Route::post('login','login');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::controller(ProviderAuthController::class)->group(function(){
            Route::post('logout','logout');
            Route::post('edit-profile','edit_profile');
            Route::post('changePassword','change_password');
            Route::get('provider-profile','provider_profile');

            Route::match(['get','post'],'bank-detail','bank_detail');
            Route::get('getBookingLists','getBookingLists');
        });
    });
});

Route::group(['prefix'=>'company'],function()
{
    Route::controller(AuthController::class)->group(function(){
        Route::post('register','register');
        Route::post('verifyOtp','verify_otp');
        Route::post('resetPassword','reset_password');
        Route::post('forgotPassword','forgot_password');
        Route::post('verifyPhoneNumberOtp','verifyPhoneNumberOtp');
        Route::post('addBusinessProfile','addBusinessProfile');
    });

    Route::controller(CompanyAuthController::class)->group(function(){
        Route::post('login','login');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::controller(CompanyAuthController::class)->group(function(){
            Route::post('logout','logout');
            Route::post('edit-profile','edit_profile');
            Route::post('changePassword','change_password');
            Route::get('company-profile','company_profile');
            Route::post('acceptOrRejectEmployeeAccount','acceptOrRejectEmployeeAccount');
            Route::get('getEmployeeList','getEmployeeList');
            Route::get('getBookingLists','getBookingLists');
        });
    });
});

Route::group(['prefix'=>'customer'],function()
{
    Route::controller(AuthController::class)->group(function(){
        Route::post('register','register');
        Route::post('verifyOtp','verify_otp');
        Route::post('resetPassword','reset_password');
        Route::post('forgotPassword','forgot_password');
        Route::post('verifyPhoneNumberOtp','verifyPhoneNumberOtp');
    });

    Route::controller(CustomerAuthController::class)->group(function(){
        Route::post('login','login');
    });

    Route::middleware('auth:sanctum')->group(function(){
        Route::controller(CustomerAuthController::class)->group(function(){
            Route::post('logout','logout');
            Route::post('edit-profile','edit_profile');
            Route::post('changePassword','change_password');
            Route::get('user-profile','user_profile');
        });

        Route::controller(CustomerApiController::class)->group(function(){
            Route::get('businesses','businesses');
            Route::get('getBookingLists','getBookingLists');
            Route::post('businessReviews','businessReviews');
            Route::get('businesses/{businessId}','getBusinessProfile');
            Route::get('business-review/{businessId}','getBusinessReview');
            Route::match(['get','post'],'favourite_business','favourite_business');
        });
    });
});
