<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\FaqDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FaqDataTable $dataTable)
    {
        return $dataTable->render('admin.faqs.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $faqs = Faq::orderBy('id','desc')->get();
        return view('admin.faqs.create',compact('faqs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request,['question'=>'required','answer'=>'required']);
        $faq = new Faq();
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->status = $request->status;
        if($faq->save()){
            return redirect()->route('faq.index')->with(['alert'=>'success','message'=>'New Faq has been created.']);
        }
        else{
            return redirect()->route('faq.index')->with(['alert'=>'danger','message'=>'New Faq has not been created.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $faq = Faq::findOrFail($id);
        return view('admin.faqs.edit',compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $faq = Faq::findOrFail($id);
        return view('admin.faqs.edit',compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request,['question'=>'required','answer'=>'required']);
        $faq = Faq::findOrFail($id);
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->status = $request->status;
        if($faq->save()){
            return redirect()->route('faq.index')->with(['alert'=>'success','message'=>'Faq has been updated.']);
        }
        else{
            return redirect()->route('faq.index')->with(['alert'=>'danger','message'=>'Faq has not been updated.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $faq = Faq::find($id);
        if(!is_null($faq)):
            $faq->delete();
            return redirect()->route('faq.index')->with(['alert'=>'success','message'=>'Faq has been removed.']);
        endif;
    }
}
