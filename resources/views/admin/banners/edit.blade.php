<x-app-layout>
  <x-slot name="header"> 
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-pencil-square"></i> </span> 
        {{ __('Edit Banner') }}
    </h3>
    <x-slot name="breadcrumb">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{route('banner.index')}}">Banner</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
        </nav>
    </x-slot>
  </x-slot>
    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="text-center">
              <img src="{{$banner->imageLink}}" class="rounded mx-auto d-block" alt="">
            </div>
            <form class="forms-sample" method="post" role="form" data-toggle="validator" action="{{route('banner.update',$banner->id)}}" enctype= multipart/form-data>
              @csrf
              {{ method_field('PUT') }}
              <div class="form-group">
                <label for="banner_type">Banner Type<span class="text-danger">*</span></label>
                <select class="form-control" id="banner_type" name="banner_type">
                  <option value="provider" {{$banner->banner_type == 'provider' ? 'selected' : '' }}>Provider</option>
                  <option value="user" {{$banner->banner_type == 'user' ? 'selected' : '' }}>Customer</option>
                </select>
              </div>
              <div class="form-group">
                <label for="image">Image<span class="text-danger">*</span></label>
                <input type="file" class="form-control" placeholder="Upload Image" name="image" accept="image/*">
              </div>
              <div class="form-group">
                <label for="position">Position<span class="text-danger">*</span></label>
                <input type="number" name="position"class="form-control" id="position" placeholder="Enter Position" value="{{old('position',$banner->position)}}" min="1" required>
              </div>
              <div class="form-group">
                <label for="status">Status<span class="text-danger">*</span></label>
                <select class="form-control" id="status" name="status">
                  <option value="1" {{$banner->status == 1 ? 'selected' : '' }}>Active</option>
                  <option value="0" {{$banner->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>

</x-app-layout>