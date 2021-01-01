<?php
/**
  * routes-slug.php registers all the paths used in accessing and
  * interacting with tree reports.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

// views include full, public, pdf, full-pdf, xml, full-xml
Route::post(
    '/{pageSlug}/read-{cid}/full/t-{token}',
    $path . 'Survloop@tokenByID'
);
Route::get(
    '/{pageSlug}/read-{cid}/full/t-{token}',
    $path . 'Survloop@tokenByID'
);
Route::get(
    '/{treeSlug}/read-{cid}/xml',
    $path . 'Survloop@xmlByID'
);
Route::get(
    '/{treeSlug}/read-{cid}/full-xml',
    $path . 'Survloop@xmlFullByID'
);
//Route::get(
//    '/{treeSlug}/read-{cid}/json',
//    $path . 'Survloop@xmlByID'
//);
Route::post(
    '/{pageSlug}/read-{cid}/{view}',
    $path . 'Survloop@loadPageURL'
);
Route::get(
    '/{pageSlug}/read-{cid}/{view}',
    $path . 'Survloop@loadPageURL'
);
Route::post(
    '/{pageSlug}/read-{cid}',
    $path . 'Survloop@loadPageURL'
);
Route::get( 
    '/{pageSlug}/read-{cid}',
    $path . 'Survloop@loadPageURL'
);
Route::post(
    '/{pageSlug}/u-{cid}',
    $path . 'Survloop@loadPageURL'
);
Route::get(
    '/{pageSlug}/u-{cid}',
    $path . 'Survloop@loadPageURL'
);
Route::post(
    '/{pageSlug}/readi-{cid}/{view}',
    $path . 'Survloop@loadPageURLrawID'
);
Route::get(
    '/{pageSlug}/readi-{cid}/{view}',
    $path . 'Survloop@loadPageURLrawID'
);
Route::post(
    '/{pageSlug}/readi-{cid}',
    $path . 'Survloop@loadPageURLrawID'
);
Route::get( 
    '/{pageSlug}/readi-{cid}',
    $path . 'Survloop@loadPageURLrawID'
);
Route::post(
    '/{pageSlug}',
    $path . 'Survloop@loadPageURL'
);
Route::get( 
    '/{pageSlug}',
    $path . 'Survloop@loadPageURL'
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
    'uses'       => $path . 'Admin\\AdminController@loadPageURLrawID', 
    'middleware' => ['auth']
]);
Route::get( '/dash/{pageSlug}/readi-{cid}', [
    'uses'       => $path . 'Admin\\AdminController@loadPageURLrawID', 
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
