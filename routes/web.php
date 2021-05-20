<?php

use Illuminate\Support\Facades\Route;

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




Route::middleware('cors')->get('admin/dbview/{viewName}', 'AdminController@dbView');
Route::middleware('cors')->get('admin/dbview/count/{viewName}', 'AdminController@countDbView');
Route::middleware('cors')->get('admin/ajax/{viewName}', 'AdminController@tableAjax');
Route::middleware('cors')->get('admin/dbview/headers/{viewName}', 'AdminController@getheaders')->middleware('csaccount');;

Route::middleware('cors')->get('alexandria/sog/datatable/{contractName}', 'SogController@getBindDatatable');





Route::get('/', function () {



    return view('welcome');



});

Route::middleware('cors')->post('/', function () {



    return view('welcome');



});


use SandraCore\ForeignEntityAdapter ;

