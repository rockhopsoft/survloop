<?php
/**
  * routes-admin.php registers all the paths used in the Survloop admin area.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

use RockHopSoft\Survloop\Controllers\Survloop;
use RockHopSoft\Survloop\Controllers\SystemUpdate;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;
use RockHopSoft\Survloop\Controllers\Admin\AdminDBController;
use RockHopSoft\Survloop\Controllers\Admin\AdminTreeController;
use RockHopSoft\Survloop\Controllers\Admin\AdminDatabaseInstall;
use RockHopSoft\Survloop\Controllers\Admin\AdminEmailController;

Route::middleware(['auth'])->group(function () {

    /**
     * Core Admin Dashboards and Interfaces
     */
    Route::get('admin',     [AdminController::class, 'loadPageDashboard']);
    Route::get('dash',      [AdminController::class, 'loadPageDashboard']);
    Route::get('dashboard', [AdminController::class, 'loadPageDashboard'])
        ->name('dashboard');

    Route::prefix('dashboard')->group(function () {

        /**
         * Email Templates & Contact Form
         */
        Route::get('/contact/{folder}',          [AdminEmailController::class, 'manageContact']);
        Route::get('/contact',                   [AdminEmailController::class, 'manageContact']);
        Route::post('/emails',                   [AdminEmailController::class, 'manageEmails']);
        Route::get('/emails',                    [AdminEmailController::class, 'manageEmails']);
        Route::get('/sent-emails',               [AdminEmailController::class, 'printSentEmails']);
        Route::post('/send-email',               [AdminEmailController::class, 'sendEmailPage']);
        Route::get('/send-email',                [AdminEmailController::class, 'sendEmailPage']);
        Route::post('/email/{emailID}',          [AdminEmailController::class, 'manageEmailsPost']);
        Route::get('/email/{emailID}',           [AdminEmailController::class, 'manageEmailsForm']);
        Route::post('/pages/snippets/{blurbID}', [AdminController::class, 'blurbEditSave']);
        Route::get('/pages/snippets/{blurbID}',  [AdminController::class, 'blurbEdit']);
        Route::get('/users/email',               [AdminController::class, 'userEmailing']);
        Route::get('/users',                     [AdminController::class, 'userManage']);


        /**
         * System Logs
         */
        Route::get('/logs',               [AdminController::class, 'logsOverview']);
        Route::get('/logs/session-stuff', [AdminController::class, 'logsSessions']);
        Route::get('/systems-check',      [AdminController::class, 'systemsCheck']);
        Route::get('/debug-node-saves',   [AdminController::class, 'debugNodeSaves']);


        /**
         * System Settings
         */
        Route::post('/settings/{set}', [AdminController::class, 'sysSettings']);
        Route::get('/settings/{set}',  [AdminController::class, 'sysSettings']);
        Route::get('/settings',        [AdminController::class, 'sysSettings']);
        Route::post('/pages/menus',    [AdminController::class, 'navMenus']);
        Route::get('/pages/menus',     [AdminController::class, 'navMenus']);
        Route::post('/images/gallery', [AdminTreeController::class, 'imgGallery']);
        Route::get('/images/gallery',  [AdminTreeController::class, 'imgGallery']);
        Route::get('/systems-clean',   [SystemUpdate::class, 'index']);


        /**
         * Manage System Surveys
         */

        Route::post('/surveys/list',           [AdminTreeController::class, 'treesList']);
        Route::get('/surveys/list',            [AdminTreeController::class, 'treesList']);

        Route::prefix('surv-{treeID}')->group(function () {

            Route::post('/settings', [AdminTreeController::class, 'treeSettings']);
            Route::get('/settings',  [AdminTreeController::class, 'treeSettings']);
            Route::post('/map',      [AdminTreeController::class, 'index']);
            Route::get('/map',       [AdminTreeController::class, 'index']);
            Route::get('/sessions',  [AdminTreeController::class, 'treeSessions']);
            Route::get('/stats',     [AdminTreeController::class, 'treeStats']);
            Route::post('/data',     [AdminTreeController::class, 'data']);
            Route::get('/data',      [AdminTreeController::class, 'data']);

            Route::post('/map/node/{nID}', [AdminTreeController::class, 'nodeEdit']);
            Route::get('/map/node/{nID}',  [AdminTreeController::class, 'nodeEdit']);
            Route::post('/map/node/{nID}', [AdminTreeController::class, 'nodeEdit']);
            Route::get('/map/node/{nID}',  [AdminTreeController::class, 'nodeEdit']);

            Route::post('/xmlmap',               [AdminTreeController::class, 'xmlmap']);
            Route::get('/xmlmap',                [AdminTreeController::class, 'xmlmap']);
            Route::get('/{treeSlug}-xmlmap',     [AdminTreeController::class, 'xmlmapInner']);
            Route::post('/xmlmap/node/{nodeIN}', [AdminTreeController::class, 'xmlNodeEdit']);
            Route::get('/xmlmap/node/{nodeIN}',  [AdminTreeController::class, 'xmlNodeEdit']);

        });

        Route::get('/surveys/switch/{treeID}', [AdminTreeController::class, 'switchTreeAdmin']);
        Route::get('/surveys/switch',          [AdminTreeController::class, 'switchTreeAdmin']);
        Route::post('/surveys/new',            [AdminTreeController::class, 'newTree']);
        Route::get('/surveys/new',             [AdminTreeController::class, 'newTree']);

        Route::get(
            '/surv-{treeID}/sessions/graph-daily',
            [AdminTreeController::class, 'treeSessGraphDaily']
        );
        Route::get(
            '/surv-{treeID}/sessions/graph-durations',
            [AdminTreeController::class, 'treeSessGraphDurations']
        );


        /**
         * Manage Survey-Database Conditions
         */
        Route::post('/db/conds',            [AdminTreeController::class, 'conditions']);
        Route::get('/db/conds',             [AdminTreeController::class, 'conditions']);
        Route::post('/db/conds/add',        [AdminTreeController::class, 'condAdd']);
        Route::get('/db/conds/add',         [AdminTreeController::class, 'condAdd']);
        Route::post('/db/conds/edit/{cid}', [AdminTreeController::class, 'condEdit']);
        Route::get('/db/conds/edit/{cid}',  [AdminTreeController::class, 'condEdit']);


        /**
         * Manage Pages with Content & Reports
         */
        Route::get('/pages/add-{addPageType}', [AdminTreeController::class, 'autoAddPages']);
        Route::post('/page/{treeID}',          [AdminTreeController::class, 'indexPage']);
        Route::get('/page/{treeID}',           [AdminTreeController::class, 'indexPage']);
        Route::post('/page/{treeID}/map',      [AdminTreeController::class, 'indexPage']);
        Route::get('/page/{treeID}/map',       [AdminTreeController::class, 'indexPage']);
        Route::post('/pages',                  [AdminTreeController::class, 'pagesList']);
        Route::get('/pages',                   [AdminTreeController::class, 'pagesList']);
        Route::post('/reports',                [AdminTreeController::class, 'reportsList']);
        Route::get('/reports',                 [AdminTreeController::class, 'reportsList']);
        Route::post('/redirects',              [AdminTreeController::class, 'redirectsList']);
        Route::get('/redirects',               [AdminTreeController::class, 'redirectsList']);
        Route::post('/pages/snippets',         [AdminTreeController::class, 'blurbsList']);
        Route::get('/pages/snippets',          [AdminTreeController::class, 'blurbsList']);


        /**
         * Manage Database Design
         */
        Route::get('/db', [AdminDBController::class, 'index']);
        Route::get('/db/all', [AdminDBController::class, 'full']);
        Route::get('/db/field-matrix', [AdminDBController::class, 'fieldMatrix']);
        Route::post('/db/addTable', [AdminDBController::class, 'addTable']);
        Route::get('/db/addTable', [AdminDBController::class, 'addTable']);
        Route::get('/db/sortTable', [AdminDBController::class, 'tblSort']);
        Route::post('/db/table/{tblName}/edit', [AdminDBController::class, 'editTable']);
        Route::post('/db/table/{tblName}/edit', [AdminDBController::class, 'editTable']);
        Route::get('/db/table/{tblName}/edit', [AdminDBController::class, 'editTable']);
        Route::get('/db/table/{tblName}/sort', [AdminDBController::class, 'fldSort']);
        Route::get('/db/table/{tblName}', [AdminDBController::class, 'viewTable']);

        Route::post('/db/field/{tblAbbr}/{FldName}', [AdminDBController::class, 'editField']);
        Route::get('/db/field/{tblAbbr}/{FldName}',  [AdminDBController::class, 'editField']);
        Route::post('/db/field/{tblAbbr}',           [AdminDBController::class, 'addTableFld']);
        Route::get('/db/field/{tblAbbr}',            [AdminDBController::class, 'addTableFld']);
        Route::get('/db/ajax-field/{FldID}',         [AdminDBController::class, 'fieldAjax']);
        Route::post('/db/fieldDescs',                [AdminDBController::class, 'fieldDescs']);
        Route::get('/db/fieldDescs',                 [AdminDBController::class, 'fieldDescs']);
        Route::get('/db/ajax/tblFldSelT/{rT}',       [AdminDBController::class, 'tblSelector']);
        Route::get('/db/ajax/tblFldSelF/{rF}',       [AdminDBController::class, 'fldSelector']);
        Route::get('/db/ajax/getSetFlds/{rSet}',     [AdminDBController::class, 'getSetFlds']);
        Route::get('/db/ajax/getSetFldVals/{FldID}', [AdminDBController::class, 'getSetFldVals']);

        Route::post('/db/fieldAPI/save', [AdminDBController::class, 'fieldAPIsave']);
        Route::get('/db/fieldAPI',       [AdminDBController::class, 'fieldAPI']);


        /**
         * Manage Database Design Definition Sets —
         * Also Business Rules (originally included, but under-utilized)
         */
        Route::get('/db/definitions',                   [AdminDBController::class, 'definitions']);
        Route::get('/db/definitions/add/{subset}',      [AdminDBController::class, 'defAdd']);
        Route::get('/db/definitions/add',               [AdminDBController::class, 'defAdd']);
        Route::get('/db/definitions/edit/{defID}',      [AdminDBController::class, 'defEdit']);
        Route::post('/db/definitions/add-sub/{subset}', [AdminDBController::class, 'defAdd']);
        Route::post('/db/definitions/add-sub',          [AdminDBController::class, 'defAdd']);
        Route::post('/db/definitions/edit-sub/{defID}', [AdminDBController::class, 'defEdit']);
        Route::get('/db/definitions/sort/{subset}',     [AdminDBController::class, 'defSort']);

        Route::get('/db/bus-rules',                [AdminDBController::class, 'businessRules']);
        Route::post('/db/bus-rules/add',           [AdminDBController::class, 'ruleAdd']);
        Route::get('/db/bus-rules/add',            [AdminDBController::class, 'ruleAdd']);
        Route::post('/db/bus-rules/edit/{ruleID}', [AdminDBController::class, 'ruleEdit']);
        Route::get('/db/bus-rules/edit/{ruleID}',  [AdminDBController::class, 'ruleEdit']);


        /**
         * Database Diagrams and Extras
         */
        Route::get('/db/diagrams',    [AdminDBController::class, 'diagrams']);
        Route::get('/db/network-map', [AdminDBController::class, 'networkMap']);
        Route::get('/db/workflows',   [AdminTreeController::class, 'workflows']);


        /**
         * Database Diagrams Exports
         */
        Route::get('/db/export',         [AdminDatabaseInstall::class, 'printExport']);
        Route::get('/db/export/laravel', [AdminDatabaseInstall::class, 'printExportLaravel']);

        Route::get(
            '/dashboard/db/export/laravel/table-model/{tbl}',
            [AdminDatabaseInstall::class, 'refreshTableModel']
        );

        Route::post('/db/install',       [AdminDatabaseInstall::class, 'autoInstallDatabase']);
        Route::get('/db/install',        [AdminDatabaseInstall::class, 'autoInstallDatabase']);
        Route::get('/db/export/dump',    [AdminDatabaseInstall::class, 'exportDump']);
        Route::get('/db/export',         [AdminDatabaseInstall::class, 'printExportPackage']);
        Route::get('/sl/export/laravel', [AdminDatabaseInstall::class, 'printExportPackageLaravel']);
        Route::post('/db/import',        [AdminDatabaseInstall::class, 'printImport']);
        Route::get('/db/import',         [AdminDatabaseInstall::class, 'printImport']);

        Route::get('/db/switch/{dbID}', [AdminDBController::class,    'switchDB']);
        Route::get('/db/switch',        [AdminDBController::class,    'switchDB']);
        Route::post('/db/new',          [AdminDBController::class,    'newDB']);
        Route::get('/db/new',           [AdminDBController::class,    'newDB']);
        Route::get('/db/tbl-raw',       [AdminDatabaseInstall::class, 'printRawTable']);


        /**
         * Default Admin Tools To Manage Core Records From Public Surveys
         * **** Coming Soon — On The List ****
         */
        Route::get('/subs',                 [AdminController::class, 'listSubsAll']);
        Route::get('/subs/all',             [AdminController::class, 'listSubsAll']);
        Route::get('/subs/unpublished',     [AdminController::class, 'listUnpublished']);
        Route::get('/subs/incomplete',      [AdminController::class, 'listSubsIncomplete']);
        Route::post('/subs/{treeID}/{cid}', [AdminController::class, 'printSubView']);
        Route::get('/subs/{treeID}/{cid}',  [AdminController::class, 'printSubView']);

    });

});
