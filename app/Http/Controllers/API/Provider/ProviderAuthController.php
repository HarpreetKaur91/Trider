<?php

namespace App\Http\Controllers\API\Provider;

use Illuminate\Support\Facades\Validator;
use App\Models\ProviderBusinessProfile;
use App\Models\ProviderAvailability;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use App\Models\ProviderService;
use App\Models\ProviderAddress;
use App\Models\CompanyService;
use Illuminate\Http\Request;
use App\Models\BankDetail;
use App\Models\User;
use Auth;

class ProviderAuthController extends Controller
{
    // login
    public function login(Request $request){
        $rules = ['email'=>'required|email','password'=>'required','device_type' => 'required|in:android,ios,web','firebase_token' => 'required','udid'=>'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
        {
          return response()->json(['status' => false,'message' => $validator->errors()->first()]);
        }
        else
        {
            try{
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password])):
                $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find(auth()->user()->id);
                    if(!is_null($provider)):

                        $provider->status = 1;
                        $provider->save();

                        FirebaseNotification::updateOrCreate(['user_id'=>$provider->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);

                        $generate_token = $provider->createToken('OneTap_'.$provider->id)->plainTextToken;

                        return response()->json([
                            'success' => true,
                            'message' => "You're successfully login.",
                            'token' => $generate_token,
                            'response' => [
                                'id' => $provider->id,
                                'name' => $provider->name,
                                'email' => $provider->email
                            ]
                        ]);
                    else:
                        $array = ['request'=>$request->all(),'message'=>'Provider not found'];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>'User not found']);
                    endif;
                else:
                    $array = ['request'=>$request->all(),'message'=>'These credentials do not match our records.'];
                    \Log::info($array);
                    return response()->json(['sucsess'=>false,'message'=>'These credentials do not match our records.']);
                endif;

            }
            catch(\Exception $e){
                $array = ['request'=>'provider login api','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // Change Password
    public function change_password(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), ['old_password'=>'required','password'=>'required|string|min:6|confirmed']);
            if ($validator->fails()):
                return response()->json(['success' => false,'message' => $validator->errors()->first()]);
            endif;
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)):
                $password = \Hash::make($request->password);
                if(\Hash::check($request->old_password,$provider->password)):
                    $provider->password = $password;
                    if($provider->save()):
                      return response()->json(['success' => true,'message' => 'Your password has been changed successfully.']);
                    else:
                      return response()->json(['success' => false,'message' => 'Your password has not been changed']);
                    endif;
                else:
                    return response()->json(['success' => false,'message' => "Password does not matches with current password"]);
                endif;
            else:
                return response()->json(['success'=>false,'message'=>'User not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'change password api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)):

                $provider->status = 0;
                $provider->save();

                $request->user()->tokens()->delete();

                return response()->json(['sucsess'=>true,'message'=>"You have been logged out!."]);
            else:
                return response()->json(['sucsess'=>false,'message'=>'User not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'provider logout','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }


    // Provider Profile
    public function provider_profile(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })
            ->with(['provider_business_profile','provider_availability','provider_services','provider_address'])
            ->select('id','email','name','phone_number','image')->find($request->user()->id);
            if(!is_null($provider)){
                if(!is_null($provider['image'])){
                    $url = \Storage::url($provider['image']);
                    $provider['image'] =  asset($url);
                }
                else{
                    $provider['image'] = asset('empty.jpg');
                }
                $provider->total_rating = number_format($provider->provider_reviews->avg('rating'),2);
                $provider->total_review = $provider->provider_reviews->count();
                $provider->total_services = $provider->provider_services->count();
                $provider->makeHidden('provider_reviews');
                return response()->json(['sucsess'=>true,'message'=>'Your Profile','response'=>$provider]);
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'provider profile api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Update Profile
    public function edit_profile(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)){
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'phone_number' => 'required|unique:users,phone_number,'.$provider->id,
                ]);

                if ($validator->fails()) {
                    return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                }

                else{
                    try{
                        if($request->hasFile('image')){
                            if((!is_null($provider->image)) && \Storage::exists($provider->image))
                            {
                                \Storage::delete($provider->image);
                            }
                            $provider->image = $request->file('image')->store('public/provider');
                        }

                        $provider->name = $request->name ?? $provider->name;
                        $provider->phone_number  = '+91'.$request->phone_number ?? $provider->phone_number;
                        if($provider->save())
                        {
                            return response()->json([
                                'success' => true,
                                'message' => 'Your profile has been updated.',
                        ]);
                        }
                        else
                        {
                            return response()->json(['sucsess'=>false,'message'=>'Something problem, while update your profile']);
                        }
                    }
                    catch(\Exception $e){
                        $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'provider edit profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }


    // Add/Update Provider Business Profile
    public function providerBusinessProfile(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)){
                $validator = Validator::make($request->all(), [
                    'business_name' => 'required',
                    'business_phone_no' => 'required|unique:provider_business_profiles,business_phone_no',
                    'year_of_exp' => 'required',
                    'days_of_availability' => 'required|array',
                    'user_type' => 'required|in:employee,freelancer',
                    'company_id' => 'required_if:user_type,==,employee',
                ]);

                if ($validator->fails()) {
                    return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                }

                else{
                    try{
                        if(($request->user_type == "employee") && ($request->has('company_id'))){
                            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
                            if(is_null($company)){
                                return response()->json(['sucsess'=>false,'message'=>'Company not found.'],400);
                            }
                        }

                        $businessProfile = ProviderBusinessProfile::where('user_id',$provider->id)->first();
                        if(is_null($businessProfile)):
                            $businessProfile = new ProviderBusinessProfile;
                        endif;
                        $businessProfile->user_id = $provider->id;
                        $businessProfile->business_name = $request->business_name;
                        $businessProfile->business_phone_no = '+91'.$request->business_phone_no;
                        $businessProfile->year_of_exp = $request->year_of_exp;
                        if(($request->user_type == "employee") && ($request->has('company_id'))){
                            $businessProfile->company_id = $request->company_id;
                        }
                        if($request->hasFile('front_aadhaar_card') &&  $request->hasFile('back_aadhaar_card')){
                            if((!is_null($businessProfile->front_aadhaar_card)) && \Storage::exists($businessProfile->front_aadhaar_card) && (!is_null($businessProfile->back_aadhaar_card)) && \Storage::exists($businessProfile->back_aadhaar_card))
                            {
                                \Storage::delete($businessProfile->front_aadhaar_card);
                                \Storage::delete($businessProfile->back_aadhaar_card);
                            }
                            $businessProfile->front_aadhaar_card = $request->file('front_aadhaar_card')->store('public/provider/providerBusinessProfile');
                            $businessProfile->back_aadhaar_card = $request->file('back_aadhaar_card')->store('public/provider/providerBusinessProfile');
                        }

                        if($request->hasFile('business_image')){
                            if((!is_null($businessProfile->business_image)) && \Storage::exists($businessProfile->business_image))
                            {
                                \Storage::delete($businessProfile->business_image);
                            }
                            $businessProfile->business_image = $request->file('business_image')->store('public/provider/providerBusinessProfile');
                        }

                        if($businessProfile->save()){

                            if(isset($request->days_of_availability) && !empty($request->days_of_availability)){
                                foreach($request->days_of_availability as $availability){
                                    foreach($availability as $key=>$value){
                                        ProviderAvailability::updateOrCreate(['user_id'=>$provider->id],[$key=>$value]);
                                    }
                                }
                            }

                            return response()->json([
                                'success' => true,
                                'message' => 'Your business profile has been added.',
                        ]);
                        }
                        else{
                            return response()->json(['sucsess'=>false,'message'=>'Something problem, while add your business profile']);
                        }
                    }
                    catch(\Exception $e){
                        $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'add provider business profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add/Update Provider Business Service
    public function providerBusinessService(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)){
                $validator = Validator::make($request->all(), [
                    'service_id' => 'required',
                    'bio' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                }

                else{
                    try{
                        if($provider->role == 'employee'):
                            $checkCompany = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($provider->provider_business_profile->company_id);
                            if(!is_null($checkCompany)){
                                $checkServcie = CompanyService::where('service_id',$request->service_id)->first();
                                if(!is_null($checkServcie)){
                                    ProviderService::updateOrCreate(['user_id'=>$provider->id,'service_id'=>$request->service_id,'bio'=>$request->bio]);
                                    return response()->json([
                                        'success' => true,
                                        'message' => 'Your business provider service has been added.',
                                    ]);
                                }
                                else{
                                    return response()->json(['sucsess'=>false,'message'=>'Company not found.']);
                                }
                            }
                            else{
                                return response()->json(['sucsess'=>false,'message'=>'Company not found.']);
                            }
                        else:
                            ProviderService::updateOrCreate(['user_id'=>$provider->id,'service_id'=>$request->service_id,'bio'=>$request->bio]);
                            return response()->json([
                                'success' => true,
                                'message' => 'Your business service has been added.',
                            ]);
                        endif;
                    }
                    catch(\Exception $e){
                        $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'add provider business provider service','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add/Update Provider Business Address
    public function providerBusinessAddress(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)){
                $validator = Validator::make($request->all(), [
                    'complete_address' => 'required',
                    'landmark' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'pincode' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                }

                else{
                    try{
                        ProviderAddress::updateOrCreate(['user_id'=>$provider->id],['complete_address'=>$request->complete_address,'landmark'=>$request->landmark,'city'=>$request->city,'state'=>$request->state,'pincode'=>$request->pincode]);
                        return response()->json([
                            'success' => true,
                            'message' => 'Your business address has been added.',
                        ]);
                    }
                    catch(\Exception $e){
                        $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'add provider business provider address','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Provider Bank Detail
    public function bank_detail(Request $request)
    {
        try{
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->user()->id);
            if(!is_null($provider)){
                if($request->isMethod('post')){
                    $validator = Validator::make($request->all(), [
                        'account_holder_name' => 'required',
                        'bank_name' => 'required',
                        'account_name' => 'required',
                        'ifsc_code' => 'required',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                    }

                    else{
                        try{
                            $request['user_id'] = $provider->id;
                            BankDetail::updateOrCreate(['user_id'=>$provider->id],['account_holder_name'=>$request->account_holder_name,'bank_name'=>$request->bank_name,'account_name'=>$request->account_name,'ifsc_code'=>$request->ifsc_code]);
                            return response()->json([
                                'success' => true,
                                'message' => 'Your bank detail has been added.',
                            ]);
                        }
                        catch(\Exception $e){
                            $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                            \Log::info($array);
                            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                        }
                    }
                }
                else
                {
                    $bank_detail = BankDetail::where('user_id',$provider->id)->select('account_holder_name','bank_name','account_name','ifsc_code')->first();
                    if(!is_null($bank_detail)){
                        return response()->json(['sucsess'=>true,'message'=>'Your Bank Detail','response'=>$bank_detail]);
                    }
                    else{
                        return response()->json(['sucsess'=>false,'message'=>'Bank Detail not found.']);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'provider bank detail','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }
}
