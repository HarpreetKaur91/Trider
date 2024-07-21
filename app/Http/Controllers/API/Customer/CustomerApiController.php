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
    // Add Favourite Business
    public function favourite_business(Request $request)
    {
        try{
            if($request->isMethod('get'))
            {
                $favourite_business = FavouriteBusiness::where('user_id',$request->user()->id)->get();
                if(count($favourite_business)>0){
                    foreach($favourite_business as $business){
                        $services = BusinessService::where('user_id',$business->business_id)->count();
                        $business->name = $business->business->name;
                        if(!is_null($provider->provider->image)){
                            $url = \Storage::url($provider->provider->image);
                            $provider->image =  asset($url);
                        }
                        else{
                            $provider->image = asset('empty.jpg');
                        }
                        $provider->complete_address = (!is_null($provider->provider->provider_address)) ? $provider->provider->provider_address->complete_address : "";
                        $provider->total_rating = 0;
                        $provider->total_review = 0;
                        $provider->total_service = $services;
                    }
                    $favourite_providers->makeHidden(['user_id','provider_id','provider','created_at','updated_at']);
                    return response()->json(['success'=>true,'message'=>'Provider List','response'=>$favourite_providers]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Provider found.']);
                }
            }
            else if($request->isMethod('post'))
            {
                $validator = Validator::make($request->all(), [
                'provider_id' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success'=>false,'message'=>$validator->errors()->first()],400);
                }
                $request['user_id'] = $request->user()->id;
                $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->provider_id);
                if(!is_null($provider)){
                    $is_already_exists = FavouriteProvider::where('user_id',$request->user_id)->where('provider_id',$provider->id)->first();
                    if(!is_null($is_already_exists)){
                        $is_already_exists->delete();
                        return response()->json(['success'=>true,'message'=>'Provider removed from your favourite table.']);
                    }
                    else{
                        FavouriteProvider::create($request->all());
                        return response()->json(['success'=>true,'message'=>'Provider added in your favourite table.']);
                    }
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Provider found.']);
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'Invalid Method.']);
            }

        }
        catch(\Exception $e){
            $array = ['request'=>'favourite provider api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add Favourite Company
    public function favourite_companies(Request $request)
    {
        try{
            if($request->isMethod('get'))
            {
                $favourite_companies = FavouriteCompany::where('user_id',$request->user()->id)->get();
                if(count($favourite_companies)>0){
                    foreach($favourite_companies as $company){
                        $services = CompanyService::where('user_id',$company->company_id)->count();
                        $company->name = $company->company->company_profile->company_name;
                        if(!is_null($company->company->image)){
                            $url = \Storage::url($company->company->image);
                            $company->image =  asset($url);
                        }
                        else{
                            $company->image = asset('empty.jpg');
                        }
                        $company->complete_address = (!is_null($company->company->company_address)) ? $company->company->company_address->complete_address : "";
                        $company->total_rating = 0;
                        $company->total_review = 0;
                        $company->total_service = $services;
                    }
                    $favourite_companies->makeHidden(['user_id','company_id','company','created_at','updated_at']);
                    return response()->json(['success'=>true,'message'=>'Company List','response'=>$favourite_companies]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Company found.']);
                }
            }
            else if($request->isMethod('post'))
            {
                $validator = Validator::make($request->all(), [
                'company_id' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success'=>false,'message'=>$validator->errors()->first()],400);
                }
                $request['user_id'] = $request->user()->id;
                $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
                if(!is_null($company)){
                    $is_already_exists = FavouriteCompany::where('user_id',$request->user_id)->where('company_id',$company->id)->first();
                    if(!is_null($is_already_exists)){
                        $is_already_exists->delete();
                        return response()->json(['success'=>true,'message'=>'Company has been removed from your favourite table.']);
                    }
                    else{
                        FavouriteCompany::create($request->all());
                        return response()->json(['success'=>true,'message'=>'Company has been added in your favourite table.']);
                    }
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Company found.']);
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'Invalid Method.']);
            }

        }
        catch(\Exception $e){
            $array = ['request'=>'favourite company api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Top Rated Providers
    public function providers(Request $request)
    {
        try{
            if($request->filled('param')):
                if($request->param == "top_rated"):
                    $providers = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })
                    ->withCount(['provider_reviews as total_rating' => function($query)
                    {
                        $query->select(\DB::raw('coalesce(avg(rating),0)'));
                    }])->orderByDesc('total_rating')
                    ->paginate(25);
                    $providers->makeHidden(['created_at','updated_at','report_status','account_status','status','email_verified_at','phone_number_code','phone_number_expired_at']);
                endif;
                if($request->param == "search"):
                    $search = $request->search_value;
                    $providers = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->where('name','LIKE',"%{$search}%")->orderBy('id','desc')->select('id','name','image','phone_number')->paginate(25);
                endif;
            endif;
            if(!isset($request->param)):
                $providers = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })
                ->withCount(['provider_reviews as total_rating' => function($query)
                {
                    $query->select(\DB::raw('coalesce(avg(rating),0)'));
                }])->orderByDesc('total_rating')
                ->orderBy('id','desc')->select('id','name','image','phone_number')->paginate(25);
            endif;
            if(count($providers)>0):
                foreach($providers as $provider){
                    $isFavouriteProvider = FavouriteProvider::where('user_id',$request->user()->id)->where('provider_id',$provider->id)->first();

                    $provider->is_favourite = (!is_null($isFavouriteProvider)) ? 1 : 0;
                    $provider->total_rating = (count($provider->provider_reviews)>0) ? number_format($provider->provider_reviews->avg('rating'),2) : 0;
                    $provider->total_review = $provider->provider_reviews->count();
                    $provider->total_service = count($provider->provider_services);
                    $provider->complete_address = (!is_null($provider->provider_address)) ? $provider->provider_address->complete_address : "";

                    if(!is_null($provider->image)){
                        $url = \Storage::url($provider->image);
                        $provider->image =  asset($url);
                    }
                    else{
                        $provider->image = asset('empty.jpg');
                    }
                }
                $providers->makeHidden(['provider_reviews','provider_services','phone_number','provider_address','created_at','updated_at']);
                $paginator = array("current_page"=>$providers->currentPage(),"total"=>$providers->total(),"per_page"=>$providers->perPage(),"next_page_url"=>(string)$providers->nextPageUrl(),"prev_page_url"=>(string)$providers->previousPageUrl(),"last_page_url"=>(string)$providers->url($providers->lastPage()),"last_page"=>$providers->lastPage(),"from"=>$providers->firstItem(),"to"=>$providers->lastItem());
                $response = array('data'=>$providers->items(),"paginator"=>$paginator);
                return response()->json(['success'=>true,'message'=>'Provider List','response'=>$response]);
            else:
                return response()->json(['success'=>false,'message'=>'No Provider found.']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'get all providers','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Top Rated Companies
    public function companies(Request $request)
    {
        try{
            if($request->filled('param')):
                if($request->param == "search"):
                    $search = $request->search_value;
                    $companies = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->where('name','LIKE',"%{$search}%")->orderBy('id','desc')->select('id','name','image','phone_number')->paginate(25);
                endif;
            endif;
            if(!isset($request->param)):
                $companies = User::whereHas('roles',function($q){ $q->where('role_name','company'); })
                    ->withCount(['company_reviews as total_rating' => function($query)
                    {
                        $query->select(\DB::raw('coalesce(avg(rating),0)'));
                    }])->orderByDesc('total_rating')
                    ->paginate(25);
                    $companies->makeHidden(['created_at','updated_at','report_status','account_status','status','email_verified_at','phone_number_code','phone_number_expired_at']);
            endif;
            if(count($companies)>0):
                foreach($companies as $company){
                    $isFavouriteCompany = FavouriteCompany::where('user_id',$request->user()->id)->where('company_id',$company->id)->first();

                    $company->is_favourite = (!is_null($isFavouriteCompany)) ? 1 : 0;
                    $company->total_rating = (count($company->company_reviews)>0) ? number_format($company->company_reviews->avg('rating'),2) : 0;
                    $company->total_review = $company->company_reviews->count();
                    $company->total_service = count($company->company_services);
                    $company->complete_address = (!is_null($company->company_address)) ? $company->company_address->complete_address : "";

                    if(!is_null($company->image)){
                        $url = \Storage::url($company->image);
                        $company->image =  asset($url);
                    }
                    else{
                        $company->image = asset('empty.jpg');
                    }
                }
                $companies->makeHidden(['company_reviews','company_services','phone_number','company_address','created_at','updated_at']);
                $paginator = array("current_page"=>$companies->currentPage(),"total"=>$companies->total(),"per_page"=>$companies->perPage(),"next_page_url"=>(string)$companies->nextPageUrl(),"prev_page_url"=>(string)$companies->previousPageUrl(),"last_page_url"=>(string)$companies->url($companies->lastPage()),"last_page"=>$companies->lastPage(),"from"=>$companies->firstItem(),"to"=>$companies->lastItem());
                $response = array('data'=>$companies->items(),"paginator"=>$paginator);
                return response()->json(['success'=>true,'message'=>'Company List','response'=>$response]);
            else:
                return response()->json(['success'=>false,'message'=>'No Company found.']);
            endif;
        }
        catch(\Exception $e){
            $array = ['request'=>'get all companies','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // get provider profile
    public function getProviderProfile(Request $request,$providerId)
    {
        try{
            $userId = $request->user()->id;
            $provider = User::with(['provider_services','provider_reviews'])->whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($providerId);
            if(!is_null($provider)){
                if(!is_null($provider->image)){
                    $url = \Storage::url($provider->image);
                    $provider->image =  asset($url);
                }
                else{
                    $provider->image = asset('empty.jpg');
                }
                if(count($provider->provider_services)):
                    foreach($provider->provider_services as $service):
                        $service->service_name = $service->service->name;
                    endforeach;
                endif;
                $favouriteProvider = FavouriteProvider::where('user_id',$request->user()->id)->where('provider_id',$provider->id)->first();
                $data = array();
                $data['id'] = $provider->id;
                $data['name'] = $provider->name;
                $data['image'] = $provider->image;
                $data['is_favourite'] = (!is_null($favouriteProvider)) ? 1 : 0;
                $data['total_rating'] = (count($provider->provider_reviews)>0) ? number_format($provider->provider_reviews->avg('rating'),2) : 0;
                $data['total_reviews'] = $provider->provider_reviews->count();
                $data['tatal_services'] = count($provider->provider_services);
                $provider->complete_address = (!is_null($provider->provider_address)) ? $provider->provider_address->complete_address : "";
                $data['services'] = $provider->provider_services;
                $provider->provider_services->makeHidden(['service','status','bio','created_at','updated_at']);
                return response()->json(['success'=>true,'message'=>'Provider Detail','response'=>$data]);
            }
            else{
                return response()->json(['success'=>false,'message'=>'Provider not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'get provider profile','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Get Provider Reviews
    public function getProviderReview(Request $request,$providerId)
    {
        try
        {
            $userId = $request->user()->id;
            $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($providerId);
            if(!is_null($provider)){
                $reviews = ProviderReview::where('provider_id',$provider->id)->get();
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
                    return response()->json(['success'=>true,'message'=>'Provider Review','response'=>$reviews]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Review']);
                }
            }
            else{
                return response()->json(['success'=>false,'message'=>'Provider not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'get provider reviews','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add Provider Reviews
    public function providerReviews(Request $request)
    {
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user))
            {
                $validator = Validator::make($request->all(), [
                    'provider_id' => 'required|numeric|exists:users,id',
                    'rating' => 'required|integer|between:1,5',
                    'comment' => 'required'
                ]);
                if ($validator->fails())
                {
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                }
                else
                {
                    $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($request->provider_id);
                    if(!is_null($provider)):
                        $request['user_id'] = $user->id;
                        ProviderReview::updateOrCreate(['provider_id'=>$provider->id,'user_id'=>$user->id],['rating'=>$request->rating,'comment'=>$request->comment]);
                        return response()->json(['success'=>true,'message'=>'Review has been added.']);
                    else:
                        return response()->json(['success'=>false,'message'=>'The selected provider is not found']);
                    endif;
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'User not found']);
            }

        }
        catch(\Exception $e){
            $array = ['request'=>'add provider review api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Get Company Reviews
    public function getCompanyReview(Request $request,$companyId)
    {
        try
        {
            $userId = $request->user()->id;
            $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($companyId);
            if(!is_null($company)){
                $reviews = CompanyReview::where('company_id',$company->id)->get();
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
                    return response()->json(['success'=>true,'message'=>'Company Review','response'=>$reviews]);
                }
                else{
                    return response()->json(['success'=>false,'message'=>'No Review']);
                }
            }
            else{
                return response()->json(['success'=>false,'message'=>'Company not found.']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'get company reviews','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // Add Company Reviews
    public function companyReviews(Request $request)
    {
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user))
            {
                $validator = Validator::make($request->all(), [
                    'company_id' => 'required|numeric|exists:users,id',
                    'rating' => 'required|integer|between:1,5',
                    'comment' => 'required'
                ]);
                if ($validator->fails())
                {
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                }
                else
                {
                    $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($request->company_id);
                    if(!is_null($company)):
                        $request['user_id'] = $user->id;
                        CompanyReview::updateOrCreate(['company_id'=>$company->id,'user_id'=>$user->id],['rating'=>$request->rating,'comment'=>$request->comment]);
                        return response()->json(['success'=>true,'message'=>'Review has been added.']);
                    else:
                        return response()->json(['success'=>false,'message'=>'The selected company is not found']);
                    endif;
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'User not found']);
            }

        }
        catch(\Exception $e){
            $array = ['request'=>'add companys review api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
