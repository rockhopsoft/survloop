<?php
/**
  * routes-core.php registers all the paths used by core SurvLoop behavior.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */

Route::post(
    '/',
    $path . 'SurvLoop@loadPageHome'
);
Route::get(
    '/',
    $path . 'SurvLoop@loadPageHome'
);
Route::post(
    '/home', 
    $path . 'SurvLoop@loadPageHome'
);
Route::get( 
    '/home', 
    $path . 'SurvLoop@loadPageHome'
);

Route::get(
    '/holdSess',
    $path . 'SurvLoop@holdSess'
);
Route::get(
    '/restart',
    $path . 'SurvLoop@restartSess'
);
Route::get(
    '/sessDump',
    $path . 'SurvLoop@sessDump'
);
Route::get(
    '/test',
    $path . 'SurvRoutes@testHome'
);

Route::get(
    '/search-bar',
    $path . 'SurvLoop@searchBar'
);
Route::get(
    '/search-results/{treeID}',
    $path . 'SurvLoop@searchResultsAjax'
);
Route::get(
    '/search-run',
    $path . 'SurvLoop@searchRun'
);

Route::post(
    '/ajax',
    $path . 'SurvLoop@ajaxChecks'
);
Route::get(
    '/ajax',
    $path . 'SurvLoop@ajaxChecks'
);
Route::post(
    '/ajax/{type}',
    $path . 'SurvLoop@ajaxChecks'
);
Route::get(
    '/ajax/{type}',
    $path . 'SurvLoop@ajaxChecks'
);

Route::get(
    '/fresh/creator', 
    $path . 'Admin\\AdminTreeController@freshUser'
);
Route::post(
    '/fresh/database', 
    $path . 'Admin\\AdminTreeController@freshDB'
);
Route::get( 
    '/fresh/database', 
    $path . 'Admin\\AdminTreeController@freshDB'
);
Route::post(
    '/fresh/survey',   
    $path . 'Admin\\AdminTreeController@freshUX'
);
Route::get(
    '/fresh/survey',   
    $path . 'Admin\\AdminTreeController@freshUX'
);

Route::post(
    '/login', [
        'as' => 'login',
        'uses' => $path . 'Auth\\AuthController@postLogin'
    ]
);
Route::get(
    '/login', [
        'as' => 'login', 
        'uses' => $path . 'Auth\\AuthController@getLogin'
    ]
);
Route::post(
    '/register',
    $path . 'Auth\\SurvRegisterController@register'
);
Route::get(
    '/register',
    $path . 'Auth\\AuthController@getRegister'
);
Route::post(
    '/afterLogin',
    $path . 'SurvLoop@afterLogin'
);
Route::get(
    '/afterLogin',
    $path . 'SurvLoop@afterLogin'
);
Route::get(
    '/logout',
    $path . 'Auth\\AuthController@getLogout'
);
Route::get(
    '/chkEmail',
    $path . 'SurvLoop@chkEmail'
);

Route::get(
    '/time-out',
    $path . 'SurvLoop@timeOut'
);
Route::get(
    '/survloop-stats.json',
    $path . 'SurvLoop@getJsonSurvStats'
);

Route::get(
    '/email-confirm/{token}/{tokenB}',
    $path . 'SurvLoop@processEmailConfirmToken'
);

///////////////////////////////////////////////////////////

Route::get(
    '/js-load-menu', 
    $path . 'SurvLoop@jsLoadMenu'
);

Route::get( 
    '/sys{which}.min.{type}',  
    $path . 'SurvRoutes@getSysFileMin'
);
Route::get( 
    '/sys{which}.{type}',
    $path . 'SurvRoutes@getSysFile'
);
Route::get(
    '/tree-{treeID}.js',
    $path . 'SurvRoutes@getSysTreeJs'
);
Route::get(
    '/sys/dyna/{file}.{type}',
    $path . 'SurvRoutes@getDynaFile'
);
Route::get( 
    '/gen-kml/{kmlfile}.kml',  
    $path . 'SurvRoutes@getKml'
);

Route::get( 
    '/jquery.min.js',
    $path . 'SurvRoutes@getJquery'
);
Route::get(
    '/jquery-ui.min.{type}',
    $path . 'SurvRoutes@getJqueryUi'
);
Route::get(
    '/bootstrap.min.{type}',
    $path . 'SurvRoutes@getBootstrap'
);

Route::get( 
    '/css/fork-awesome.min.css', 
    $path . 'SurvRoutes@getFontAwesome'
);
Route::get( 
    '/fonts/{file}',
    $path . 'SurvRoutes@getFont'
);

Route::get( 
    '/summernote.min.js',
    $path . 'SurvRoutes@getSummernoteJs'
);
Route::get(
    '/summernote.css',
    $path . 'SurvRoutes@getSummernoteCss'
);
Route::get(
    '/font/summernote.eot',
    $path . 'SurvRoutes@getSummernoteEot'
);

Route::get( 
    '/Chart.bundle.min.js',   
    $path . 'SurvRoutes@getChartJs'
);
Route::get(
    '/plotly.min.js',
    $path . 'SurvRoutes@getPlotlyJs'
);

Route::get( 
    '/vendor/wikiworldorder/survloop/src/Public/jquery-ui-1.12.1/images/{file}',
    $path . 'SurvRoutes@catchJqueryUiMappingError'
);
