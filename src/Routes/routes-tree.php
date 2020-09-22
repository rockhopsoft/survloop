<?php
/**
  * routes-tree.php registers all the paths used by core Survloop behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

// main survey process for primary database, primary tree
Route::post(
    '/u/{nodeSlug}',
    $path . 'Survloop@loadNodeURL'
);
Route::get(
    '/u/{nodeSlug}',
    $path . 'Survloop@loadNodeURL'
);

// survey process for any database or tree
Route::get(
    '/start/{treeSlug}',
    $path . 'Survloop@loadNodeTreeURL'
);
Route::get(
    '/start-{cid}/{treeSlug}', [
        'uses' => $path . 'Survloop@loadNodeTreeURLedit', 
        'middleware' => ['auth']
    ]
);
Route::post(
    '/u/{treeSlug}/{nodeSlug}',
    $path . 'Survloop@loadNodeURL'
);
Route::get(
    '/u/{treeSlug}/{nodeSlug}',
    $path . 'Survloop@loadNodeURL'
);

Route::get(
    '/switch/{treeID}/{cid}',
    $path . 'Survloop@switchSess'
);
Route::get(
    '/delSess/{treeID}/{cid}',
    $path . 'Survloop@delSess'
);
Route::get(
    '/cpySess/{treeID}/{cid}',
    $path . 'Survloop@cpySess'
);

Route::post(
    '/sub',
    $path . 'Survloop@mainSub'
);

Route::get(
    '/sortLoop',
    $path . 'Survloop@sortLoop'
);

Route::get(
    '/defer/{treeID}/{cid}/{nID}/{date}/{rand}',
    $path . 'Survloop@deferNode'
);

Route::get(
    '/{abbr}/uploads/{file}',
    $path . 'Survloop@getUploadFile'
);
Route::get(
    '/up-fresh-{rand}/{treeSlug}/{cid}/{upID}',
    $path . 'Survloop@retrieveUploadFresh'
);
Route::get(
    '/up/{treeSlug}/{cid}/{upID}',
    $path . 'Survloop@retrieveUpload'
);
Route::get(
    '/up-img-resize-all/{treeSlug}',
    $path . 'Survloop@checkImgResizeAll'
);


Route::get(
    '/records-full/{treeID}',
    $path . 'Survloop@ajaxRecordFulls'
);
Route::get(
    '/record-prevs/{treeID}',
    $path . 'Survloop@ajaxRecordPreviews'
);
Route::get(
    '/record-check/{treeID}',
    $path . 'Survloop@ajaxMultiRecordCheck'
);
Route::get(
    '/record-graph/{gType}/{treeID}/{nID}',
    $path . 'Survloop@ajaxGraph'
);
Route::get(
    '/widget-custom/{treeID}/{nID}',
    $path . 'Survloop@widgetCust'
);

Route::get(
    '/ajax-get-flds/{treeID}',
    $path . 'Survloop@getSetFlds'
);
Route::get(
    '/ajax-get-flds/{treeID}/{rSet}',
    $path . 'Survloop@getSetFlds'
);
Route::get(
    '/ajax-emoji-tag/{treeID}/{recID}/{defID}',
    $path . 'Survloop@ajaxEmojiTag'
);

    
Route::get(
    '/api/all/{treeSlug}/xml',
    $path . 'Survloop@xmlAll'
);
Route::get(
    '/{treeSlug}-xml-all',
    $path . 'Survloop@xmlAll'
);
Route::get(
    '/{treeSlug}-xml-example',
    $path . 'Survloop@getXmlExample'
);
Route::get(
    '/{treeSlug}-xml-example.xml',
    $path . 'Survloop@getXmlExample'
);
Route::get(
    '/schema/{treeSlug}/xml',
    $path . 'Survloop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-schema-xml',
    $path . 'Survloop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-schema-xml.xsd',
    $path . 'Survloop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-xml-schema',
    $path . 'Survloop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-xml-schema.xsd',
    $path . 'Survloop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-report-xml/{cid}',
    $path . 'Survloop@xmlByID'
);
Route::get(
    '/xml-example',
    $path . 'Survloop@getXmlExample'
);
Route::get(
    '/xml-schema',
    $path . 'Survloop@genXmlSchema'
);
