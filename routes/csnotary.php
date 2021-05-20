<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 28.10.20
 * Time: 12:46
 */

use Illuminate\Http\Request;



Route::middleware('cors')->get("/",'CSNotary\CSNotaryController@helloNotary');
Route::middleware('cors')->post("/createWallet/",'CSNotary\CSNotaryController@createWallet');
Route::middleware('cors')->post("/createSend/",'CSNotary\CSNotaryController@createSend');
Route::middleware('cors')->post("/broadcast/",'CSNotary\CSNotaryController@broadcast');
Route::middleware('cors')->get("/contract/read/{contractId}",'CSNotary\CSNotaryController@getContractData');