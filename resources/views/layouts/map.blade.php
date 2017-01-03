<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!--materialize-->
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="{{ asset('css/materialize.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ elixir('css/map/app.css') }}">
    </head>
    <body>
        @yield('content')

        <script src="{{ elixir('js/map/app.js') }}"></script>
        <script src="{{ asset('js/materialize.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js" async defer></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GMAP_KEY', '') }}&libraries=places&callback=map.init" async defer></script>
    </body>
</html>