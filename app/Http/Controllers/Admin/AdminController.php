<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\FreelancersDataTable;
use App\DataTables\EmployeesDataTable;
use App\DataTables\CompaniesDataTable;
use App\DataTables\MessageDataTable;
use App\Http\Controllers\Controller;
use App\DataTables\UsersDataTable;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Models\Service;
use App\Models\User;
use App\Models\Page;

class AdminController extends Controller
{
    public function dashboard()
    {
        $data = [];
        $data['total_category'] = Service::count();
        $data['total_customers'] =  User::whereHas('roles',function($q){ $q->where('role_name','user'); })->count();
        $data['total_companies'] =  User::whereHas('roles',function($q){ $q->where('role_name','company'); })->count();
        $data['total_employees'] =  User::whereHas('roles',function($q){ $q->where('role_name','employee'); })->count();
        $data['total_freelancers'] =  User::whereHas('roles',function($q){ $q->where('role_name','freelancer'); })->count();
        $data['recent_customers'] = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->orderBy('id','desc')->limit(5)->get();
        $data['recent_companies'] = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->orderBy('id','desc')->limit(5)->get();
        $data['recent_providers'] = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->orderBy('id','desc')->limit(5)->get();
        return view('dashboard',compact('data'));
    }

    public function employee(EmployeesDataTable $dataTable)
    {
        return $dataTable->render('admin.providers.employee.index');
    }

    public function employee_profile($id)
    {
        $provider = User::whereHas('roles',function($q){ $q->where('role_name','employee'); })->findOrFail($id);
        if(!is_null($provider) && !is_null($provider->image)){
            $url = \Storage::url($provider->image);
            $provider->image =  asset($url);
        }
        else{
            $provider->image = asset('empty.jpg');
        }

        return view('admin.providers.employee.view',compact('provider'));
    }

    public function employee_destroy($id)
    {
        $provider = User::whereHas('roles',function($q){ $q->where('role_name','employee'); })->find($id);
        if(!is_null($provider)){
            if((!is_null($provider->image)) && \Storage::exists($provider->image))
            {
                \Storage::delete($provider->image);
            }
            $provider->delete();
            return redirect()->route('employee')->with(['alert'=>'success','message'=>'Employee has been successfully removed from the table']);
        }
        else{
            return redirect()->route('employee')->with(['alert'=>'danger','message'=>'Employee not found']);
        }
    }

    public function freelancer(FreelancersDataTable $dataTable)
    {
        return $dataTable->render('admin.providers.freelancer.index');
    }

    public function freelancer_profile($id)
    {
        $provider = User::whereHas('roles',function($q){ $q->where('role_name','freelancer'); })->findOrFail($id);
        if(!is_null($provider) && !is_null($provider->image)){
            $url = \Storage::url($provider->image);
            $provider->image =  asset($url);
        }
        else{
            $provider->image = asset('empty.jpg');
        }

        return view('admin.providers.freelancer.view',compact('provider'));
    }

    public function freelancer_destroy($id)
    {
        $provider = User::whereHas('roles',function($q){ $q->where('role_name','freelancer'); })->find($id);
        if(!is_null($provider)){
            if((!is_null($provider->image)) && \Storage::exists($provider->image))
            {
                \Storage::delete($provider->image);
            }
            $provider->delete();
            return redirect()->route('freelancer')->with(['alert'=>'success','message'=>'Freelancer has been successfully removed from the table']);
        }
        else{
            return redirect()->route('freelancer')->with(['alert'=>'danger','message'=>'Freelancer not found']);
        }
    }

    public function company(CompaniesDataTable $dataTable)
    {
        return $dataTable->render('admin.companies.index');
    }

    public function company_profile($id)
    {
        $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->findOrFail($id);
        if(!is_null($company) && !is_null($company->image)){
            $url = \Storage::url($company->image);
            $company->image =  asset($url);
        }
        else{
            $company->image = asset('empty.jpg');
        }

        return view('admin.companies.view',compact('company'));
    }

    public function company_destroy($id)
    {
        $company = User::whereHas('roles',function($q){ $q->where('role_name','company'); })->find($id);
        if(!is_null($company)){
            if((!is_null($company->image)) && \Storage::exists($company->image))
            {
                \Storage::delete($company->image);
            }
            $company->delete();
            return redirect()->route('company')->with(['alert'=>'success','message'=>'Company has been successfully removed from the table']);
        }
        else{
            return redirect()->route('company')->with(['alert'=>'danger','message'=>'Company not found']);
        }
    }

    public function verify_provider_status($id,$status)
    {
        $provider = User::whereHas('roles',function($q){ $q->whereIn('role_name',['employee','freelancer']); })->find($id);
        if(!is_null($provider))
        {
            $provider->account_status = $status;
            $provider->save();
            $msg = $provider->account_status == 1 ? 'success' : 'danger';
            \Session::flash('alert',$msg);
            \Session::flash('message',$provider->name.' provider status has been changed successfully.');

            if($provider->account_status == 1)
            {
                $notification =
                [
                    "title" => "Approved Provider Profile",
                    "message"=> "Your profile has been approved.",
                ];
                $data = ['type'=>'provider_profile'];
                //PushNotification::send($provider,$notification,$data);
                return 1;
            }
            else
            {
                $notification =
                [
                    "title" => "Rejected Provider Profile",
                    "message"=> "Your profile has been rejected.",
                ];
                $data = ['type'=>'provider_profile'];
                //PushNotification::send($provider,$notification,$data);
                return 0;
            }
        }
        else
        {
            \Session::flash('alert','danger');
            \Session::flash('message','Provider not found.');
            return 0;
        }
    }

    public function verify_report_status($id,$status)
    {
        $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($id);
        if(!is_null($user))
        {
            $user->report_status = $status;
            $user->save();
            $msg = $user->report_status == 'block' ? 'success' : 'danger';
            \Session::flash('alert',$msg);
            \Session::flash('message',$user->name.' user status has been changed successfully.');
            if($user->report_status == 'block')
            {
                $notification =
                [
                    "title" => "Blocked Customer Profile",
                    "message"=> "Your profile has been blocked.",
                ];
                $data = ['type'=>'customer_profile'];
                //PushNotification::send($user,$notification,$data);
                return 1;
            }
            else
            {
                $notification =
                [
                    "title" => "Unblock Customer Profile",
                    "message"=> "Your profile has been unblock.",
                ];
                $data = ['type'=>'customer_profile'];
                //PushNotification::send($user,$notification,$data);
                return 0;
            }
        }
        else
        {
            \Session::flash('alert','danger');
            \Session::flash('message','Provider not found.');
            return 0;
        }
    }

    public function customer(UsersDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index');
    }

    public function customer_profile($id)
    {
        $customer = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($id);
        if(!is_null($customer) && !is_null($customer->image)){
            $url = \Storage::url($customer->image);
            $customer->image =  asset($url);
        }
        else{
            $customer->image = asset('empty.jpg');
        }
        return view('admin.users.view',compact('customer'));
    }

    public function customer_destroy($id)
    {
        $customer = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($id);
        if(!is_null($customer)){
            if((!is_null($customer->image)) && \Storage::exists($customer->image))
            {
                \Storage::delete($customer->image);
            }
            $customer->delete();
            return redirect()->route('customer')->with(['alert'=>'success','message'=>'Customer has been successfully removed from the table']);
        }
        else{
            return redirect()->route('customer')->with(['alert'=>'danger','message'=>'Customer not found']);
        }
    }

    public function page_content(Request $request,$page_name)
    {
        if($page_name == "about-us")
        {
            if ($request->isMethod('post'))
            {
                $content = Page::updateOrCreate(['page_name'=>'about-us'],$request->all());
                return redirect()->route('page.create',$page_name)->with(['alert'=>'success','message'=>'Content has been created.']);
            }
            else
            {
                $content = Page::where('page_name','about-us')->first();
                return view('admin.pages.create',compact('content'));
            }
        }
        else if($page_name == "term-of-condition")
        {
            if ($request->isMethod('post'))
            {
                $content = Page::updateOrCreate(['page_name'=>'term-of-condition'],$request->all());
                return redirect()->route('page.create',$page_name)->with(['alert'=>'success','message'=>'Content has been created.']);
            }
            else
            {
                $content = Page::where('page_name','term-of-condition')->first();
                return view('admin.pages.create',compact('content'));
            }
        }
        else if($page_name == "privacy-policy")
        {
            if ($request->isMethod('post'))
            {
                $content = Page::updateOrCreate(['page_name'=>'privacy-policy'],$request->all());
                return redirect()->route('page.create',$page_name)->with(['alert'=>'success','message'=>'Content has been created.']);
            }
            else
            {
                $content = Page::where('page_name','privacy-policy')->first();
                return view('admin.pages.create',compact('content'));
            }
        }
        else
        {
            abort(404);
        }

    }

    public function message(Request $request, MessageDataTable $dataTable,$id=null)
    {
        if($request->isMethod('get')){
            if(!is_null($id)){
                $message = ContactUs::find($id);
                return view('admin.messages.view',compact('message'));
            }
            else{
                return $dataTable->render('admin.messages.index');
            }
        }
        else if($request->isMethod('delete')){
            $message = ContactUs::find($id);
            $message->delete();
            return redirect()->route('message')->with(['alert'=>'success','message'=>'Message has been removed.']);
        }
        else{
            about(404);
        }
    }
}
