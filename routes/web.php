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
$router->post('auth/login', 'AuthController@authenticate');

$router->get('/', function () use ($router) {
    echo "<center> Welcome </center>";
});

$router->group(['middleware' => 'auth.jwt'], function () use ($router) {
    $router->get('protected', function () {
        return response()->json(['message' => 'This is a protected route']);
    });

    $router->get('/version', function () use ($router) {
        return $router->app->version();
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

    Route::group([
        'prefix' => 'to-do-list'
    ], function ($router) {
        Route::post('new', 'ToDoListController@create');
        Route::get('by-audio-center/{id_audio_center}','ToDoListController@getByAudioCenter');
        Route::patch('make/{id_to_do_list}','ToDoListController@make');
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
        Route::post('upload', 'UserDocumentController@create');
        Route::get('download/{id_document}', 'UserDocumentController@download');
        Route::get('get-by-user/{id_user}','UserDocumentController@getDocumentByUser');
        Route::delete('delete','UserDocumentController@delete');
    });

    Route::group([
        'prefix' => 'device'
    ], function ($router) {
        Route::get('by-state/set-sail/{state}/{idAudioCenter}', 'DeviceController@getSetSailByState');
        Route::get('by-state/{state}/{idAudioCenter}/{idManufactured}/{contentModele}', 'DeviceController@getByState');
    });
    Route::group([
            'prefix' => 'useful-link'
        ], function ($router) {
        Route::get('/', 'UsefulLinkController@getByUser');
        Route::post('new', 'UsefulLinkController@create');
        Route::delete('delete','UsefulLinkController@delete');
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
});



