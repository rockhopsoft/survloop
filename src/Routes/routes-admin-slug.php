<?php
/**
  * routes-admin-slug.php registers all the paths used in accessing and
  * interacting with admin tree reports.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

// survey process for any admin tree
Route::get( '/dashboard/start/{treeSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadNodeTreeURL', 
    'middleware' => ['auth']
]);
Route::get( '/dashboard/start-{cid}/{treeSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadNodeTreeURLedit', 
    'middleware' => ['auth']
]);
Route::post('/dash-sub', [
    'uses'       => $path . 'Admin\\AdminController@postNodeURL', 
    'middleware' => ['auth']
]);
Route::post('/dash/u/{treeSlug}/{nodeSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadNodeURL', 
    'middleware' => ['auth']
]);
Route::get( '/dash/u/{treeSlug}/{nodeSlug}', [
    'uses'       => $path . 'Admin\\AdminController@loadNodeURL', 
    'middleware' => ['auth']
]);