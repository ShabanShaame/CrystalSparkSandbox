<?php

use App\Sandra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

app()->singleton('Sandra',function(){
    return new Sandra();
});

app()->singleton('SystemConfig',function(){
    return new \App\SystemConfig();
});

$basePath = '/v1';
$wipPath = '/v01';
$sogPath = '/chagall';
$firstOasis = '/firstOasis';
$CSNotaryPath = '/csnotary/';


//dd("$basePath/getCollections/{filter?}");

//test


//dd(Config('database.connections.mysql.username'));



$sandra = app('Sandra')->getSandra();

CsCannon\SandraManager::setSandra($sandra);

Route::domain('metadata.spellsofgenesis.com')->group(function () {
    Route::get('/', 'CollectionController@getAll');
});





Route::middleware('cors')->get("testgame/people",'TestGameController@getPeople');





Route::middleware('cors')->get("$basePath/search/{txString}",'SearchController@search');

//ho

Route::middleware('cors')->get("$basePath/transactions/sync",'TransactionsController@syncLocal');
Route::middleware('cors')->get("$basePath/transactions/opensea",'TransactionsController@syncOpensea');
Route::middleware('cors')->get("$basePath/transactions/transfers",'TransactionsController@getTransfer');
Route::middleware('cors')->get("$basePath/transactions/transfers/{address}",'TransactionsController@getTransfer');
Route::middleware('cors')->get("$basePath/tx/{txString}",'TransactionsController@getByTx');


Route::middleware('cors')->get("$basePath/events/{address}",'TransactionsController@getEvents');
Route::middleware('cors')->get("$basePath/eventsRaw/{address}",'TransactionsController@getEventsRaw');
set_time_limit(10000);
Route::middleware('cors')->post("$basePath/events/import",'TransactionsController@import');
Route::middleware('cors')->post("$basePath/events/{blockchain}/import",'TransactionsController@importEventsByBlockchain');
Route::middleware('cors')->get("$basePath/events/",'TransactionsController@getEvents');
Route::middleware('cors')->get("$basePath/eventsRaw/",'TransactionsController@getEventsRaw');








Route::middleware('cors')->get("$basePath/balances/{address}",'BalanceController@getBalance');
Route::middleware('cors')->get("$basePath/tokens/balances/{address}",'BalanceController@getTokenBalance');



Route::middleware('cors')->get("$basePath/collections", 'CollectionController@getAll');
Route::middleware('cors')->get("$basePath/collections/carousels",'CollectionController@getCarousels');
Route::middleware('cors')->post("$basePath/collections/{collectionId}/sticker/{stickerId}",'CollectionController@stickCollection');
Route::middleware('cors')->get("$basePath/collections/carousels/{stickerId}",'CollectionController@getCarousels');
Route::middleware('cors')->get("$basePath/collections/probe/{collectionId}/{contract}/{tokenId}",'CollectionController@probeErc721');
Route::middleware('cors')->get("$basePath/collections/stats/{collectionId}",'CollectionController@getStats');

//contract
Route::middleware('cors')->post("$basePath/contract/{contractId}", 'ContractController@trackContract');
Route::middleware('cors')->get("$basePath/contract/{contractId}", 'ContractController@getContractInfo');

Route::middleware('cors')->post("$basePath/config/{blockchainName}/requireExplicitTrack", 'ContractController@requireExplicitTrackForChain');
Route::middleware('cors')->get("$basePath/config/getActiveBlockchains", 'AdminController@getActiveBlockchains');
Route::middleware('cors')->post("$basePath/config/{blockchainName}/setActive", 'AdminController@setActiveBlockchain');



Route::middleware('cors')->put("$basePath/{collectionId}/image/solver",'MetadataController@setMetadataSolver');
Route::middleware('cors')->get("$basePath/{collectionId}/image/solver",'MetadataController@getImageSolver');
Route::middleware('cors','throttle:1000,1')->get("$basePath/{collectionId}/image/{tokenId}",'MetadataController@getImage');
Route::middleware('cors','throttle:1000,1')->get("$basePath/{collectionId}/image",'MetadataController@getImage');
Route::middleware('cors')->get("$basePath/collections",'CollectionController@getAll') ;



//SOG Chagall

Route::middleware('cors')->get("$sogPath/{bundle}/cards",'SogController@getCards');


//first Oasis
Route::middleware('cors')->get("firstOasis/getDeposit/{chain}/{jwt}",'FirstOasisController@getDeposit');
Route::middleware('cors')->get("firstOasis/job/",'FirstOasisController@job');
Route::middleware('cors')->get("firstOasis/getManagedAddress/{qualifierAddress}/",'FirstOasisController@getManagedAddress');
Route::middleware('cors')->post("sog/blockchainize",'FirstOasisController@blockchainize');
Route::middleware('cors')->post("firstOasis/withdraw",'FirstOasisController@withdraw');
Route::middleware('cors')->post("$basePath/firstOasis/withdraw",'FirstOasisController@withdrawDispatch');
Route::middleware('cors')->post("$basePath/firstOasis/withdrawVerify",'FirstOasisController@withdrawVerify');
Route::middleware('cors')->post("firstOasis/withdrawVerify",'FirstOasisController@withdrawVerify');
Route::middleware('cors')->get("firstOasis/withdrawVerify",'FirstOasisController@withdrawVerify');

//jetski






//temporary

Route::middleware('cors')->get("config/", function (Request $request) {

    echo "hellowalid";




});



Route::middleware('cors')->get("$basePath/collections/{identifier}", 'CollectionController@getSingle');


Route::middleware('cors')->get("$basePath/getCollectionETH/{collectionIdentifier}", function (Request $request,$identifier) {



    $sandra = new SandraCore\System(null,true);
    $address = '0xdceaf1652a131f32a821468dc03a92df0edd86ea';
    $address = $identifier;



    $url="https://api.opensea.io/api/v1/assets/?order_by=current_price&order_direction=asc&asset_contract_address=$address";

    $assetVocabulary = array('image_url'=>'image',
        'assetName'=>'assetName',
        'name'=>'name',



    );

    $foreignAdapter = new ForeignEntityAdapter($url,'assets',$sandra);
    $foreignAdapter->populate();
    $todisplay = array_keys($assetVocabulary);



    //$factory->getAllWith('envCode',$identifier);
    //$hello = $foreignAdapter->getDisplay('array',$todisplay,$assetVocabulary);

    //I'm  tired so I manualy parse the array because the displayer downs't work
    foreach ($foreignAdapter->return2dArray() as $value){

        $unit['image'] = $value['f:image_url'];
        $displayArray[] = $unit ;


    }

    // return response()->json($foreignAdapter->return2dArray(), 200);
    return response()->json($displayArray, 200);




});



Route::middleware('cors')->get('/getCollection/setCPLearner', function (Request $request) {



    $sandra = new SandraCore\System(null,true);

    $weblearner = new LearnFromWeb\LearnFromWeb($sandra);

    $factory = $weblearner->getFactoryFromLearnerName('BooCollectionsLearner');
    $factoryEth = $weblearner->getFactoryFromLearnerName('openSeaLearner');



    $factory->getAllWith('envCode',$identifier);


    return response()->json($factory->getDisplay('array',$todisplay,$booCollectionVocabulary), 200);




});

Route::middleware('cors')->get('/getCollection/getLearners', function (Request $request) {

    $sandra = new SandraCore\System(null,true);


    $weblearner = new LearnFromWeb\LearnFromWeb($sandra);



    return response()->json($factory->getDisplay('array',$todisplay,$booCollectionVocabulary), 200);




});




Route::domain('localhost')->group(function () {
    Route::get('/shaban', 'CollectionController@getAll');
});

