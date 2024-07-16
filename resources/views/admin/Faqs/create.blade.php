<x-app-layout>
    <style> 
textarea {
  height: 500px;
  box-sizing: border-box;
  resize: none;
}
</style>
  <x-slot name="header"> 
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-plus-lg"></i> </span> 
        {{ __("Add New FAQ's") }}
    </h3>
    <x-slot name="breadcrumb">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{route('faq.index')}}">FAQ's</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
        </nav>
    </x-slot>
  </x-slot>
    
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <form class="forms-sample" method="post" role="form" data-toggle="validator" action="{{route('faq.store')}}" enctype= multipart/form-data>
              @csrf
              <div class="form-group">
                <label for="question">Question<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="question" name="question" placeholder="Add Question" value="{{old('question')}}" required>
                <x-input-error :messages="$errors->get('question')" class="mt-2" />
              </div>
              <div class="form-group">
                <label for="answer">Answer<span class="text-danger">*</span></label>
                <textarea class="form-control" name="answer" id="answer" placeholder="Add Content">{{old('answer')}}</textarea>
                <x-input-error :messages="$errors->get('answer')" class="mt-2" />
              </div>
              <div class="form-group">
                <label for="status">Status<span class="text-danger">*</span></label>
                <select class="form-control" id="status" name="status">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @stack('scripts')
    <script type="module">
      ClassicEditor.create( document.querySelector( '#answer' ) ).catch( error => { console.error( error );} );
    </script>
</x-app-layout>