<?php

namespace App\Http\Controllers\API\Customer;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class CustomerAuthController extends Controller
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
                if(Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role'=>'user'])):
                $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find(auth()->user()->id);
                    if(!is_null($user)):

                        $user->status = 1;
                        $user->save();

                        FirebaseNotification::updateOrCreate(['user_id'=>$user->id,'udid'=>$request->udid],['firebase_token'=>$request->firebase_token,'device_type'=>$request->device_type]);

                        $generate_token = $user->createToken('OneTap_'.$user->id)->plainTextToken;

                        return response()->json([
                            'success' => true,
                            'message' => "You're successfully login.",
                            'token' => $generate_token,
                            'response' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email
                            ]
                        ]);
                    else:
                        $array = ['request'=>$request->all(),'message'=>'User not found'];
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
                $array = ['request'=>'user login api','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // Change Password
    public function change_password(Request $request)
    {
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user)):
                $validator = Validator::make($request->all(), ['old_password'=>'required','password'=>'required|string|min:6|confirmed']);
                if ($validator->fails()):
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                endif;
                $password = \Hash::make($request->password);
                if(\Hash::check($request->old_password,$user->password)):
                    $user->password = $password;
                    if($user->save()):
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
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user)):

                $user->status = 0;
                $user->save();

                $request->user()->tokens()->delete();

                return response()->json(['sucsess'=>true,'message'=>"You have been logged out!."]);
            else:
                return response()->json(['sucsess'=>false,'message'=>'User not found']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'user logout','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }


    // User Profile
    public function user_profile(Request $request)
    {
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->select('id','email','name','phone_number','image')->find($request->user()->id);
            if(!is_null($user)){
                if(!is_null($user['image'])){
                    $url = \Storage::url($user['image']);
                    $user['image'] =  asset($url);
                }
                else{
                    $user['image'] = asset('empty.jpg');
                }
                return response()->json(['sucsess'=>true,'message'=>'Your Profile','response'=>$user]);
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'user profile api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Update Profile
    public function edit_profile(Request $request)
    {
        try{
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone_number' => 'required|unique:users,phone_number,'.$user->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
            }

            else{
                try{
                    if($request->hasFile('image')){
                        if((!is_null($user->image)) && \Storage::exists($user->image))
                        {
                            \Storage::delete($user->image);
                        }
                        $user->image = $request->file('image')->store('public/user');
                    }

                    $user->name = $request->name ?? $user->name;
                    $user->phone_number  = '+91'.$request->phone_number ?? $user->phone_number;
                    if($user->save())
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
            $array = ['request'=>'user edit profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }
}
