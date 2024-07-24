<?php

namespace App\Http\Controllers\API\Customer;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\FavouriteBusiness;
use App\Models\BusinessReview;
use Illuminate\Http\Request;
use App\Models\User;

class CustomerApiController extends Controller
{
    // Add Favourite Businesses
    public function favourite_business(Request $request)
    {
        try{
            if($request->isMethod('get'))
            {
                $favourite_business = FavouriteBusiness::where('user_id',$request->user()->id)->get();
                if(count($favourite_business)>0){
                    foreach($favourite_business as $business){
                        $business->name = $business->business->business_profile->business_name;
                        if(count($business->business->business_images)> 0){
                            $image = $business->business->business_images[0]['business_image'];
                            $url = \Storage::url($image);
                            $business->image =  asset($url);
                        }
                        else{
                            $business->image = asset('empty.jpg');
                        }
                        $business->business_address = $business->business->business_address;
                        $business->total_rating = number_format($business->business->business_reviews->avg('rating'),2);
                        $business->total_review = $business->business->business_reviews->count();
                        $business->total_service = $business->business->business_services->count();
                    }
                    $favourite_business->makeHidden(['user_id','business_id','business','created_at','updated_at']);
                    return response()->json(['success'=>true,'message'=>'Business List','response'=>$favourite_business]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Business found.']);
                }
            }
            else if($request->isMethod('post'))
            {
                $validator = Validator::make($request->all(), [
                'business_id' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success'=>false,'message'=>$validator->errors()->first()],400);
                }
                $request['user_id'] = $request->user()->id;
                $business = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->business_id);
                if(!is_null($business)){
                    $is_already_exists = FavouriteBusiness::where('user_id',$request->user_id)->where('business_id',$business->id)->first();
                    if(!is_null($is_already_exists)){
                        $is_already_exists->delete();
                        return response()->json(['success'=>true,'message'=>'Business removed from your favourite table.']);
                    }
                    else{
                        FavouriteBusiness::create($request->all());
                        return response()->json(['success'=>true,'message'=>'Business added in your favourite table.']);
                    }
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Business found.']);
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'Invalid Method.']);
            }

        }
        catch(\Exception $e){
            $array = ['request'=>'favourite business api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }


    // Top Rated Businesses
    public function businesses(Request $request)
    {
        try{
            if($request->filled('param')):
                if($request->param == "company"):
                    $businesses = User::with('business_address')->whereHas('roles',function($q){ $q->where('role_name','company'); })
                    ->withCount(['business_reviews as total_rating' => function($query)
                    {
                        $query->select(\DB::raw('coalesce(avg(rating),0)'));
                    }])->orderByDesc('total_rating')
                    ->paginate(25);
                endif;
                if($request->param == "freelancer"):
                    $businesses = User::with('business_address')->whereHas('roles',function($q){ $q->where('role_name','freelancer'); })
                    ->withCount(['business_reviews as total_rating' => function($query)
                    {
                        $query->select(\DB::raw('coalesce(avg(rating),0)'));
                    }])->orderByDesc('total_rating')
                    ->paginate(25);
                endif;
                if($request->param == "employee"):
                    $businesses = User::with('business_address')->whereHas('roles',function($q){ $q->where('role_name','employee'); })
                    ->withCount(['business_reviews as total_rating' => function($query)
                    {
                        $query->select(\DB::raw('coalesce(avg(rating),0)'));
                    }])->orderByDesc('total_rating')
                    ->paginate(25);
                endif;
                if($request->param == "search"):
                    $search = $request->search_value;
                    $businesses = User::with('business_address')->whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->where('name','LIKE',"%{$search}%")->orderBy('id','desc')->select('id')->paginate(25);
                endif;
            endif;
            if(!isset($request->param)):
                $businesses = User::with('business_address')->whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })
                ->withCount(['business_reviews as total_rating' => function($query)
                {
                    $query->select(\DB::raw('coalesce(avg(rating),0)'));
                }])->orderByDesc('total_rating')
                ->orderBy('id','desc')->paginate(25);
            endif;
            if(count($businesses)>0):
                foreach($businesses as $business){
                    $business->user_type = $business->role;
                    $isFavouriteBusiness = FavouriteBusiness::where('user_id',$request->user()->id)->where('business_id',$business->id)->first();
                    $business->is_favourite = (!is_null($isFavouriteBusiness)) ? 1 : 0;
                    $business->total_rating = number_format($business->business_reviews->avg('rating'),2);
                    $business->total_review = $business->business_reviews->count();
                    $business->total_service = count($business->business_services);
                    $business->name = $business->business_profile->business_name;
                    if(count($business->business_images)> 0){
                        $image = $business->business_images[0]['business_image'];
                        $url = \Storage::url($image);
                        $business->image =  asset($url);
                    }
                    else{
                        $business->image = asset('empty.jpg');
                    }
                }
                $businesses->makeHidden(['role','email','business_images','business_profile','created_at','updated_at','report_status','account_status','status','email_verified_at','phone_number_code','phone_number_expired_at','business_reviews','business_services','phone_number','provider_address','created_at','updated_at']);
                $paginator = array("current_page"=>$businesses->currentPage(),"total"=>$businesses->total(),"per_page"=>$businesses->perPage(),"next_page_url"=>(string)$businesses->nextPageUrl(),"prev_page_url"=>(string)$businesses->previousPageUrl(),"last_page_url"=>(string)$businesses->url($businesses->lastPage()),"last_page"=>$businesses->lastPage(),"from"=>$businesses->firstItem(),"to"=>$businesses->lastItem());
                $response = array('data'=>$businesses->items(),"paginator"=>$paginator);
                return response()->json(['success'=>true,'message'=>'Business Lists','response'=>$response]);
            else:
                return response()->json(['success'=>false,'message'=>'No Business found.']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'get all business','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // get business profile
    public function getBusinessProfile(Request $request,$businessId)
    {
        try{
            $userId = $request->user()->id;
            $business = User::with(['business_services','business_reviews'])->whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($businessId);
            if(!is_null($business)){
                if(count($business->business_images)> 0){
                    $image = $business->business_images[0]['business_image'];
                    $url = \Storage::url($image);
                    $business->image =  asset($url);
                }
                else{
                    $business->image = asset('empty.jpg');
                }
                if(count($business->business_services)):
                    foreach($business->business_services as $service):
                        $service->service_name = $service->service->name;
                    endforeach;
                endif;
                $isFavouriteBusiness = FavouriteBusiness::where('user_id',$request->user()->id)->where('business_id',$business->id)->first();
                $data = array();
                $data['id'] = $business->id;
                $data['name'] = $business->business_profile->business_name;
                $data['image'] = $business->image;
                $data['is_favourite'] = (!is_null($isFavouriteBusiness)) ? 1 : 0;
                $data['total_rating'] = number_format($business->business_reviews->avg('rating'),2);
                $data['total_reviews'] = $business->business_reviews->count();
                $data['tatal_services'] = count($business->business_services);
                $data['services'] = $business->business_services;
                $data['address'] = $business->business_address;
                $business->business_services->makeHidden(['service','status','bio','created_at','updated_at']);
                return response()->json(['success'=>true,'message'=>'Business Detail','response'=>$data]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'Business not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'get business profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Get Business Reviews
    public function getBusinessReview(Request $request,$businessId)
    {
        try
        {
            $userId = $request->user()->id;
            $business = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($businessId);
            if(!is_null($business)){
                $reviews = BusinessReview::where('business_id',$business->id)->get();
                if(count($reviews)>0){
                    foreach($reviews as $review){
                        if(!is_null($review->user->image)){
                            $url = \Storage::url($review->user->image);
                            $review->user_image =  asset($url);
                        }
                        else{
                            $review->user_image = asset('empty.jpg');
                        }
                        $review->user_name  = $review->user->name;
                        $review->review_date_time = $review->created_at->diffForHumans();
                    }
                    $review->makeHidden(['status','user','created_at','updated_at']);
                    return response()->json(['success'=>true,'message'=>'Business Review','response'=>$reviews]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Review']);
                }
            }
            else{
                return response()->json(['success'=>false,'message'=>'Business not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'get business reviews','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add Business Reviews
    public function businessReviews(Request $request)
    {
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user))
            {
                $validator = Validator::make($request->all(), [
                    'business_id' => 'required|numeric|exists:users,id',
                    'rating' => 'required|integer|between:1,5',
                    'comment' => 'required'
                ]);
                if ($validator->fails())
                {
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                }
                else
                {
                    $business = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer','company']); })->find($request->business_id);
                    if(!is_null($business)):
                        $request['user_id'] = $user->id;
                        BusinessReview::updateOrCreate(['business_id'=>$business->id,'user_id'=>$user->id],['rating'=>$request->rating,'comment'=>$request->comment]);
                        return response()->json(['success'=>true,'message'=>'Review has been added.']);
                    else:
                        return response()->json(['success'=>false,'message'=>'The selected business is not found']);
                    endif;
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'User not found']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'add business review api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
