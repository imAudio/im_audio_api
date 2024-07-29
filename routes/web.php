<?php
use Illuminate\Support\Facades\Route;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->options(
    '/{any:.*}',
    function () {
        return response(['status' => 'success'], 200);
    }
);
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@authenticate');
    Route::post('creat-send-code-password', 'AuthController@creatSendCodePassword');
    Route::post('check-code', 'AuthController@checkCode');
    Route::post('new-password', 'AuthController@newPassword');
});


$router->get('/', function () use ($router) {
    echo "<center> Welcome </center>";
});

$router->group(['middleware' => 'auth.jwt'], function () use ($router) {
    $router->get('protected', function () {
        return response()->json(['message' => 'This is a protected route']);
    });

    Route::group([
        'prefix' => 'user'
    ], function ($router) {
        Route::get('get-info', 'UserController@getAuthenticatedUser');
    });

    Route::group([
        'prefix' => 'audio-center'
    ], function ($router) {
        Route::get('get-all', 'AudioCenterController@index');
    });

    Route::group([
        'prefix' => 'patient'
    ], function ($router) {
        Route::get('autocomplete/{query}', 'PatientController@autocomplete');
        Route::post('new', 'PatientController@create');
        Route::get('{id}','PatientController@show');
    });
    $router->get('/version', function () use ($router) {
        return $router->app->version();
    });

    Route::group([
        'prefix' => 'to-do-list'
    ], function ($router) {
        Route::post('new', 'ToDoListController@create');
        Route::get('by-audio-center/{id_audio_center}','ToDoListController@getByAudioCenter');
        Route::patch('make/{id_to_do_list}','ToDoListController@make');
        Route::put('put','ToDoListController@update');
    });

    Route::group([
        'prefix' => 'patient-note'
    ], function ($router) {
        Route::post('new', 'PatientNoteController@create');
        Route::get('by-patient/{idPatient}', 'PatientNoteController@getByPatient');
    });

    Route::group([
        'prefix' => 'user-document'
    ],function ($router){

        Route::get('download/{id_document}', 'UserDocumentController@download');
        Route::get('get-by-user/{id_user}','UserDocumentController@getDocumentByUser');
        Route::get('quote/search/{query}/{id_user}','UserDocumentController@searchQuote');
        Route::get('quote/check/{id_user}','UserDocumentController@checkQuote');
        Route::get('quote-affiliate/check/{id_user}','UserDocumentController@checkQuoteAffiliate');
        Route::get('prescription/check/{id_user}','UserDocumentController@checkPrescription');
        Route::post('quote','UserDocumentController@generateQuote');
        Route::post('upload', 'UserDocumentController@create');
        Route::delete('delete','UserDocumentController@delete');

    });

    Route::group([
        'prefix' => 'device'
    ], function ($router) {
        Route::get('show/{id_device}', 'DeviceController@show');
        Route::get('history-state/{id_device}', 'DeviceController@getHistoryState');
        Route::get('history-transfer/{id_device}', 'DeviceController@getHistoryTransfer');
        Route::get('by-state/set-sail/{state}/{idAudioCenter}', 'DeviceController@getSetSailByState');
        Route::get('by-state-audio-center/{state}/{idAudioCenter}', 'DeviceController@getByStateAudioCenter');
        Route::get('autocomplete/{query}/{id_audio_center}/{type}','DeviceController@autocomplete');
        Route::post('new', 'DeviceController@create');
        Route::patch('edit/state', 'DeviceController@editState');
        Route::patch('edit/transfer', 'DeviceController@transfer');
        Route::put('put','DeviceController@update');
    });

    Route::group([
       'prefix' => 'set-sail'
    ],function ($router){
        Route::post('new','SetSailController@create');
    });

    Route::group([
        'prefix' => 'useful-link'
        ], function ($router) {
        Route::get('/', 'UsefulLinkController@getByUser');
        Route::get('{id_useful_link}', 'UsefulLinkController@show');
        Route::post('new', 'UsefulLinkController@create');
        Route::delete('delete','UsefulLinkController@delete');
        Route::put('put','UsefulLinkController@update');
    });

    Route::group([
        'prefix' => 'event'
    ], function ($router) {
        Route::get('by-audio-center-and-date/{id_audio_center}', 'EventController@getByAudioCenterAndDate');
        Route::get('by-id/{id_event}', 'EventController@show');
        Route::post('new','EventController@create');
        Route::delete('delete','EventController@delete');
        Route::patch('edit/state','EventController@editState');
        Route::put('put','EventController@update');
    });

    Route::group([
        'prefix' => 'event-type'
    ], function ($router) {
        Route::get('/', 'EventTypeController@index');
    });

    Route::group([
        'prefix' => 'device-manufactured'
    ], function ($router){
        Route::get('/','DeviceManufacturedController@index');
        Route::post('new','DeviceManufacturedController@create');
    });

    Route::group([
        'prefix' => 'device-model'
    ], function ($router){
        Route::get('/','DeviceModelController@index');
        Route::get('by-manufactured/{id_manufactured}','DeviceModelController@byManufactured');
    });

    Route::group([
        'prefix' => 'delivery-note'
    ], function ($router){
        Route::get('by-audio-center/{id_audio_center}','DeliveryNoteController@getByAudioCenter');
        Route::get('by-device/{id_device}','DeliveryNoteController@getByDevice');
    });

    Route::group([
        'prefix' => 'device-type'
    ], function ($router){
        Route::get('/','DeviceTypeController@index');
    });

    Route::group([
        'prefix' => 'attribut-mcq'
    ], function ($router){
        Route::post('new','AttributMcqController@create');
    });

    Route::group([
        'prefix' => 'mcq'
    ], function ($router){
        Route::get('/','McqController@index');
    });
});

