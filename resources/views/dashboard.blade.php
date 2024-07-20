<x-app-layout>

    <x-slot name="header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-house-fill"></i> </span> {{ __('Dashboard') }}
        </h3>
    </x-slot>

    <div class="row">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-danger card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Services <i class="bi bi-grid-fill mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{$data['total_category']}}</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('service.index')}}'">View All</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-primary card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Companies<i class="bi bi-person-check-fill mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{$data['total_companies']}}</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('customer')}}'">View All</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-info card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Customers<i class="bi bi-person-fill mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{$data['total_customers']}}</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('customer')}}'">View All</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-success card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Comapny Employees <i class="bi bi-people-fill mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{$data['total_employees']}}</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('employee')}}'">View All</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-warning card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Freelancers <i class="bi bi-people mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">{{$data['total_freelancers']}}</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('freelancer')}}'">View All</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-secondary card-img-holder text-white">
                <div class="card-body">
                    <img src="{{asset('assets/images/dashboard/circle.svg')}}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Total Bookings <i class="bi bi-x-diamond-fill mdi-24px float-right"></i></h4>
                    <h2 class="mb-5">0</h2>
                    <h6 class="card-text cursor-pointer" onclick="window.location.href='{{route('freelancer')}}'">View All</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Customers</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Name </th>
                                    <th> Phone No </th>
                                    <th> Status </th>
                                    <th> Created AT </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data['recent_customers'])>0)
                                @foreach($data['recent_customers'] as $customer)
                                <tr>
                                    <td>
                                        @if(!is_null($customer->image))
                                        <img src="{{asset('storage/'.$customer->image)}}" class="me-2" alt="image">
                                        @else
                                        <img src="{{asset('empty.jpg')}}" class="me-2" alt="image">
                                        @endif
                                    </td>
                                    <td class="text-capitalize">
                                        {{$customer->name}}
                                    </td>
                                    <td> {{$customer->phone_number ?? '--' }} </td>
                                    <td> <label class="badge badge-gradient-{{ ($customer->account_status == 1) ? 'success' : 'danger' }}">{{ ($customer->account_status == 1) ? 'Active' : 'Inactive' }}</label> </td>
                                    <td> {{$customer->created_at->diffForHumans()}} </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="5" class="text-center">There is no record.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Companies</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Name </th>
                                    <th> Phone No </th>
                                    <th> Status </th>
                                    <th> Created AT </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data['recent_companies'])>0)
                                @foreach($data['recent_companies'] as $company)
                                <tr>
                                    <td>
                                        @if(!is_null($company->image))
                                        <img src="{{asset('storage/'.$company->image)}}" class="me-2" alt="image">
                                        @else
                                        <img src="{{asset('empty.jpg')}}" class="me-2" alt="image">
                                        @endif
                                    </td>
                                    <td class="text-capitalize">
                                        {{$company->name}}
                                    </td>
                                    <td> {{$company->phone_number ?? '--' }} </td>
                                    <td> <label class="badge badge-gradient-{{ ($company->account_status == 1) ? 'success' : 'danger' }}">{{ ($company->account_status == 1) ? 'Active' : 'Inactive' }}</label> </td>
                                    <td> {{$company->created_at->diffForHumans()}} </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="5" class="text-center">There is no record.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Providers</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Name </th>
                                    <th> Phone No </th>
                                    <th> Provider Type </th>
                                    <th> Status </th>
                                    <th> Created AT </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data['recent_providers'])>0)
                                @foreach($data['recent_providers'] as $provider)
                                <tr>
                                    <td>
                                        @if(!is_null($provider->image))
                                        <img src="{{asset('storage/'.$provider->image)}}" class="me-2" alt="image">
                                        @else
                                        <img src="{{asset('empty.jpg')}}" class="me-2" alt="image">
                                        @endif
                                    </td>
                                    <td class="text-capitalize">
                                        {{$provider->name}}
                                    </td>
                                    <td> {{$provider->phone_number ?? '--' }} </td>
                                    <td class="text-capitalize"> {{ $provider->role }} </td>
                                    <td>
                                        @if(is_null($provider->account_status))
                                            <label class="badge badge-gradient-warning">Need for approval</label>
                                        @elseif($provider->account_status == 1)
                                            <label class="badge badge-gradient-success"> Approved</label>
                                        @elseif($provider->account_status == 0)
                                            <label class="badge badge-gradient-danger">Declined</label>
                                        @endif
                                    </td>
                                    <td> {{$provider->created_at->diffForHumans()}} </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center">There is no record.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
