<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Trider') }}</title>

        <!-- Fonts -->
        <!-- <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> -->

        <link rel="stylesheet" href="{{asset('assets/mdi/css/materialdesignicons.min.css')}}">

        <!-- Scripts -->
        @vite([
        'resources/css/app.css',
        'resources/sass/app.scss',
        'resources/js/app.js',
        'resources/css/style.css',
        'resources/css/vendors/css/vendor.bundle.base.css',
        'resources/js/jquery.cookie.js',
        'resources/js/off-canvas.js',
        'resources/js/hoverable-collapse.js',
        'resources/js/misc.js',
        ])
        <script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>
    </head>

    <body>

        <div class="container-scroller">
            @include('layouts.navigation')

            <!-- Page Content -->
            <div class="container-fluid page-body-wrapper">
                @include('layouts.sidebar')
                <div class="main-panel">
                    <div class="content-wrapper">
                        <!-- Page Heading -->
                        @if (isset($header))
                            <div class="page-header">
                                {{ $header }}

                                @if(isset($breadcrumb))
                                    <nav aria-label="breadcrumb">
                                        {{$breadcrumb}}
                                    </nav>
                                @endif
                            </div>
                        @endif
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @include('delete')
        @stack('scripts')
        <script>
            function deleteData(url)
            {
                console.log('url '+url);
                $("#deleteForm").attr('action', url);
                var modal = new bootstrap.Modal(document.getElementById('myModal'));
                modal.show();
            }
                const myModalEl = document.getElementById('myModal');
                myModalEl.addEventListener('hidden.bs.modal', event => {
                $("#deleteForm").attr('action', '');
            })
        </script>
    </body>
</html>
