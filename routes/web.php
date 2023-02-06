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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')
->name('home');

Route::get('/thankYouPage/{slug}', 'HomeController@thankYouPage')->middleware('auth')
->name('thankYouPage');

Route::get('/createATestUser', 'HomeController@createATestUser')
->name('createATestUser');

Route::get('/truncate', 'HomeController@truncate')
->name('truncate');

Route::get('/subscriptions', 'SubscriptionController@subscriptionScreen')->middleware('auth')
->name('subscriptions');

Route::post('/cancelCurrentSubscription', 'SubscriptionController@cancelCurrentSubscription')->middleware('auth')
->name('cancelCurrentSubscription');

Route::get('/subscriptionsHistory', 'SubscriptionController@subscriptionHistoryScreen')->middleware('auth')
->name('subscriptionHistory');

Route::get('/paymentMethods', 'PaymentMethodController@paymentMethodScreen')->middleware('auth')
->name('paymentMethods');

Route::get('/payment/{slug}', 'SubscriptionController@paymentScreen')->middleware(['auth'])
->name('payment');

Route::post('/doPayment', 'SubscriptionController@doPayment')->middleware('auth')
->name('doPayment');

Route::post('/addCard', 'PaymentMethodController@addCard')->middleware('auth')
->name('addCard');

Route::post('/makeCardAsDefault', 'PaymentMethodController@makeCardAsDefault')->middleware('auth')
->name('makeCardAsDefault');

Route::post('/makeCardAsBackUpPayment', 'PaymentMethodController@makeCardAsBackUpPayment')->middleware('auth')
->name('makeCardAsBackUpPayment');

