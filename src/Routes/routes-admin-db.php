<?php
/**
  * routes-admin-db.php registers the admin paths used for
  * managing Survloop database structures.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

Route::get(
    '/db/{database}', 
    $path . 'Admin\\AdminDBController@adminPrintFullDBPublic'
);

Route::get( '/dashboard/db', [
    'uses'       => $path . 'Admin\\AdminDBController@index', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/all', [
    'uses'       => $path . 'Admin\\AdminDBController@full', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/field-matrix', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldMatrix', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/addTable', [
    'uses'       => $path . 'Admin\\AdminDBController@addTable', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/addTable', [
    'uses'       => $path . 'Admin\\AdminDBController@addTable', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/sortTable', [
    'uses'       => $path . 'Admin\\AdminDBController@tblSort', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/table/{tblName}/edit', [
    'uses'       => $path . 'Admin\\AdminDBController@editTable', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/table/{tblName}/edit', [
    'uses'       => $path . 'Admin\\AdminDBController@editTable', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/table/{tblName}/sort', [
    'uses'       => $path . 'Admin\\AdminDBController@fldSort', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/table/{tblName}', [
    'uses'       => $path . 'Admin\\AdminDBController@viewTable', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/field/{tblAbbr}/{FldName}', [
    'uses'       => $path . 'Admin\\AdminDBController@editField', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/field/{tblAbbr}/{FldName}', [
    'uses'       => $path . 'Admin\\AdminDBController@editField', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/field/{tblAbbr}', [
    'uses'       => $path . 'Admin\\AdminDBController@addTableFld', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/field/{tblAbbr}', [
    'uses'       => $path . 'Admin\\AdminDBController@addTableFld', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/ajax-field/{FldID}', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldAjax', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/fieldDescs', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldDescs', 
    'middleware' => ['auth']
]);
Route::get( '/dashboard/db/fieldDescs', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldDescs', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/ajax/tblFldSelT/{rT}', [
    'uses' => $path . 'Admin\\AdminDBController@tblSelector'
]);

Route::get( '/dashboard/db/ajax/tblFldSelF/{rF}', [
    'uses' => $path . 'Admin\\AdminDBController@fldSelector'
]);

Route::get( '/dashboard/db/ajax/getSetFlds/{rSet}', [
    'uses' => $path . 'Admin\\AdminDBController@getSetFlds'
]);

Route::get( '/dashboard/db/ajax/getSetFldVals/{FldID}', [
    'uses' => $path . 'Admin\\AdminDBController@getSetFldVals'
]);

Route::post('/dashboard/db/fieldXML/save', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldXMLsave', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/fieldXML', [
    'uses'       => $path . 'Admin\\AdminDBController@fieldXML', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/definitions', [
    'uses'       => $path . 'Admin\\AdminDBController@definitions', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/definitions/add/{subset}', [
    'uses'       => $path . 'Admin\\AdminDBController@defAdd', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/definitions/add', [
    'uses'       => $path . 'Admin\\AdminDBController@defAdd', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/definitions/edit/{defID}', [
    'uses'       => $path . 'Admin\\AdminDBController@defEdit', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/definitions/add-sub/{subset}', [
    'uses'       => $path . 'Admin\\AdminDBController@defAdd', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/definitions/add-sub', [
    'uses'       => $path . 'Admin\\AdminDBController@defAdd', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/definitions/edit-sub/{defID}', [
    'uses'       => $path . 'Admin\\AdminDBController@defEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/definitions/sort/{subset}', [
    'uses'       => $path . 'Admin\\AdminDBController@defSort', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/bus-rules', [
    'uses'       => $path . 'Admin\\AdminDBController@businessRules', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/bus-rules/add', [
    'uses'       => $path . 'Admin\\AdminDBController@ruleAdd', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/bus-rules/add', [
    'uses'       => $path . 'Admin\\AdminDBController@ruleAdd', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/bus-rules/edit/{ruleID}', [
    'uses'       => $path . 'Admin\\AdminDBController@ruleEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/bus-rules/edit/{ruleID}',    [
    'uses'       => $path . 'Admin\\AdminDBController@ruleEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/diagrams', [
    'uses'       => $path . 'Admin\\AdminDBController@diagrams', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/network-map', [
    'uses'       => $path . 'Admin\\AdminDBController@networkMap', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/workflows', [
    'uses'       => $path . 'Admin\\AdminTreeController@workflows', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/conds', [
    'uses'       => $path . 'Admin\\AdminTreeController@conditions', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/conds', [
    'uses'       => $path . 'Admin\\AdminTreeController@conditions', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/conds/add', [
    'uses'       => $path . 'Admin\\AdminTreeController@condAdd', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/conds/add', [
    'uses'       => $path . 'Admin\\AdminTreeController@condAdd', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/conds/edit/{cid}', [
    'uses'       => $path . 'Admin\\AdminTreeController@condEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/conds/edit/{cid}', [
    'uses'       => $path . 'Admin\\AdminTreeController@condEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/export', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printExport', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/export/laravel', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printExportLaravel', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/export/laravel/table-model/{tbl}', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@refreshTableModel', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/install', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@autoInstallDatabase', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/install', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@autoInstallDatabase', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/export/dump', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@exportDump', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/sl/export', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printExportPackage', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/sl/export/laravel', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printExportPackageLaravel', 
    'middleware' => ['auth']
]);

Route::post( '/dashboard/db/import', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printImport', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/import', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printImport', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/tbl-raw', [
    'uses'       => $path . 'Admin\\AdminDatabaseInstall@printRawTable', 
    'middleware' => ['auth']
]);


Route::get( '/dashboard/db/switch/{dbID}', [
    'uses'       => $path . 'Admin\\AdminDBController@switchDB', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/switch', [
    'uses'       => $path . 'Admin\\AdminDBController@switchDB', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/db/new', [
    'uses'       => $path . 'Admin\\AdminTreeController@newDB', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/db/new', [
    'uses'       => $path . 'Admin\\AdminTreeController@newDB', 
    'middleware' => ['auth']
]);
