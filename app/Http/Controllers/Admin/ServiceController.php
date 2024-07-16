<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ServiceDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ServiceDataTable $dataTable)
    {
        return $dataTable->render('admin.services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::where('parent_id','=',0)->orderBy('id','desc')->get();
        return view('admin.services.create',compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request,['name'=>'unique:services']);
        $service = new Service();
        $service->parent_id = 0;
        $service->name = ucwords(strtolower($request->name));
        if($request->hasFile('image')){
            $service->image = $request->file('image')->store('public/serviceImage');
        }
        $service->position = 1;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->status = $request->status;
        if($service->save()){
            return redirect()->route('service.index')->with(['alert'=>'success','message'=>'New Service has been created.']);
        }
        else{
            return redirect()->route('service.index')->with(['alert'=>'danger','message'=>'New Service has not been created.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $services = Service::where('parent_id','=',0)->orderBy('id','desc')->get();
        $service = Service::findOrFail($id);
        if(!is_null($service->image)){
            $url = \Storage::url($service->image);
            $service->imageLink =  asset($url);
        }
        else{
            $service->imageLink = asset('empty.jpg');
        }
        return view('admin.services.edit',compact('service','services'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $services = Service::where('parent_id','=',0)->orderBy('id','desc')->get();
        $service = Service::findOrFail($id);
        if(!is_null($service->image)){
            $url = \Storage::url($service->image);
            $service->imageLink =  asset($url);
        }
        else{
            $service->imageLink = asset('empty.jpg');
        }
        return view('admin.services.edit',compact('service','services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request,['name'=>'unique:services,name,'.$id]);
        $service = Service::findOrFail($id);
        $service->parent_id = 0;
        $service->name = ucwords(strtolower($request->name));
        if($request->hasFile('image')){
            if((!is_null($service->image)) && \Storage::exists($service->image))
            {
                \Storage::delete($service->image);
            }
            $service->image = $request->file('image')->store('public/serviceImage');
        }
        $service->position = 1;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->status = $request->status;
        if($service->save()){
            return redirect()->route('service.index')->with(['alert'=>'success','message'=>'Service has been updated.']);
        }
        else{
            return redirect()->route('service.index')->with(['alert'=>'danger','message'=>'Service has not been updated.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::find($id);
        if(!is_null($service)):
            if((!is_null($service->image)) && \Storage::exists($service->image))
            {
                \Storage::delete($service->image);
            }
            $service->delete();
            return redirect()->route('service.index')->with(['alert'=>'success','message'=>'Service has been removed.']);
        endif;
    }
}
