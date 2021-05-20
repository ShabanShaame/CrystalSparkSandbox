<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 28.10.20
 * Time: 12:46
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//header('Access-Control-Allow-Origin: *');
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

Route::match(['get','post'],'getViews', 'Alexandria\MetaController@getViews')
    ->middleware('cors','csaccount');

Route::match(['get','post'],'cookie',function (Request $request) {

   return response()->json("hello")->withCookie(cookie('nananna','asdf',0,'/',null,false,false));


});


Route::match(['get','post'],'mylogs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('csaccount');

Route::match(['get','post'],'gossipTest', 'Alexandria\MetaController@listenGossip')->middleware('cors');;
Route::match(['post'],'gossip', 'Alexandria\MetaController@listenGossipAuth')->middleware('cors');;
Route::match(['get'],'gossip', 'Alexandria\MetaController@exposeGossip')->middleware('cors');;
Route::match(['get','post'],'casaBuilder', function (){

    return view('casaBuilder/casaBuilder', []);
})->middleware('csaccount');
