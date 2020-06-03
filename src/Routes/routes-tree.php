<?php
/**
  * routes-tree.php registers all the paths used by core SurvLoop behavior.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

// main survey process for primary database, primary tree
Route::post(
    '/u/{nodeSlug}',
    $path . 'SurvLoop@loadNodeURL'
);
Route::get(
    '/u/{nodeSlug}',
    $path . 'SurvLoop@loadNodeURL'
);

// survey process for any database or tree
Route::get(
    '/start/{treeSlug}',
    $path . 'SurvLoop@loadNodeTreeURL'
);
Route::get(
    '/start-{cid}/{treeSlug}', [
        'uses' => $path . 'SurvLoop@loadNodeTreeURLedit', 
        'middleware' => ['auth']
    ]
);
Route::post(
    '/u/{treeSlug}/{nodeSlug}',
    $path . 'SurvLoop@loadNodeURL'
);
Route::get(
    '/u/{treeSlug}/{nodeSlug}',
    $path . 'SurvLoop@loadNodeURL'
);

Route::get(
    '/switch/{treeID}/{cid}',
    $path . 'SurvLoop@switchSess'
);
Route::get(
    '/delSess/{treeID}/{cid}',
    $path . 'SurvLoop@delSess'
);
Route::get(
    '/cpySess/{treeID}/{cid}',
    $path . 'SurvLoop@cpySess'
);

Route::post(
    '/sub',
    $path . 'SurvLoop@mainSub'
);

Route::get(
    '/sortLoop',
    $path . 'SurvLoop@sortLoop'
);

Route::get(
    '/defer/{treeID}/{cid}/{nID}/{date}/{rand}',
    $path . 'SurvLoop@deferNode'
);

Route::get(
    '/{abbr}/uploads/{file}',
    $path . 'SurvLoop@getUploadFile'
);
Route::get(
    '/up/{treeSlug}/{cid}/{upID}',
    $path . 'SurvLoop@retrieveUpload'
);


Route::get(
    '/records-full/{treeID}',
    $path . 'SurvLoop@ajaxRecordFulls'
);
Route::get(
    '/record-prevs/{treeID}',
    $path . 'SurvLoop@ajaxRecordPreviews'
);
Route::get(
    '/record-check/{treeID}',
    $path . 'SurvLoop@ajaxMultiRecordCheck'
);
Route::get(
    '/record-graph/{gType}/{treeID}/{nID}',
    $path . 'SurvLoop@ajaxGraph'
);
Route::get(
    '/widget-custom/{treeID}/{nID}',
    $path . 'SurvLoop@widgetCust'
);

Route::get(
    '/ajax-get-flds/{treeID}',
    $path . 'SurvLoop@getSetFlds'
);
Route::get(
    '/ajax-get-flds/{treeID}/{rSet}',
    $path . 'SurvLoop@getSetFlds'
);
Route::get(
    '/ajax-emoji-tag/{treeID}/{recID}/{defID}',
    $path . 'SurvLoop@ajaxEmojiTag'
);

    
Route::get(
    '/{treeSlug}-xml-all',
    $path . 'SurvLoop@xmlAll'
);
Route::get(
    '/{treeSlug}-xml-example',
    $path . 'SurvLoop@getXmlExample'
);
Route::get(
    '/{treeSlug}-xml-example.xml',
    $path . 'SurvLoop@getXmlExample'
);
Route::get(
    '/schema/{treeSlug}/xml',
    $path . 'SurvLoop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-schema-xml',
    $path . 'SurvLoop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-schema-xml.xsd',
    $path . 'SurvLoop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-xml-schema',
    $path . 'SurvLoop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-xml-schema.xsd',
    $path . 'SurvLoop@genXmlSchema'
);
Route::get(
    '/{treeSlug}-report-xml/{cid}',
    $path . 'SurvLoop@xmlByID'
);
Route::get(
    '/xml-example',
    $path . 'SurvLoop@getXmlExample'
);
Route::get(
    '/xml-schema',
    $path . 'SurvLoop@genXmlSchema'
);
