<?php

namespace App\Http\Controllers\API\Provider;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
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
                                'email' => $provider->email,
                                'user_type' => $provider->role
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
            ->with(['business_profile','business_images','business_services','business_address'])
            ->select('id','email','name','phone_number','image','role')->find($request->user()->id);

            if(!is_null($provider)){
                $provider['user_type'] = $provider->role;
                if(!is_null($provider['image'])){
                    $url = \Storage::url($provider['image']);
                    $provider['image'] =  asset($url);
                }
                else{
                    $provider['image'] = asset('empty.jpg');
                }

                if(!is_null($provider->business_profile)){
                    if(!is_null($provider->business_profile->front_aadhaar_card) && (!is_null($provider->business_profile->back_aadhaar_card))){
                        $url = \Storage::url($provider->business_profile->front_aadhaar_card);
                        $provider->business_profile->front_aadhaar_card =  asset($url);
    
                        $url = \Storage::url($provider->business_profile->back_aadhaar_card);
                        $provider->business_profile->back_aadhaar_card =  asset($url);
                    }
                }
                
                if(count($provider->business_images)>0){
                    foreach($provider->business_images as $image){
                        if(!is_null($image->business_image)){
                            $url = \Storage::url($image->business_image);
                            $image->business_image =  asset($url);
                        }
                        else{
                            $image->business_image = asset('empty.jpg');
                        }
                    }
                }

                if(count($provider->business_services)>0){
                    foreach($provider->business_services as $service){
                        if(!is_null($service->service->image)){
                            $url = \Storage::url($service->service->image);
                            $service->service->image =  asset($url);
                        }
                        else{
                            $service->service->image = asset('empty.jpg');
                        }
                    }
                }

                $provider->total_rating = number_format($provider->business_reviews->avg('rating'),2);
                $provider->total_review = $provider->business_reviews->count();
                $provider->total_services = $provider->business_services->count();
                $provider->makeHidden('business_reviews','role');

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
