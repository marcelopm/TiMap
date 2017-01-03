@extends('layouts.map')

@section('content')
<!--map-->
<span class="map-container">
    <div class="progress z-depth-1">
        <div class="indeterminate"></div>
    </div>

    <div id="map"></div>
</span>

<!--searchbox-->
<input id="searchbar" class="controls z-depth-1" type="text" placeholder="Search Box">

<!--image popup-->
<div class="container">
    <div id="modal" class="modal transparent">
        <div class="card">
            <!--image-->
            <div class="card-image">
                <div id="image"></div>
                <div id="overlay">
                    <div class="row">
                        <div class="col s6 center increase-weight btn transparent">
                            <span class="btn-floating white waves-effect waves-green">
                                <i class="material-icons green-text">done</i>
                            </span>
                        </div>
                        <div class="col s6 center decrease-weight btn transparent">
                            <span class="btn-floating white valign waves-effect waves-red">
                                <i class="material-icons red-text">close</i>
                            </span>
                        </div>
                    </div>
                </div>

                <!--tags-->
                <span class="card-chip right">
                    <div class="chip  blue lighten-1 z-depth-4 grey-text text-lighten-5"></div>
                </span>
            </div>

            <!--title, extra info button-->
            <div class="card-content">
                <span class="card-title">
                    <span class="image-title grey-text text-darken-4 left truncate"></span>
                    <a class="btn-floating right waves-effect waves-light red activator disabled">
                        <i class="material-icons">add</i>
                    </a>
                </span>
            </div>
            <div class="progress">
                <div class="indeterminate"></div>
            </div>

            <!--graph-->
            <div class="card-reveal">
                <span class="card-title">
                    <span class="image-title grey-text text-darken-4 left truncate"></span>
                    <i class="material-icons right">close</i>
                </span>
                <p>
                <div class="chart-holder"></div>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection