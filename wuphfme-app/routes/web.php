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

Route::get('/', function () {
	if(!Auth::guest()) return view('home');
    return view('splash');
});
Route::get('login', function () {
    return view('login');
});
Route::get('register', function () {
    return view('register');
});
Route::get('manage', function () {
    return view('manage');
});

Route::post('contacts/{user}/{method}/{key}', "UserContactController@set");
Route::get('contacts/{user}/{method}/{key}', "UserContactController@get");// Authentication Routes...

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

/* Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');*/

Route::get('home', 'HomeController@index');

Route::post('wuphf', 'WuphfController@wuphf');
