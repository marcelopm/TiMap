<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

// Map related routes, uses user auth middleware
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'Map\MapController@index');
    Route::get('/map/image/search', 'Map\ImageController@search');
    Route::get('/map/image/analyse', 'Map\ImageController@analyse');
    Route::get('/map/analyser/weight/{operation}', 'Map\AnalyserController@weight');
});

// Authentication Routes, login related ones only @see Auth::routes()
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->get('logout', 'Auth\LoginController@logout')->name('logout');