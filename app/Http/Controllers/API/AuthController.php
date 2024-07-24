<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use App\Models\BusinessProfile;
use App\Models\BusinessAddress;
use App\Models\BusinessService;
use App\Models\BusinessImage;
use Illuminate\Http\Request;
//use Twilio\Rest\Client;
use App\Mail\SendOtp;
use App\Models\Role;
use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;

class AuthController extends Controller
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
            'user_type' => 'required|in:employee,freelancer,company',
            'phone_number' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,phone_number',
            'device_type' => 'required|in:android,ios,web',
            'firebase_token' => 'required',
            'udid'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
        }
        else{
            try{
                $checkEmail = User::where('email',$request->email)->where('role',$request->user_type)->first();
                if(is_null($checkEmail)){
                    if($request->has('name')):
                        $name = $request->name;
                    else:
                        $explode = explode('@',$request->email);
                        $name = $explode[0];
                    endif;
                    $user = new User;
                    $user->name = $name;
                    $user->email = $request->email;
                    $user->password = \Hash::make($request->password);
                    if($request->has('phone_number')):
                        $user->phone_number  = '+91'.$request->phone_number;
                        // $user->phone_number_expired_at = Carbon::now()->addMinutes(30);
                        // $user->phone_number_code = mt_rand(1000,9999);
                    endif;
                    $user->role = $request->user_type;
                    $user->status = 0;
                    if($user->save())
                    {
                        //$message = "Please Verify Your Account. Your OTP is ".$user->phone_number_code;

                        //$this->sendMessage($message, $user->phone_number);
                        //$generate_token = $user->createToken('OneTap_'.$user->id)->plainTextToken;

                        $role = Role::where('role_name',$request->user_type)->first();
                        $user->roles()->attach($role);

                        FirebaseNotification::updateOrCreate(['user_id'=>$user->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);
                        return response()->json([
                            'success' => true,
                            'user_id' => $user->id,
                            'name' => $user->name,
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
                $array = ['request'=>'Register API','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // Forget Password
    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), ['email'=>'required|email']);
        if ($validator->fails()):
          return response()->json(['success' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $user = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->where('email',$request->email)->first();
            if(!is_null($user)):
                $otp = Otp::whereUserId($user->id)->first();
                if(!is_null($otp)):
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($user->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email
                            ]
                        ]);
                    else:
                      return response()->json(['success' => false,'message' => 'Something problem when generating otp']);
                    endif;
                else:
                    $otp = new Otp;
                    $otp->user_id = $user->id;
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($user->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email
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
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','user_id'=>'required|integer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $user = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->user_id);
            if(!is_null($user)):
              $otp = Otp::whereCode($request->otp)->whereUserId($user->id)->first();
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
              return response()->json(['sucsess' => false,'message' => 'User not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'verify otp','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Verify Phone Number OTP
    public function verifyPhoneNumberOtp(Request $request)
    {
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','user_id'=>'required|integer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $user = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->user_id);
            if(!is_null($user)):
              if($user->phone_number_code == $request->otp)
              {
                if(Carbon::now() > $user->phone_number_expire_at):
                  return response()->json(['sucsess' => false,'message' => 'OTP Expired.']);
                else:
                    $user->phone_number_verified_at = now();
                    $user->save();
                    return response()->json(['sucsess' => true,'message' => 'OTP Matched.']);
                endif;
              }
              else
              {
                return response()->json(['sucsess' => false,'message' => 'OTP not matched']);
              }
            else:
              return response()->json(['sucsess' => false,'message' => 'User not found']);
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
        $validator = Validator::make($request->all(), ['user_id'=>'required|integer','password'=>'required|string|confirmed']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;
        try{
            $user = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->user_id);
            if(!is_null($user)):
                $password = \Hash::make($request->password);
                $user->password = $password;
                if($user->save()):
                    //Auth::logout();
                  return response()->json(['sucsess' => true,'message' => 'Your password has been changed successfully.']);
                else:
                  return response()->json(['sucsess' => false,'message' => 'Your password has not been changed']);
                endif;
            else:
              return response()->json(['sucsess' => false,'message' => "User not found"]);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'reset password api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add/Update Business Profile
    public function addBusinessProfile(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'business_name' => 'required',
                'business_phone_no' => 'required|unique:business_profiles,business_phone_no',
                'year_of_exp' => 'required',
                'user_type' => 'required|in:employee,freelancer,company',
                'company_id' => 'required_if:user_type,==,employee|exists:business_profiles,company_unique_id',
                'front_aadhaar_card' => 'required_if:user_type,==,freelancer',
                'back_aadhaar_card' => 'required_if:user_type,==,freelancer',
                'pan_card_number' => 'required_if:user_type,==,company',
                'gst_number' => 'required_if:user_type,==,company',
                'service_id' => 'required|array',
                'bio' => 'required',
                'address_line_one' => 'required',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
            }
            else{
                try{
                    $user = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->user_id);
                    if(is_null($user)){
                        return response()->json(['sucsess'=>false,'message'=>'User not found.'],400);
                    }

                    if(($request->user_type == "employee") && ($request->has('company_id'))){
                        $company = BusinessProfile::where('company_unique_id',$request->company_id)->first();
                        if(is_null($company)){
                            return response()->json(['sucsess'=>false,'message'=>'Company not exists.'],400);
                        }
                    }

                    $businessProfile = BusinessProfile::where('user_id',$user->id)->first();
                    if(is_null($businessProfile)):
                        $businessProfile = new BusinessProfile;
                    endif;
                    $businessProfile->user_id = $user->id;
                    $businessProfile->business_name = $request->business_name;
                    $businessProfile->business_phone_no = '+91'.$request->business_phone_no;
                    $businessProfile->pan_card_number = $request->pan_card_number;
                    $businessProfile->gst_number = $request->gst_number;
                    $businessProfile->year_of_exp = $request->year_of_exp;
                    $businessProfile->bio = $request->bio;
                    if(($request->user_type == "employee") && ($request->has('company_id'))){
                        $businessProfile->company_id = $company->user_id;
                    }
                    if(($request->user_type == "company")){
                        $businessProfile->company_unique_id = 'TR'.$user->id;
                    }
                    if($request->hasFile('front_aadhaar_card') &&  $request->hasFile('back_aadhaar_card')){
                        if((!is_null($businessProfile->front_aadhaar_card)) && \Storage::exists($businessProfile->front_aadhaar_card) && (!is_null($businessProfile->back_aadhaar_card)) && \Storage::exists($businessProfile->back_aadhaar_card))
                        {
                            \Storage::delete($businessProfile->front_aadhaar_card);
                            \Storage::delete($businessProfile->back_aadhaar_card);
                        }
                        $businessProfile->front_aadhaar_card = $request->file('front_aadhaar_card')->store('public/business');
                        $businessProfile->back_aadhaar_card = $request->file('back_aadhaar_card')->store('public/business');
                    }

                    if($businessProfile->save()){

                        if(count($request->service_id) > 0){
                            foreach($request->service_id as $key => $value){
                                BusinessService::updateOrCreate(['user_id'=>$user->id,'business_profile_id'=>$businessProfile->id,'service_id'=>$value]);
                            }
                        }

                        BusinessAddress::updateOrCreate(['user_id'=>$user->id,'business_profile_id'=>$businessProfile->id],
                            [
                                'address_line_one'=>$request->address_line_one,
                                'address_line_two'=>$request->address_line_two,
                                'state'=>$request->state,
                                'city'=>$request->city,
                                'pincode'=>$request->pincode,
                                'latitude'=>$request->latitude,
                                'longitude'=>$request->longitude
                            ]);

                        if($request->hasFile('business_images')){
                            if(is_array($request->business_images)):
                                $businessImages = BusinessImage::where('business_profile_id',$businessProfile->id)->get();
                                if(count($businessImages)>0):
                                    foreach($businessImages as $key => $value):
                                        if((!is_null($value->business_image)) && \Storage::exists($value->business_image)):
                                            \Storage::delete($value->business_image);
                                        endif;
                                    endforeach;
                                else:
                                    foreach($request->business_images as $image):
                                        $businessImage = new BusinessImage;
                                        $businessImage->user_id = $user->id;
                                        $businessImage->business_profile_id = $businessProfile->id;
                                        $businessImage->business_image = $image->store('public/businessImage');
                                        $businessImage->save();
                                    endforeach;
                                endif;
                            else:
                                $businessImage = BusinessImage::where('user_id',$user->id)->where('business_profile_id',$businessProfile->id)->first();
                                if(!is_null($businessImage)):
                                    if((!is_null($businessImage->business_image)) && \Storage::exists($businessImage->business_image))
                                    {
                                        \Storage::delete($businessImage->business_image);
                                    }
                                    $businessImage->business_image = $request->file('business_images')->store('public/businessImage');
                                    $businessImage->save();
                                else:
                                    $businessImage = new BusinessImage;
                                    $businessImage->user_id = $user->id;
                                    $businessImage->business_profile_id = $businessProfile->id;
                                    $businessImage->business_image = $request->file('business_images')->store('public/businessImage');
                                    $businessImage->save();
                                endif;
                            endif;
                        }
                        if(($request->user_type == "company")){
                            $data = array('company_id'=>$businessProfile->company_unique_id);
                        }
                        else{
                            $data = null;
                        }
                        return response()->json([
                            'success' => true,
                            'message' => 'Your business profile has been added.',
                            'data' => $data
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
            $array = ['request'=>'add business profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

}
