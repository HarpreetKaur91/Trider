<x-app-layout>
    
    <x-slot name="header"> 
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-eye-fill"></i> </span> 
            {{ __($customer->name.' Profile') }}
        </h3>
        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="{{route('customer')}}">Customers</a></li>
                  <li class="breadcrumb-item active" aria-current="page">{{ __($customer->name.' Profile') }}</li>
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
                                    <a href="{{$customer->image}}"><img src="{{$customer->image}}" alt=""></a>
                                    <h1>{{$customer->name}}</h1>
                                    <p>{{$customer->email}}</p>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-info btn-sm" id="userBlock" data-url="{{route('verifyReportStatus',[$customer->id,'block'])}}">Block</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="userUnblock" data-url="{{route('verifyReportStatus',[$customer->id,'unblock'])}}">Unblock</button>
                                </div>

                                <!-- <ul class="nav flex-column">
                                  <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="#"><i class="mdi mdi-file-check"></i>Active</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="mdi mdi-file-check"></i>Link</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="#"><i class="mdi mdi-file-check"></i>Link</a>
                                  </li>
                                </ul> -->
                            </div>
                        </div>
                        <div class="profile-info col-md-9">
                            <div class="panel">
                                <!-- <div class="bio-graph-heading"><h1>{{$customer->name}} Bio Graph</h1></div> -->
                                <div class="panel-body bio-graph-info">
                                    <h1>Basic Information</h1>
                                    <div class="row">
                                        <div class="bio-row">
                                            <p><span>Name </span>: {{$customer->name}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Email </span>: {{$customer->email}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Phone Number</span>: {{$customer->phone_number ?? "--"}}</p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Report Status</span>: <span class="text-capitalize">{{is_null($customer->report_status) ? "N/A" : $customer->report_status}}</span></p>
                                        </div>
                                        <div class="bio-row">
                                            <p><span>Joined At</span>: {{date('M d,Y',strtotime($customer->created_at))}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row mt-5">
                        <table class="table table-bordered profile_table">
                            <thead>
                                <tr>
                                    <th colspan="5" class="text-center">Recent Service's</th>
                                </tr>
                            </thead>
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Provider </th>
                                    <th> Service </th>
                                    <th> Location </th>
                                    <th> Date </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td> 1 </td>
                                    <td> John </td>
                                    <td> Cleaning </td>
                                    <td> Chandigarh </td>
                                    <td> 05 Apr,2023 </td>
                                </tr>
                            </tbody>
                        </table>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    <script type="module">
        $('#userBlock').on('click',function(){
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
        $('#userUnblock').on('click',function(){
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