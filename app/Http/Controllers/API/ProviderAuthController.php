<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Models\ProviderBusinessProfile;
use App\Models\ProviderAvailability;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use App\Models\ProviderService;
use App\Models\ProviderAddress;
use Illuminate\Http\Request;
use App\Models\BankDetail;
//use Twilio\Rest\Client;
use App\Mail\SendOtp;
use App\Models\Role;
use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;
use Auth;

class ProviderAuthController extends Controller
{
    // twilio function
    private function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
        // $client = new Client($account_sid, $auth_token);
        // $client->messages->create($recipients, ['from' => $twilio_number, 'body' => $message]);
    }

    // Register
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'provider_type' => 'required|in:employee,freelancer',
            //'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,phone_number',
            'device_type' => 'required|in:android,ios',
            'firebase_token' => 'required',
            'udid'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
        }
        else{
            try{
                $checkEmail = User::where('email',$request->email)->where('role','provider')->first();
                if(is_null($checkEmail)){
                    $name = explode('@',$request->email);
                    $provider = new User;
                    $provider->name = $name[0];
                    $provider->email = $request->email;
                    $provider->password = \Hash::make($request->password);
                    // $provider->phone_number  = '+91'.$request->phone_number;
                    // $provider->phone_number_expired_at = Carbon::now()->addMinutes(30);
                    // $provider->phone_number_code = mt_rand(1000,9999);
                    $provider->role = $request->provider_type;
                    $provider->status = 0;
                    if($provider->save())
                    {
                        //$message = "Please Verify Your Account. Your OTP is ".$provider->phone_number_code;

                        //$this->sendMessage($message, $provider->phone_number);
                        //$generate_token = $provider->createToken('OneTap_'.$provider->id)->plainTextToken;

                        $role = Role::where('role_name',$request->provider_type)->first();
                        $provider->roles()->attach($role);

                        FirebaseNotification::updateOrCreate(['user_id'=>$provider->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);

                        return response()->json([
                            'success' => true,
                            'provider_id' => $provider->id,
                            'name' => $provider->name,
                       ]);

                    }
                    else
                    {
                        return response()->json(['sucsess'=>false,'message'=>'Something problem, while registration']);
                    }
                }
                else{
                    return response()->json(['sucsess'=>false,'message'=>'The email has already been taken.']);
                }
            }
            catch(\Exception $e){
                $array = ['request'=>'Provider Register API','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // Verify Phone Number OTP
    public function verifyPhoneNumberOtp(Request $request)
    {
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','provider_id'=>'required|integer','provider_type' => 'required|in:employee,freelancer',]);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find($request->provider_id);
            if(!is_null($provider)):
              if($provider->phone_number_code == $request->otp)
              {
                if(Carbon::now() > $provider->phone_number_expire_at):
                  return response()->json(['sucsess' => false,'message' => 'OTP Expired.']);
                else:
                    $provider->phone_number_verified_at = now();
                    $provider->save();
                    return response()->json(['sucsess' => true,'message' => 'OTP Matched.']);
                endif;
              }
              else
              {
                return response()->json(['sucsess' => false,'message' => 'OTP not matched']);
              }
            else:
              return response()->json(['sucsess' => false,'message' => 'Provider not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'verify otp','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // login
    public function login(Request $request){
        $rules = ['email'=>'required|email','password'=>'required','device_type' => 'required|in:android,ios','firebase_token' => 'required','udid'=>'required','provider_type' => 'required|in:employee,freelancer'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
        {
          return response()->json(['status' => false,'message' => $validator->errors()->first()]);
        }
        else
        {
            try{
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password,'role'=>'provider'])):
                $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find(auth()->user()->id);
                    if(!is_null($provider)):

                        $provider->status = 1;
                        $provider->save();

                        FirebaseNotification::updateOrCreate(['user_id'=>$provider->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);

                        $generate_token = $provider->createToken('OneTap_'.$provider->id)->plainTextToken;

                        return response()->json([
                            'status'=>2,
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
                        return response()->json(['sucsess'=>false,'message'=>'Provider not found']);
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

    // Forget Password
    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), ['email'=>'required|email','provider_type' => 'required|in:employee,freelancer']);
        if ($validator->fails()):
          return response()->json(['success' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->where('email',$request->email)->first();
            if(!is_null($provider)):
                $otp = Otp::whereUserId($provider->id)->first();
                if(!is_null($otp)):
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($provider->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $provider->id,
                                'name' => $provider->name,
                                'email' => $provider->email
                            ]
                        ]);
                    else:
                      return response()->json(['success' => false,'message' => 'Something problem when generating otp']);
                    endif;
                else:
                    $otp = new Otp;
                    $otp->user_id = $provider->id;
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($provider->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $provider->id,
                                'name' => $provider->name,
                                'email' => $provider->email
                            ]
                        ]);
                    else:
                      return response()->json(['success' => false,'message' => 'Something problem when generating otp']);
                    endif;
                endif;
            else:
                return response()->json(['success' => false,'message' => 'Email address not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>$request->email,'message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }

    }

    // Verify OTP
    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','provider_id'=>'required|integer','provider_type' => 'required|in:employee,freelancer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find($request->provider_id);
            if(!is_null($provider)):
              $otp = Otp::whereCode($request->otp)->whereUserId($provider->id)->first();
              if(!is_null($otp)):
                if(Carbon::now() > $otp->expire_at):
                  return response()->json(['sucsess' => false,'message' => 'OTP Expired.']);
                else:
                  return response()->json(['sucsess' => true,'message' => 'OTP Matched.']);
                endif;
              else:
                return response()->json(['sucsess' => false,'message' => 'OTP not matched']);
              endif;
            else:
              return response()->json(['sucsess' => false,'message' => 'Provider not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'verify otp','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }

    }

    // Reset Password
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), ['provider_id'=>'required|integer','password'=>'required|string|confirmed','provider_type' => 'required|in:employee,freelancer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;
        try{
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find($request->provider_id);
            if(!is_null($provider)):
                $password = \Hash::make($request->password);
                $provider->password = $password;
                if($provider->save()):
                    //Auth::logout();
                  return response()->json(['sucsess' => true,'message' => 'Your password has been changed successfully.']);
                else:
                  return response()->json(['sucsess' => false,'message' => 'Your password has not been changed']);
                endif;
            else:
              return response()->json(['sucsess' => false,'message' => "Provider not found"]);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'reset password api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }

    }

    // Change Password
    public function change_password(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), ['old_password'=>'required','password'=>'required|string|min:6|confirmed','provider_type' => 'required|in:employee,freelancer']);
            if ($validator->fails()):
                return response()->json(['success' => false,'message' => $validator->errors()->first()]);
            endif;
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find($request->user()->id);
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
                return response()->json(['success'=>false,'message'=>'Provider not found']);
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
            $validator = Validator::make($request->all(), ['provider_type' => 'required|in:employee,freelancer']);
            if ($validator->fails()):
                return response()->json(['success' => false,'message' => $validator->errors()->first()]);
            endif;
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->find($request->user()->id);
            if(!is_null($provider)):

                $provider->status = 0;
                $provider->save();

                $request->user()->tokens()->delete();

                return response()->json(['sucsess'=>true,'message'=>"You have been logged out!."]);
            else:
                return response()->json(['sucsess'=>false,'message'=>'Provider not found']);
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
            $validator = Validator::make($request->all(), ['provider_type' => 'required|in:employee,freelancer']);
            if ($validator->fails()):
                return response()->json(['success' => false,'message' => $validator->errors()->first()]);
            endif;
            $provider = User::whereHas('roles',function($q){ $q->where('role_name',$request->provider_type); })->select('id','email','name','phone_number','image')->find($request->user()->id);
            if(!is_null($provider)){
                if(!is_null($provider['image'])){
                    $url = \Storage::url($provider['image']);
                    $provider['image'] =  asset($url);
                }
                else{
                    $provider['image'] = asset('empty.jpg');
                }
                $provider->total_rating = 0;
                $provider->total_review = 0;
                return response()->json(['sucsess'=>true,'message'=>'Your Profile','response'=>$provider]);
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'Provider not found.']);
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
            $provider = $request->user();

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
            $provider = $request->user();

            $validator = Validator::make($request->all(), [
                'business_name' => 'required',
                'business_phone_no' => 'required|unique:provider_business_profiles,business_phone_no',
                'year_of_exp' => 'required',
                'days_of_availability' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
            }

            else{
                try{
                    $businessProfile = ProviderBusinessProfile::where('user_id',$provider->id)->first();
                    if(is_null($businessProfile)):
                        $businessProfile = new ProviderBusinessProfile;
                    endif;
                    $businessProfile->user_id = $provider->id;
                    $businessProfile->business_name = $request->business_name;
                    $businessProfile->business_phone_no = '+91'.$request->business_phone_no;
                    $businessProfile->year_of_exp = $request->year_of_exp;

                    if($request->hasFile('front_aadhaar_card') &&  $request->hasFile('back_aadhaar_card')){
                        if((!is_null($provider->front_aadhaar_card)) && \Storage::exists($provider->front_aadhaar_card) && (!is_null($provider->back_aadhaar_card)) && \Storage::exists($provider->back_aadhaar_card))
                        {
                            \Storage::delete($provider->front_aadhaar_card);
                            \Storage::delete($provider->back_aadhaar_card);
                        }
                        $provider->front_aadhaar_card = $request->file('front_aadhaar_card')->store('public/provider/providerBusinessProfile');
                        $provider->back_aadhaar_card = $request->file('back_aadhaar_card')->store('public/provider/providerBusinessProfile');
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
            $provider = $request->user();

            $validator = Validator::make($request->all(), [
                'service_id' => 'required',
                'bio' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
            }

            else{
                try{
                    ProviderService::updateOrCreate(['user_id'=>$provider->id,'service_id'=>$request->service_id,'bio'=>$request->bio]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Your business provider service has been added.',
                    ]);
                }
                catch(\Exception $e){
                    $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                    \Log::info($array);
                    return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                }
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
            $provider = $request->user();

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
                        'message' => 'Your business provider address has been added.',
                    ]);
                }
                catch(\Exception $e){
                    $array = ['request'=>$request->all(),'message'=>$e->getMessage()];
                    \Log::info($array);
                    return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
                }
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
            $provider = $request->user();

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
        catch(\Exception $e){
            $array = ['request'=>'provider bank detail','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }
}
