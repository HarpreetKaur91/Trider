<x-app-layout>
    
    <x-slot name="header"> 
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-eye-fill"></i> </span> 
            {{ __('View Message') }}
        </h3>
        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="{{route('message')}}">Message/Report</a></li>
                  <li class="breadcrumb-item active" aria-current="page">{{ __('View Message') }}</li>
                </ol>
              </nav>
        </x-slot>
    </x-slot>
    
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">{{$message->name}}</h5>
                <h6 class="card-subtitle mb-2 text-muted">Email :- {{$message->email}}</h6>
                <h6 class="card-subtitle mb-2 text-muted">Created At :- {{date('M d,Y',strtotime($message->created_at))}}</h6>
                <p class="card-text">Message :- {{$message->message}}</p>
              </div>
            </div>
        </div>
    </div>
</x-app-layout>