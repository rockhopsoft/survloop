<?php
/**
  * routes-admin-tree.php registers the admin paths used for
  * managing Survloop trees.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

Route::get( '/dashboard/subs', [
    'uses'       => $path . 'Admin\\AdminController@listSubsAll',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/subs/all', [
    'uses'       => $path . 'Admin\\AdminController@listSubsAll',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/subs/unpublished', [
    'uses'       => $path . 'Admin\\AdminController@listUnpublished',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/subs/incomplete', [
    'uses'       => $path . 'Admin\\AdminController@listSubsIncomplete',    
    'middleware' => ['auth']
]);

Route::post('/dashboard/subs/{treeID}/{cid}', [
    'uses'       => $path . 'Admin\\AdminController@printSubView',    
    'middleware' => ['auth']
]);
Route::get( '/dashboard/subs/{treeID}/{cid}', [
    'uses'       => $path . 'Admin\\AdminController@printSubView',    
    'middleware' => ['auth']
]);


Route::get(
    '/tree/{treeSlug}', 
    $path . 'Admin\\AdminTreeController@adminPrintFullTreePublic'
);

Route::post('/dashboard/surveys/list', [
    'uses'       => $path . 'Admin\\AdminTreeController@treesList',
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surveys/list', [
    'uses'       => $path . 'Admin\\AdminTreeController@treesList',
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/settings', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeSettings', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/settings', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeSettings', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/map', [
    'uses'       => $path . 'Admin\\AdminTreeController@index', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/map', [
    'uses'       => $path . 'Admin\\AdminTreeController@index', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/sessions', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeSessions',
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/sessions/graph-daily', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeSessGraphDaily',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/sessions/graph-durations', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeSessGraphDurations',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/stats', [
    'uses'       => $path . 'Admin\\AdminTreeController@treeStats', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/data', [
    'uses'       => $path . 'Admin\\AdminTreeController@data', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/data', [
    'uses'       => $path . 'Admin\\AdminTreeController@data', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/map/node/{nID}', [
    'uses'       => $path . 'Admin\\AdminTreeController@nodeEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/map/node/{nID}', [
    'uses'       => $path . 'Admin\\AdminTreeController@nodeEdit', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/xmlmap', [
    'uses'       => $path . 'Admin\\AdminTreeController@xmlmap', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/{treeSlug}-xmlmap', [
    'uses'       => $path . 'Admin\\AdminTreeController@xmlmapInner', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/xmlmap', [
    'uses'       => $path . 'Admin\\AdminTreeController@xmlmap', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surv-{treeID}/xmlmap/node/{nodeIN}', [
    'uses'       => $path . 'Admin\\AdminTreeController@xmlNodeEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surv-{treeID}/xmlmap/node/{nodeIN}', [
    'uses'       => $path . 'Admin\\AdminTreeController@xmlNodeEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surveys/switch/{treeID}', [
    'uses'       => $path . 'Admin\\AdminTreeController@switchTreeAdmin', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surveys/switch', [
    'uses'       => $path . 'Admin\\AdminTreeController@switchTreeAdmin', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/surveys/new', [
    'uses'       => $path . 'Admin\\AdminTreeController@newTree', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/surveys/new', [
    'uses'       => $path . 'Admin\\AdminTreeController@newTree', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/pages/add-{addPageType}', [
    'uses'       => $path . 'Admin\\AdminTreeController@autoAddPages', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/page/{treeID}', [
    'uses'       => $path . 'Admin\\AdminTreeController@indexPage', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/page/{treeID}', [
    'uses'       => $path . 'Admin\\AdminTreeController@indexPage', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/page/{treeID}/map', [
    'uses'       => $path . 'Admin\\AdminTreeController@indexPage', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/page/{treeID}/map', [
    'uses'       => $path . 'Admin\\AdminTreeController@indexPage', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/pages', [
    'uses'       => $path . 'Admin\\AdminTreeController@pagesList', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/pages', [
    'uses'       => $path . 'Admin\\AdminTreeController@pagesList', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/reports', [
    'uses'       => $path . 'Admin\\AdminTreeController@reportsList', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/reports', [
    'uses'       => $path . 'Admin\\AdminTreeController@reportsList', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/redirects', [
    'uses'       => $path . 'Admin\\AdminTreeController@redirectsList', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/redirects', [
    'uses'       => $path . 'Admin\\AdminTreeController@redirectsList', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/pages/snippets', [
    'uses'       => $path . 'Admin\\AdminTreeController@blurbsList', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/pages/snippets', [
    'uses'       => $path . 'Admin\\AdminTreeController@blurbsList', 
    'middleware' => ['auth']
]);

