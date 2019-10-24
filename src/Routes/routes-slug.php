<?php
/**
  * routes-slug.php registers all the paths used in accessing and
  * interacting with tree reports.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */

// views include full, public, pdf, full-pdf, xml, full-xml
Route::post(
    '/{pageSlug}/read-{cid}/full/t-{token}',
    $path . 'SurvLoop@tokenByID'
);
Route::get(
    '/{pageSlug}/read-{cid}/full/t-{token}',
    $path . 'SurvLoop@tokenByID'
);
Route::get(
    '/{treeSlug}/read-{cid}/xml',
    $path . 'SurvLoop@xmlByID'
);
//Route::get(
//    '/{treeSlug}/read-{cid}/json',
//    $path . 'SurvLoop@xmlByID'
//);
Route::post(
    '/{pageSlug}/read-{cid}/{view}',
    $path . 'SurvLoop@loadPageURL'
);
Route::get(
    '/{pageSlug}/read-{cid}/{view}',
    $path . 'SurvLoop@loadPageURL'
);
Route::post(
    '/{pageSlug}/read-{cid}',
    $path . 'SurvLoop@loadPageURL'
);
Route::get( 
    '/{pageSlug}/read-{cid}',
    $path . 'SurvLoop@loadPageURL'
);
Route::post(
    '/{pageSlug}/u-{cid}',
    $path . 'SurvLoop@loadPageURL'
);
Route::get(
    '/{pageSlug}/u-{cid}',
    $path . 'SurvLoop@loadPageURL'
);
Route::post(
    '/{pageSlug}/readi-{cid}/{view}',
    $path . 'SurvLoop@loadPageURLrawID'
);
Route::get(
    '/{pageSlug}/readi-{cid}/{view}',
    $path . 'SurvLoop@loadPageURLrawID'
);
Route::post(
    '/{pageSlug}/readi-{cid}',
    $path . 'SurvLoop@loadPageURLrawID'
);
Route::get( 
    '/{pageSlug}/readi-{cid}',
    $path . 'SurvLoop@loadPageURLrawID'
);
Route::post(
    '/{pageSlug}',
    $path . 'SurvLoop@loadPageURL'
);
Route::get( 
    '/{pageSlug}',
    $path . 'SurvLoop@loadPageURL'
);

Route::post('/dash/{pageSlug}/read-{cid}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
Route::get( '/dash/{pageSlug}/read-{cid}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
Route::post('/dash/{pageSlug}/readi-{cid}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
Route::get( '/dash/{pageSlug}/readi-{cid}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
Route::post('/dash/{pageSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
Route::get( '/dash/{pageSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURL', 
    'middleware' => ['auth']
]);
