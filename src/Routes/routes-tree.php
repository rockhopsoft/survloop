<?php
/**
  * routes-tree.php registers all the paths used by core Survloop behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */


use RockHopSoft\Survloop\Controllers\Survloop;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;
use RockHopSoft\Survloop\Controllers\Admin\AdminDBController;
use RockHopSoft\Survloop\Controllers\Admin\AdminTreeController;

/**
 * Survloop-Generated Survey Routes
 */
Route::post('/sub',      [Survloop::class, 'mainSub']);
Route::get('/sortLoop',  [Survloop::class, 'sortLoop']);

Route::get('/switch/{treeID}/{cid}',  [Survloop::class, 'switchSess']);
Route::get('/delSess/{treeID}/{cid}', [Survloop::class, 'delSess']);
Route::get('/cpySess/{treeID}/{cid}', [Survloop::class, 'cpySess']);

Route::get('/start/{treeSlug}',       [Survloop::class, 'loadNodeTreeURL']);
Route::get('/start-{cid}/{treeSlug}', [Survloop::class, 'loadNodeTreeURLedit'])
    ->middleware('auth');

Route::post('/u/{treeSlug}/{nodeSlug}', [Survloop::class, 'loadNodeURL']);
Route::get('/u/{treeSlug}/{nodeSlug}', [Survloop::class, 'loadNodeURL']);
Route::post('/u/{nodeSlug}',            [Survloop::class, 'loadNodeURL']);
Route::get('/u/{nodeSlug}',            [Survloop::class, 'loadNodeURL']);

Route::get('/defer/{treeID}/{cid}/{nID}/{date}/{rand}', [Survloop::class, 'deferNode']);
Route::get('/up-fresh-{rand}/{treeSlug}/{cid}/{upID}',  [Survloop::class, 'retrieveUploadFresh']);
Route::get('/up/{treeSlug}/{cid}/{upID}',               [Survloop::class, 'retrieveUpload']);
Route::get('/up-img-resize-all/{treeSlug}',             [Survloop::class, 'checkImgResizeAll']);
Route::get('/{abbr}/uploads/{file}',                    [Survloop::class, 'getUploadFile']);


/**
 * Survey & Page Components
 */
Route::get('/records-full/{treeID}',  [Survloop::class, 'ajaxRecordFulls']);
Route::get('/record-prevs/{treeID}', [Survloop::class, 'ajaxRecordPreviews']);
Route::get('/record-check/{treeID}',  [Survloop::class, 'ajaxMultiRecordCheck']);

Route::get('/record-graph/{gType}/{treeID}/{nID}',     [Survloop::class, 'ajaxGraph']);
Route::get('/widget-custom/{treeID}/{nID}',            [Survloop::class, 'widgetCust']);
Route::get('/ajax-get-flds/{treeID}',                  [Survloop::class, 'getSetFlds']);
Route::get('/ajax-get-flds/{treeID}/{rSet}',           [Survloop::class, 'getSetFlds']);
Route::get('/ajax-emoji-tag/{treeID}/{recID}/{defID}', [Survloop::class, 'ajaxEmojiTag']);


/**
 * Survey Data Exports & API
 */

Route::get('/db/{database}',   [AdminDBController::class,   'adminPrintFullDBPublic']);
Route::get('/tree/{treeSlug}', [AdminTreeController::class, 'adminPrintFullTreePublic']);

Route::get('/api/all/{treeSlug}/xml',      [Survloop::class, 'xmlAll']);
Route::get('/{treeSlug}-xml-all',          [Survloop::class, 'xmlAll']);
Route::get('/{treeSlug}-xml-example',      [Survloop::class, 'getXmlExample']);
Route::get('/{treeSlug}-xml-example.xml',  [Survloop::class, 'getXmlExample']);
Route::get('/schema/{treeSlug}/xml',       [Survloop::class, 'genXmlSchema']);
Route::get('/{treeSlug}-schema-xml',       [Survloop::class, 'genXmlSchema']);
Route::get('/{treeSlug}-schema-xml.xsd',   [Survloop::class, 'genXmlSchema']);
Route::get('/{treeSlug}-xml-schema',       [Survloop::class, 'genXmlSchema']);
Route::get('/{treeSlug}-xml-schema.xsd',   [Survloop::class, 'genXmlSchema']);
Route::get('/{treeSlug}-report-xml/{cid}', [Survloop::class, 'xmlByID']);
Route::get('/{treeSlug}-report-xml/{cid}', [Survloop::class, 'xmlByID']);
Route::get('/xml-example',                 [Survloop::class, 'getXmlExample']);
Route::get('/xml-schema',                  [Survloop::class, 'genXmlSchema']);


/**
 * Survloop-Generated Survey Routes Requiring Authentication
 */

Route::middleware(['auth'])->group(function () {

  Route::get('/dashboard/start/{treeSlug}',       [AdminController::class, 'loadNodeTreeURL']);
  Route::get('/dashboard/start-{cid}/{treeSlug}', [AdminController::class, 'loadNodeTreeURLedit']);
  Route::post('/dash/u/{treeSlug}/{nodeSlug}',    [AdminController::class, 'loadNodeURL']);
  Route::get('/dash/u/{treeSlug}/{nodeSlug}',     [AdminController::class, 'loadNodeURL']);
  Route::post('/dash-sub',                        [AdminController::class, 'postNodeURL']);

});


/**
 * Survloop-Generated Page Routes Requiring Authentication
 */

Route::middleware(['auth'])->group(function () {

  Route::post('/dash/{pageSlug}/read-{cid}',  [AdminController::class, 'loadPageURL']);
  Route::get('/dash/{pageSlug}/read-{cid}',   [AdminController::class, 'loadPageURL']);
  Route::post('/dash/{pageSlug}/readi-{cid}', [AdminController::class, 'loadPageURLrawID']);
  Route::get('/dash/{pageSlug}/readi-{cid}',  [AdminController::class, 'loadPageURLrawID']);
  Route::post('/dash/{pageSlug}',             [AdminController::class, 'loadPageURL']);
  Route::get('/dash/{pageSlug}',              [AdminController::class, 'loadPageURL']);

});


/**
 * Survloop-Generated Page Routes
 */
Route::post('/{pageSlug}/read-{cid}/full/t-{token}', [Survloop::class, 'tokenByID']);
Route::get('/{pageSlug}/read-{cid}/full/t-{token}',  [Survloop::class, 'tokenByID']);
Route::get('/{treeSlug}/read-{cid}/xml',             [Survloop::class, 'xmlByID']);
Route::get('/{treeSlug}/read-{cid}/full-xml',        [Survloop::class, 'xmlFullByID']);
//Route::get('/{treeSlug}/read-{cid}/json',          [Survloop::class, 'xmlByID']);

Route::post('/{pageSlug}/read-{cid}/{view}',         [Survloop::class, 'loadPageURL']);
Route::get('/{pageSlug}/read-{cid}/{view}',          [Survloop::class, 'loadPageURL']);
Route::post('/{pageSlug}/read-{cid}',                [Survloop::class, 'loadPageURL']);
Route::get('/{pageSlug}/read-{cid}',                 [Survloop::class, 'loadPageURL']);
Route::post('/{pageSlug}/u-{cid}',                   [Survloop::class, 'loadPageURL']);
Route::get('/{pageSlug}/u-{cid}',                    [Survloop::class, 'loadPageURL']);
Route::post('/{pageSlug}/readi-{cid}/{view}',        [Survloop::class, 'loadPageURLrawID']);
Route::get('/{pageSlug}/readi-{cid}/{view}',         [Survloop::class, 'loadPageURLrawID']);
Route::post('/{pageSlug}/readi-{cid}',               [Survloop::class, 'loadPageURLrawID']);
Route::get('/{pageSlug}/readi-{cid}',                [Survloop::class, 'loadPageURLrawID']);
Route::post('/{pageSlug}',                           [Survloop::class, 'loadPageURL']);
Route::get('/{pageSlug}',                            [Survloop::class, 'loadPageURL']);


