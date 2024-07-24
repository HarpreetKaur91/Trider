<?php

namespace App\Http\Controllers\API\Company;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\FirebaseNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class CompanyAuthController extends Controller
{
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
                            'success' => true,
                            'message' => "You're successfully login.",
                            'token' => $generate_token,
                            'response' => [
                                'id' => $company->id,
                                'name' => $company->name,
                                'email' => $company->email,
                                'user_type' => $company->role,
                            ]
                        ]);
                    else:
                        $array = ['request'=>$request->all(),'message'=>'Company not found'];
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
                $array = ['request'=>'company login api','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
            }
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
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->user()->id);
            if(!is_null($company)):

                $company->status = 0;
                $company->save();

                $request->user()->tokens()->delete();

                return response()->json(['sucsess'=>true,'message'=>"You have been logged out!."]);
            else:
                return response()->json(['sucsess'=>false,'message'=>'User not found']);
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
            $company = User::with(['business_profile','business_images','business_services','business_address'])
            ->whereHas('roles',function($q){ $q->where('role_name','company'); })
            ->select('id','email','name','phone_number','image','role')
            ->find($request->user()->id);
            if(!is_null($company)){
                $company['user_type'] = $company->role;
                if(!is_null($company['image'])){
                    $url = \Storage::url($company['image']);
                    $company['image'] =  asset($url);
                }
                else{
                    $company['image'] = asset('empty.jpg');
                }
                if(count($company->business_services)>0){
                    foreach($company->business_services as $service){
                        if(!is_null($service->service->image)){
                            $url = \Storage::url($service->service->image);
                            $service->service->image =  asset($url);
                        }
                        else{
                            $service->service->image = asset('empty.jpg');
                        }
                    }
                }
                if(count($company->business_images)>0){
                    foreach($company->business_images as $image){
                        if(!is_null($image->business_image)){
                            $url = \Storage::url($image->business_image);
                            $image->business_image =  asset($url);
                        }
                        else{
                            $image->business_image = asset('empty.jpg');
                        }
                    }
                }
                $company->total_rating = number_format($company->business_reviews->avg('rating'),2);
                $company->total_review = $company->business_reviews->count();
                $company->total_service = $company->business_services->count();
                $company->makeHidden(['business_reviews','role']);
                return response()->json(['sucsess'=>true,'message'=>'Your Profile','response'=>$company]);
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
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
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->user()->id);
            if(!is_null($company)){
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
                            return response()->json(['sucsess'=>false,'message'=>'Something problem, while updation']);
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
            $array = ['request'=>'company edit profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Accept OR Reject Provider Account
    public function acceptOrRejectProviderAccount(Request $request)
    {
        try{
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->user()->id);
            if(!is_null($company)){
                $validator = Validator::make($request->all(), [
                    'employee_id' => 'required|exists:users,id',
                    'account_status' => 'required|boolean'
                ]);

                if ($validator->fails()) {
                    return response()->json(['sucsess'=>false,'message'=>$validator->errors()->first()],400);
                }
                else{
                    $employee = User::whereHas('roles',function($q){ $q->where('role_name','employee'); })->where('company_id',$company->id)->find($request->employee_id);
                    if(!is_null($employee)){
                        $employee->account_status = $request->account_status;
                        if($employee->save())
                        {
                            return response()->json([
                                'success' => true,
                                'message' => 'Employee profile has been updated.',
                            ]);
                        }
                        else
                        {
                            return response()->json(['sucsess'=>false,'message'=>'Something problem, while updation']);
                        }
                    }
                    else{
                        return response()->json(['sucsess'=>false,'message'=>'Employee not found.']);
                    }
                }
            }
            else{
                return response()->json(['sucsess'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'accept or reject provider account','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['sucsess'=>false,'message'=>$e->getMessage()]);
        }
    }
}
