<x-app-layout>

    <x-slot name="header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="bi bi-people-fill"></i> </span>
            {{ __('Companies') }}
        </h3>
        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Companies</li>
                </ol>
              </nav>
        </x-slot>
    </x-slot>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">Manage Companies</div>
                <div class="card-body">
                    @include('components.alert')
                    {!! $dataTable->table(['class' => 'table table-striped'], true) !!}
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-app-layout>
