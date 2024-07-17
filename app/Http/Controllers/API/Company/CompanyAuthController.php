<?php

namespace App\Http\Controllers\API\Company;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use Illuminate\Http\Request;
//use Twilio\Rest\Client;
use App\Mail\SendOtp;
use App\Models\Role;
use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;
use Auth;

class CompanyAuthController extends Controller
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
            'name'=>'required',
            'email' => 'required|email',
            'password' => 'required',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,phone_number',
            'device_type' => 'required|in:android,ios',
            'firebase_token' => 'required',
            'udid'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
        }
        else{
            try{
                $checkEmail = User::where('email',$request->email)->where('role','company')->first();
                $checkPhoneNo = User::where('phone_number',$request->phone_number)->where('role','company')->first();
                if(is_null($checkEmail) && is_null($checkPhoneNo)){
                    $user = new User;
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->password = \Hash::make($request->password);
                    $user->phone_number  = '+91'.$request->phone_number;
                    $user->phone_number_expired_at = Carbon::now()->addMinutes(30);
                    $user->phone_number_code = mt_rand(1000,9999);
                    $user->role = "company";
                    $user->status = 0;
                    if($user->save())
                    {
                        //$message = "Please Verify Your Account. Your OTP is ".$user->phone_number_code;

                        //$this->sendMessage($message, $user->phone_number);
                        //$generate_token = $user->createToken('OneTap_'.$user->id)->plainTextToken;

                        $role = Role::where('role_name','company')->first();
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
                    return response()->json(['sucsess'=>false,'message'=>'The email / Phone Number has already been taken.']);
                }
            }
            catch(\Exception $e){
                $array = ['request'=>'company Register API','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // Verify Phone Number OTP
    public function verifyPhoneNumberOtp(Request $request)
    {
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','company_id'=>'required|integer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
            if(!is_null($company)):
              if($company->phone_number_code == $request->otp)
              {
                if(Carbon::now() > $company->phone_number_expire_at):
                  return response()->json(['sucsess' => false,'message' => 'OTP Expired.']);
                else:
                    $company->phone_number_verified_at = now();
                    $company->save();
                    return response()->json(['sucsess' => true,'message' => 'OTP Matched.']);
                endif;
              }
              else
              {
                return response()->json(['sucsess' => false,'message' => 'OTP not matched']);
              }
            else:
              return response()->json(['sucsess' => false,'message' => 'Company not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'company verify otp','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // login
    public function login(Request $request){
        $rules = ['email'=>'required|email','password'=>'required','device_type' => 'required|in:android,ios','firebase_token' => 'required','udid'=>'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
        {
          return response()->json(['status' => false,'message' => $validator->errors()->first()]);
        }
        else
        {
            try{
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role'=>'company'])):
                $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find(auth()->user()->id);
                    if(!is_null($company)):

                        $company->status = 1;
                        $company->save();

                        FirebaseNotification::updateOrCreate(['user_id'=>$company->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);

                        $generate_token = $company->createToken('OneTap_'.$company->id)->plainTextToken;

                        return response()->json([
                            'status'=>2,
                            'success' => true,
                            'message' => "You're successfully login.",
                            'token' => $generate_token,
                            'response' => [
                                'id' => $company->id,
                                'name' => $company->name,
                                'email' => $company->email
                            ]
                        ]);
                    else:
                        $array = ['request'=>$request->all(),'message'=>'Company not found'];
                        \Log::info($array);
                        return response()->json(['sucsess'=>false,'message'=>'Company not found']);
                    endif;
                else:
                    $array = ['request'=>$request->all(),'message'=>'These credentials do not match our records.'];
                    \Log::info($array);
                    return response()->json(['sucsess'=>false,'message'=>'These credentials do not match our records.']);
                endif;

            }
            catch(\Exception $e){
                $array = ['request'=>'company login api','message'=>$e->getMessage()];
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
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->where('email',$request->email)->first();
            if(!is_null($company)):
                $otp = Otp::whereUserId($company->id)->first();
                if(!is_null($otp)):
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($company->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $company->id,
                                'name' => $company->name,
                                'email' => $company->email
                            ]
                        ]);
                    else:
                      return response()->json(['success' => false,'message' => 'Something problem when generating otp']);
                    endif;
                else:
                    $otp = new Otp;
                    $otp->user_id = $company->id;
                    $otp->expire_at = Carbon::now()->addMinutes(5);
                    $otp->code = mt_rand(1000,9999);
                    if($otp->save()):
                        //Mail::to($company->email)->send(new SendOtp($otp));
                        return response()->json([
                            'success' => true,
                            'message' => 'OTP sent to your mail.',
                            'response' => [
                                'id' => $company->id,
                                'name' => $company->name,
                                'email' => $company->email
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
        $validator = Validator::make($request->all(), ['otp'=>'required|numeric','company_id'=>'required|integer']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;

        try{
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
            if(!is_null($company)):
              $otp = Otp::whereCode($request->otp)->whereUserId($company->id)->first();
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
              return response()->json(['sucsess' => false,'message' => 'Company not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'company verify otp','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }

    }

    // Reset Password
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), ['company_id'=>'required|integer','password'=>'required|string|confirmed']);
        if ($validator->fails()):
          return response()->json(['sucsess' => false,'message' => $validator->errors()->first()]);
        endif;
        try{
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
            if(!is_null($company)):
                $password = \Hash::make($request->password);
                $company->password = $password;
                if($company->save()):
                    //Auth::logout();
                  return response()->json(['sucsess' => true,'message' => 'Your password has been changed successfully.']);
                else:
                  return response()->json(['sucsess' => false,'message' => 'Your password has not been changed']);
                endif;
            else:
              return response()->json(['sucsess' => false,'message' => "Company not found"]);
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
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->user()->id);
            if(!is_null($company)):
                $validator = Validator::make($request->all(), ['old_password'=>'required','password'=>'required|string|min:6|confirmed']);
                if ($validator->fails()):
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                endif;
                $password = \Hash::make($request->password);
                if(\Hash::check($request->old_password,$company->password)):
                    $company->password = $password;
                    if($company->save()):
                      return response()->json(['success' => true,'message' => 'Your password has been changed successfully.']);
                    else:
                      return response()->json(['success' => false,'message' => 'Your password has not been changed']);
                    endif;
                else:
                    return response()->json(['success' => false,'message' => "Password does not matches with current password"]);
                endif;
            else:
                return response()->json(['success'=>false,'message'=>'Company not found']);
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
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->user()->id);
            if(!is_null($company)):

                $company->status = 0;
                $company->save();

                $request->user()->tokens()->delete();

                return response()->json(['sucsess'=>true,'message'=>"You have been logged out!."]);
            else:
                return response()->json(['sucsess'=>false,'message'=>'Company not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'company logout','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }


    // Company Profile
    public function company_profile(Request $request)
    {
        try{
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->select('id','email','name','phone_number','image')->find($request->user()->id);
            if(!is_null($company)){
                if(!is_null($company['image'])){
                    $url = \Storage::url($company['image']);
                    $company['image'] =  asset($url);
                }
                else{
                    $company['image'] = asset('empty.jpg');
                }
                return response()->json(['sucsess'=>true,'message'=>'Your Profile','response'=>$company]);
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'Company not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'company profile api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Update Profile
    public function edit_profile(Request $request)
    {
        try{
            $company = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone_number' => 'required|unique:users,phone_number,'.$company->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
            }

            else{
                try{
                    if($request->hasFile('image')){
                        if((!is_null($company->image)) && \Storage::exists($company->image))
                        {
                            \Storage::delete($company->image);
                        }
                        $company->image = $request->file('image')->store('public/company');
                    }

                    $company->name = $request->name ?? $company->name;
                    $company->phone_number  = '+91'.$request->phone_number ?? $company->phone_number;
                    if($company->save())
                    {
                        return response()->json([
                            'success' => true,
                            'message' => 'Your profile has been updated.',
                       ]);
                    }
                    else
                    {
                        return response()->json(['sucsess'=>false,'message'=>'Something problem, while registration']);
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
            $array = ['request'=>'company edit profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }
}
