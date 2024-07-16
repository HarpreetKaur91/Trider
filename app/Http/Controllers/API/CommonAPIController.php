<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Models\Service;
use App\Models\User;
use App\Models\Page;
use App\Models\Faq;

class CommonAPIController extends Controller
{
    // Get all services
    public function services(Request $request)
    {
        if($request->filled('param')):
            if($request->param == "all"):
                $services = Service::where('parent_id',0)->where('status',1)->orderBy('position','asc')->select('id','name','description','image')->get();
            endif;
            if($request->param == "children"):
                $services = Service::where('parent_id',0)->where('status',1)->whereHas('children')->with('children')->orderBy('position','asc')->select('id','name','description','image')->get();
                $services->makeVisible(['children']);
            endif;
        endif;
        if(!isset($request->param)):
            $services = Service::where('parent_id',0)->where('status',1)->whereHas('children')->orderBy('position','asc')->select('id','name','description','image')->get();
        endif;
        if(count($services)>0):
            foreach($services as $service){
                if(!is_null($service->image)){
                    $url = \Storage::url($service->image);
                    $service->image =  asset($url);
                }
                else{
                    $service->image = asset('empty.jpg');
                }
                if($service->has('children')){
                    foreach($service->children as $child){
                        if(!is_null($child->image)){
                            $urlLink = \Storage::url($child->image);
                            $child->image =  asset($urlLink);
                        }
                        else{
                            $child->image = asset('empty.jpg');
                        }
                    }
                }
            }
            return response()->json(['success'=>true,'response'=>$services]);
        else:
            return response()->json(['success'=>false,'message'=>'Service not found']);
        endif;
    }

    // Contact Us
    public function contact_us(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()],400);
        }
        else{
            try{
                $user = User::find($request->user()->id);
                if(!is_null($user)){
                    $request['user_id'] = $user->id;
                    $contact_us = ContactUs::create($request->all());
                    return response()->json(['success'=>true,'message'=>'Your message has been submitted.']);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'User not found.']);
                }
            }
            catch(\Exception $e){
                $array = ['request'=>'contact us api','message'=>$e->getMessage()];
                \Log::info($array);
                return response()->json(['success'=>false,'message'=>$e->getMessage()]);
            }
        }
    }

    // About Us Content
    public function about_us_content(Request $request)
    {
        try{
            $user = User::find($request->user()->id);
            if(!is_null($user)){
                $page = Page::where('page_name','=','about-us')->select(['page_header','content'])->first();
                if(!is_null($page)){
                    $page->content = strip_tags($page->content);
                }
                return response()->json(['success'=>true,'message'=>'About Us Content.','response'=>$page]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'about us','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Privacy & Policy Content
    public function privacy_policy_content(Request $request)
    {
        try{
            $user = User::find($request->user()->id);
            if(!is_null($user)){
                $page = Page::where('page_name','=','privacy-policy')->select(['page_header','content'])->first();
                if(!is_null($page)){
                    $page->content = strip_tags($page->content);
                }
                return response()->json(['success'=>true,'message'=>'Privacy & Policy Content.','response'=>$page]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'privacy policy','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Term & Condition Content
    public function term_of_condition_content(Request $request)
    {
        try{
            $user = User::find($request->user()->id);
            if(!is_null($user)){
                $page = Page::where('page_name','=','term-of-condition')->select(['page_header','content'])->first();
                if(!is_null($page)){
                    $page->content = strip_tags($page->content);
                }
                return response()->json(['success'=>true,'message'=>'Term & Condition Content.','response'=>$page]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'term & condition','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // FAQ's
    public function faq_content(Request $request)
    {
        try{
            $user = User::find($request->user()->id);
            if(!is_null($user)){
                $faq = Faq::select(['question','answer'])->first();
                if(!is_null($faq)){
                    $faq->answer = strip_tags($faq->answer);
                }
                return response()->json(['success'=>true,'message'=>'Faq Content.','response'=>$faq]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'User not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'Faq','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
