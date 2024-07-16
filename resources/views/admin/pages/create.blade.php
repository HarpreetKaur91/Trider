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
        <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-book"></i> </span>
        {{ __('Add Content') }}
    </h3>
    <x-slot name="breadcrumb">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
        </nav>
    </x-slot>
  </x-slot>
    
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            @php 
            $url = request()->segment(count(request()->segments()));
            @endphp
            <form class="forms-sample" method="post" role="form" data-toggle="validator" action="{{route('page.create',$url)}}" enctype= multipart/form-data>
              @csrf
              <div class="form-group">
                <label for="page_header">Content Header<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="page_header" pattern="^[a-zA-Z][a-zA-Z ]+[a-zA-Z]$" name="page_header" placeholder="Content Header" value="{{old('page_header',$content->page_header ?? '')}}" required>
                <x-input-error :messages="$errors->get('page_header')" class="mt-2" />
              </div>
              <div class="form-group">
                <label for="content">Add Content<span class="text-danger">*</span></label>
                <textarea class="form-control" name="content" id="content" placeholder="Add Content">{{old('content',$content->content ?? '')}}</textarea>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
              </div>
              <!-- <div class="form-group">
                <label for="position">Position</label>
                <input type="number" name="position"class="form-control" id="position" placeholder="Position" min="1">
              </div> -->
              <div class="form-group">
                <label for="status">Status<span class="text-danger">*</span></label>
                <select class="form-control" id="status" name="status">
                  <option value="1" {{!is_null($content) ? $content->status == 1 ? 'selected' : '' : ''}}>Active</option>
                  <option value="0" {{!is_null($content) ? $content->status == 0 ? 'selected' : '' : ''}}>Inactive</option>
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
      ClassicEditor.create( document.querySelector( '#content' ) ).catch( error => { console.error( error );} );
    </script>
</x-app-layout>