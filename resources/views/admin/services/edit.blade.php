<x-app-layout>
  <x-slot name="header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-pencil-square"></i> </span>
        {{ __('Edit Service') }}
    </h3>
    <x-slot name="breadcrumb">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{route('service.index')}}">Service</a></li>
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
              <img src="{{$service->imageLink}}" class="rounded mx-auto d-block" alt="">
            </div>
            <form class="forms-sample" method="post" role="form" data-toggle="validator" action="{{route('service.update',$service->id)}}" enctype= multipart/form-data>
              @csrf
              {{ method_field('PUT') }}
              <div class="form-group">
                <label for="service_name">Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" pattern="^[a-zA-Z][a-zA-Z ]+[a-zA-Z]$" name="name" placeholder="Enter Category Name" value="{{old('name',$service->name)}}" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
              </div>
              <div class="form-group">
                <label for="description">Description(optional)</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Enter Description" value="{{old('description',$service->description)}}">
              </div>
              <div class="form-group">
                <label for="description">Instruction</label>
                <input type="text" class="form-control" id="instruction" name="instruction" placeholder="Enter Instruction" value="{{old('instruction',$service->instruction)}}">
              </div>
                <div class="row">
                    @foreach($service->service_prices as $value)
                        <div class="form-group col-md-6">
                            @if($value->shift == 1)
                            <label for="price">Overtime Price Per Hour<span class="text-danger">*</span></label>
                            @else
                            <label for="price">{{ $value->shift }} Hours Price<span class="text-danger">*</span></label>
                            @endif
                            <input type="hidden" value="{{ $value->shift }}" name="shift[]">
                            <input type="text" class="form-control" id="price" pattern="^\d{0,8}(\.\d{1,4})?$" name="price[]" placeholder="Enter Service Price" value="{{$value->price}}" required>
                        </div>
                    @endforeach
                </div>
              <div class="form-group">
                <label for="image">Image<span class="text-danger">*</span></label>
                <input type="file" class="form-control" placeholder="Upload Image" name="image" accept="image/*">
              </div>
              <!-- <div class="form-group">
                <label for="position">Position</label>
                <input type="number" name="position"class="form-control" id="position" placeholder="Enter Position" value="{{old('position',$service->position)}}" min="1" required>
              </div> -->
              <div class="form-group">
                <label for="status">Status<span class="text-danger">*</span></label>
                <select class="form-control" id="status" name="status">
                  <option value="1" {{$service->status == 1 ? 'selected' : '' }}>Active</option>
                  <option value="0" {{$service->status == 0 ? 'selected' : '' }}>Inactive</option>
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
