<x-app-layout>
    
    <x-slot name="header"> 
        <h3 class="page-title text-capitalize">
            <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-eye-fill"></i> </span> 
            {{ __($provider->name.' Profile') }}
        </h3>
        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="{{route('provider')}}">Providers</a></li>
                  <li class="breadcrumb-item active text-capitalize" aria-current="page">{{ __($provider->name.' Profile') }}</li>
                </ol>
              </nav>
        </x-slot>
    </x-slot>
    
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @include('components.alert')
                        <div class="profile-nav col-md-3">
                            <div class="panel">
                                <div class="user-heading round">
                                    <a href="{{$provider->image}}"><img src="{{$provider->image}}" alt=""></a>
                                    <h1 class="text-capitalize">{{$provider->name}}</h1>
                                    <p>{{$provider->email}}</p>
                                </div>
                                <ul class="nav flex-column">
                                    <li class="nav-item"><a class="nav-link" href="#">
                                        @php $rate = $provider->provider_reviews->avg('rating'); @endphp
                                        @if($rate)
                                            @for($i=1;$i<=$rate;$i++)
                                                <i class='bi bi-star-fill text-warning'></i>
                                            @endfor
                                        @else
                                            <i class='bi bi-star'></i><i class='bi bi-star'></i><i class='bi bi-star'></i><i class='bi bi-star'></i><i class='bi bi-star'></i>
                                        @endif
                                    </a></li>
                                    <li class="nav-item"><a class="nav-link" href="#">Total Review :- {{$provider->provider_reviews->count() }}</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><button type="button" class="btn btn-info btn-sm" id="providerApproved" title="Approve" data-url="{{route('verifyProviderStatus',[$provider->id,1])}}"><i class="bi bi-bag-check-fill"></i></button>&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-danger btn-sm" id="providerDeclined" title="Reject" data-url="{{route('verifyProviderStatus',[$provider->id,0])}}"><i class="bi bi-bag-x-fill"></i></button></a></li>
                                </ul>
                                <!-- <div class="text-center mt-3">
                                    <button type="button" class="btn btn-info btn-sm" id="providerApproved" data-url="{{route('verifyProviderStatus',[$provider->id,1])}}">Approve</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="providerDeclined" data-url="{{route('verifyProviderStatus',[$provider->id,0])}}">Reject</button>
                                </div> -->
                            </div>
                        </div>
                        <div class="profile-info col-md-9">
                            <div class="panel">
                                <!-- <div class="bio-graph-heading"><img src="{{$provider->image}}" alt=""></div> -->
                                <div class="panel-body bio-graph-info">
                                    <h1>Basic Information</h1>
                                    <div class="row">
                                        <div class="bio-row text-capitalize">
                                            <p><span>Name </span>: {{$provider->name}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Email </span>: {{$provider->email}}</p>
                                        </div> 
                                        <div class="bio-row">
                                            <p><span>Phone Number</span>: {{$provider->phone_number ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Business Logo </span>: 
                                                @if(!is_null($provider->provider_business_profile->business_image))
                                                <a class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" target="_blank" href="{{asset($provider->provider_business_profile->business_image)}}">View Logo</a>
                                                @else
                                                N/A
                                                @endif
                                            </p>
                                        </div>
                                        <div class="bio-row text-capitalize">
                                            <p><span>Business Name </span>: {{$provider->provider_business_profile->business_name ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Business Phone No </span>: {{$provider->provider_business_profile->business_phone_no ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Year of experience </span>: {{$provider->provider_business_profile->year_of_exp ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row text-capitalize">
                                            <p><span>Complete Address</span>: {{$provider->provider_address->complete_address ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row text-capitalize">
                                            <p><span>Landmark</span>: {{$provider->provider_address->landmark ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row text-capitalize">
                                            <p><span>State</span>: {{$provider->provider_address->state ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row text-capitalize">
                                            <p><span>City</span>: {{$provider->provider_address->city ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Pincode</span>: {{$provider->provider_address->pincode ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Front Aadhar Card </span>: {{$provider->provider_business_profile->front_aadhaar_card ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Back Aadhar Card </span>: {{$provider->provider_business_profile->back_aadhaar_card ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Joined At</span>: {{date('M d,Y',strtotime($provider->created_at))}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Available Status </span>: {{$provider->status == 1 ? 'Online' : 'Offline'}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Account Status </span>: 
                                                @if(is_null($provider->account_status))
                                                    <span class="text-info"> Pending </span>
                                                @elseif($provider->account_status == 1)
                                                   <span class="text-success"> Approved </span>
                                                @else
                                                    <span class="text-danger"> Rejected </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <table class="table table-bordered profile_table">
                            <thead>
                                <tr>
                                    <th colspan="5" class="text-center">Provider Service's</th>
                                </tr>
                            </thead>
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Service </th>
                                    <th> Bio </th>
                                    <th> Created At </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($provider->provider_services)>0)
                                @foreach($provider->provider_services as $service)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{$service->service->name}} </td>
                                    <td> {{$service->bio}} </td>
                                    <td> {{date('M Y,d',strtotime($service->created_at))}} </td>
                                </tr>
                                @endforeach
                                @else
                                <tr class="text-center"><td colspan="4">No Service</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    <script type="module">
        $('#providerApproved').on('click',function(){
            var url = $(this).data("url")
            $.ajax({
                type:'get',
                url:url,
                success:function(data){
                    console.log(data);
                    location.reload(true);
                }
            })
        })
        $('#providerDeclined').on('click',function(){
            var url = $(this).data("url")
            $.ajax({
                type:'get',
                url:url,
                success:function(data){
                    console.log(data);
                    location.reload(true);
                }
            })
        })
    </script>
</x-app-layout>