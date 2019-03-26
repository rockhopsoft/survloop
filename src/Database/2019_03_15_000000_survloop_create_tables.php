<?php 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SurvLoopCreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
    	Schema::create('SL_Databases', function(Blueprint $table)
		{
			$table->increments('DbID');
			$table->integer('DbUser')->unsigned()->nullable();
			$table->string('DbPrefix', 25)->nullable();
			$table->string('DbName')->nullable();
			$table->longText('DbDesc')->nullable();
			$table->longText('DbMission')->nullable();
			$table->integer('DbOpts')->default('1')->nullable();
			$table->integer('DbTables')->default('0')->nullable();
			$table->integer('DbFields')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Tables', function(Blueprint $table)
		{
			$table->increments('TblID');
			$table->integer('TblDatabase')->unsigned()->nullable();
			$table->string('TblAbbr')->nullable();
			$table->string('TblName')->nullable();
			$table->string('TblEng')->nullable();
			$table->longText('TblDesc')->nullable();
			$table->longText('TblNotes')->nullable();
			$table->string('TblType', 25)->default('Data')->nullable();
			$table->string('TblGroup', 50)->nullable();
			$table->integer('TblOrd')->default('0')->nullable();
			$table->integer('TblOpts')->default('1')->nullable();
			$table->integer('TblExtend')->unsigned()->default('0')->nullable();
			$table->integer('TblNumFields')->default('0')->nullable();
			$table->integer('TblNumForeignKeys')->default('0')->nullable();
			$table->integer('TblNumForeignIn')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Fields', function(Blueprint $table)
		{
			$table->increments('FldID');
			$table->integer('FldDatabase')->unsigned()->nullable();
			$table->integer('FldTable')->unsigned()->nullable();
			$table->integer('FldOrd')->default('0')->nullable();
			$table->string('FldSpecType', 10)->default('Unique')->nullable();
			$table->integer('FldSpecSource')->default('-3')->nullable();
			$table->string('FldName')->nullable();
			$table->string('FldEng')->nullable();
			$table->string('FldAlias')->nullable();
			$table->longText('FldDesc')->nullable();
			$table->longText('FldNotes')->nullable();
			$table->integer('FldForeignTable')->default('-3')->nullable();
			$table->string('FldForeignMin', 11)->default('1')->nullable();
			$table->string('FldForeignMax', 11)->default('1')->nullable();
			$table->string('FldForeign2Min', 11)->default('1')->nullable();
			$table->string('FldForeign2Max', 11)->default('1')->nullable();
			$table->longText('FldValues')->nullable();
			$table->string('FldDefault')->nullable();
			$table->boolean('FldIsIndex')->default('0')->nullable();
			$table->string('FldType', 25)->default('VARCHAR')->nullable();
			$table->string('FldDataType', 20)->default('Alphanumeric')->nullable();
			$table->integer('FldDataLength')->nullable();
			$table->integer('FldDataDecimals')->default('0')->nullable();
			$table->string('FldCharSupport')->default(',Letters,Numbers,Keyboard,Special,')->nullable();
			$table->string('FldInputMask')->nullable();
			$table->string('FldDisplayFormat')->nullable();
			$table->string('FldKeyType')->default(',Non,')->nullable();
			$table->string('FldKeyStruct', 10)->nullable();
			$table->string('FldEditRule', 10)->default('LateAllow')->nullable();
			$table->boolean('FldUnique')->default('0')->nullable();
			$table->boolean('FldNullSupport')->default('1')->nullable();
			$table->string('FldValuesEnteredBy', 10)->default('User')->nullable();
			$table->boolean('FldRequired')->default('0')->nullable();
			$table->integer('FldCompareSame')->default('1')->nullable();
			$table->integer('FldCompareOther')->default('1')->nullable();
			$table->integer('FldCompareValue')->default('1')->nullable();
			$table->integer('FldOperateSame')->default('1')->nullable();
			$table->integer('FldOperateOther')->default('1')->nullable();
			$table->integer('FldOperateValue')->default('1')->nullable();
			$table->integer('FldOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Definitions', function(Blueprint $table)
		{
			$table->increments('DefID');
			$table->integer('DefDatabase')->unsigned()->nullable();
			$table->string('DefSet', 20)->default('Value Ranges')->nullable();
			$table->string('DefSubset', 50)->nullable();
			$table->integer('DefOrder')->default('0')->nullable();
			$table->boolean('DefIsActive')->default('1')->nullable();
			$table->string('DefValue')->nullable();
			$table->longText('DefDescription')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_BusRules', function(Blueprint $table)
		{
			$table->increments('RuleID');
			$table->integer('RuleDatabase')->unsigned()->nullable();
			$table->longText('RuleStatement')->nullable();
			$table->longText('RuleConstraint')->nullable();
			$table->string('RuleTables')->default(',')->nullable();
			$table->string('RuleFields')->default(',')->nullable();
			$table->boolean('RuleIsAppOrient')->default('1')->nullable();
			$table->boolean('RuleIsRelation')->default('1')->nullable();
			$table->string('RuleTestOn', 10)->default('Insert')->nullable();
			$table->integer('RulePhys')->default('1')->nullable();
			$table->integer('RuleLogic')->default('1')->nullable();
			$table->integer('RuleRel')->default('1')->nullable();
			$table->longText('RuleAction')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Tree', function(Blueprint $table)
		{
			$table->increments('TreeID');
			$table->integer('TreeDatabase')->unsigned()->nullable();
			$table->integer('TreeUser')->unsigned()->nullable();
			$table->string('TreeType', 30)->default('Primary Public')->nullable();
			$table->string('TreeName')->nullable();
			$table->longText('TreeDesc')->nullable();
			$table->string('TreeSlug')->nullable();
			$table->integer('TreeRoot')->unsigned()->nullable();
			$table->integer('TreeFirstPage')->unsigned()->nullable();
			$table->integer('TreeLastPage')->unsigned()->nullable();
			$table->integer('TreeCoreTable')->unsigned()->nullable();
			$table->integer('TreeOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Node', function(Blueprint $table)
		{
			$table->increments('NodeID');
			$table->integer('NodeTree')->unsigned()->nullable();
			$table->integer('NodeParentID')->default('-3')->nullable();
			$table->integer('NodeParentOrder')->default('0')->nullable();
			$table->string('NodeType', 25)->nullable();
			$table->longText('NodePromptText')->nullable();
			$table->longText('NodePromptNotes')->nullable();
			$table->longText('NodePromptAfter')->nullable();
			$table->longText('NodeInternalNotes')->nullable();
			$table->string('NodeResponseSet', 50)->nullable();
			$table->string('NodeDefault')->nullable();
			$table->string('NodeDataBranch', 100)->nullable();
			$table->string('NodeDataStore', 100)->nullable();
			$table->string('NodeTextSuggest', 100)->nullable();
			$table->integer('NodeCharLimit')->default('0')->nullable();
			$table->integer('NodeLikes')->default('0')->nullable();
			$table->integer('NodeDislikes')->default('0')->nullable();
			$table->integer('NodeOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_NodeResponses', function(Blueprint $table)
		{
			$table->increments('NodeResID');
			$table->integer('NodeResNode')->unsigned()->nullable();
			$table->integer('NodeResOrd')->default('0')->nullable();
			$table->string('NodeResEng')->nullable();
			$table->string('NodeResValue')->nullable();
			$table->integer('NodeResShowKids')->default('0')->nullable();
			$table->integer('NodeResMutEx')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Conditions', function(Blueprint $table)
		{
			$table->increments('CondID');
			$table->integer('CondDatabase')->unsigned()->nullable();
			$table->string('CondTag', 100)->nullable();
			$table->longText('CondDesc')->nullable();
			$table->string('CondOperator', 50)->default('{')->nullable();
			$table->string('CondOperDeet', 100)->nullable();
			$table->integer('CondField')->unsigned()->nullable();
			$table->integer('CondTable')->unsigned()->nullable();
			$table->integer('CondLoop')->unsigned()->nullable();
			$table->integer('CondOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ConditionsVals', function(Blueprint $table)
		{
			$table->increments('CondValID');
			$table->integer('CondValCondID')->unsigned()->nullable();
			$table->string('CondValValue')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ConditionsNodes', function(Blueprint $table)
		{
			$table->increments('CondNodeID');
			$table->integer('CondNodeCondID')->unsigned()->nullable();
			$table->integer('CondNodeNodeID')->unsigned()->nullable();
			$table->integer('CondNodeLoopID')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ConditionsArticles', function(Blueprint $table)
		{
			$table->increments('ArticleID');
			$table->integer('ArticleCondID')->unsigned()->nullable();
			$table->string('ArticleURL')->nullable();
			$table->string('ArticleTitle')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DataLoop', function(Blueprint $table)
		{
			$table->increments('DataLoopID');
			$table->integer('DataLoopTree')->unsigned()->nullable();
			$table->integer('DataLoopRoot')->unsigned()->nullable();
			$table->string('DataLoopPlural', 50)->nullable();
			$table->string('DataLoopSingular', 50)->nullable();
			$table->string('DataLoopTable', 100)->nullable();
			$table->string('DataLoopSortFld', 100)->nullable();
			$table->string('DataLoopDoneFld', 100)->nullable();
			$table->integer('DataLoopMaxLimit')->default('0')->nullable();
			$table->integer('DataLoopWarnLimit')->default('0')->nullable();
			$table->integer('DataLoopMinLimit')->default('0')->nullable();
			$table->boolean('DataLoopIsStep')->default('0')->nullable();
			$table->boolean('DataLoopAutoGen')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DataSubsets', function(Blueprint $table)
		{
			$table->increments('DataSubID');
			$table->integer('DataSubTree')->unsigned()->nullable();
			$table->string('DataSubTbl', 100)->nullable();
			$table->string('DataSubTblLnk', 100)->nullable();
			$table->string('DataSubSubTbl', 50)->nullable();
			$table->string('DataSubSubLnk', 100)->nullable();
			$table->boolean('DataSubAutoGen')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DataHelpers', function(Blueprint $table)
		{
			$table->increments('DataHelpID');
			$table->integer('DataHelpTree')->unsigned()->nullable();
			$table->string('DataHelpParentTable', 50)->nullable();
			$table->string('DataHelpTable', 50)->nullable();
			$table->string('DataHelpKeyField', 50)->nullable();
			$table->string('DataHelpValueField', 50)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DataLinks', function(Blueprint $table)
		{
			$table->increments('DataLinkID');
			$table->integer('DataLinkTree')->unsigned()->nullable();
			$table->integer('DataLinkTable')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Images', function(Blueprint $table)
		{
			$table->increments('ImgID');
			$table->integer('ImgDatabaseID')->unsigned()->nullable();
			$table->integer('ImgUserID')->unsigned()->nullable();
			$table->string('ImgFileOrig')->nullable();
			$table->string('ImgFileLoc')->nullable();
			$table->string('ImgFullFilename')->nullable();
			$table->string('ImgTitle')->nullable();
			$table->string('ImgCredit')->nullable();
			$table->string('ImgCreditUrl')->nullable();
			$table->integer('ImgNodeID')->unsigned()->nullable();
			$table->string('ImgType', 10)->nullable();
			$table->integer('ImgFileSize')->nullable();
			$table->integer('ImgWidth')->nullable();
			$table->integer('ImgHeight')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SearchRecDump', function(Blueprint $table)
		{
			$table->increments('SchRecDmpID');
			$table->integer('SchRecDmpTreeID')->unsigned()->nullable();
			$table->integer('SchRecDmpRecID')->nullable();
			$table->longText('SchRecDmpDump')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DesignTweaks', function(Blueprint $table)
		{
			$table->increments('TweakID');
			$table->string('TweakVersionAB')->nullable();
			$table->string('TweakVersionAB')->nullable();
			$table->integer('TweakSubmissionProgress')->unsigned()->nullable();
			$table->integer('TweakSubmissionProgress')->nullable();
			$table->string('TweakIPaddy')->nullable();
			$table->string('TweakIPaddy')->nullable();
			$table->string('TweakTreeVersion')->nullable();
			$table->string('TweakTreeVersion')->nullable();
			$table->string('TweakUniqueStr')->nullable();
			$table->string('TweakUniqueStr')->nullable();
			$table->integer('TweakUserID')->unsigned()->nullable();
			$table->integer('TweakUserID')->unsigned()->nullable();
			$table->string('TweakIsMobile')->nullable();
			$table->string('TweakIsMobile')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Sess', function(Blueprint $table)
		{
			$table->increments('SessID');
			$table->integer('SessUserID')->unsigned()->nullable();
			$table->integer('SessTree')->unsigned()->nullable();
			$table->integer('SessCoreID')->unsigned()->nullable();
			$table->boolean('SessIsActive')->nullable();
			$table->integer('SessCurrNode')->unsigned()->nullable();
			$table->integer('SessLoopRootJustLeft')->unsigned()->nullable();
			$table->integer('SessAfterJumpTo')->unsigned()->nullable();
			$table->boolean('SessIsMobile')->nullable();
			$table->string('SessBrowser', 255)->nullable();
			$table->string('SessIP')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessLoops', function(Blueprint $table)
		{
			$table->increments('SessLoopID');
			$table->integer('SessLoopSessID')->unsigned()->nullable();
			$table->string('SessLoopName', 50)->nullable();
			$table->integer('SessLoopItemID')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_NodeSavesPage', function(Blueprint $table)
		{
			$table->increments('PageSaveID');
			$table->integer('PageSaveSession')->unsigned()->nullable();
			$table->integer('PageSaveNode')->unsigned()->nullable();
			$table->integer('PageSaveLoopItemID')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_NodeSaves', function(Blueprint $table)
		{
			$table->increments('NodeSaveID');
			$table->integer('NodeSaveSession')->unsigned()->nullable();
			$table->integer('NodeSaveLoopItemID')->nullable();
			$table->integer('NodeSaveNode')->unsigned()->nullable();
			$table->string('NodeSaveVersionAB')->nullable();
			$table->string('NodeSaveTblFld', 100)->nullable();
			$table->longText('NodeSaveNewVal')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessEmojis', function(Blueprint $table)
		{
			$table->increments('SessEmoID');
			$table->integer('SessEmoUserID')->unsigned()->nullable();
			$table->integer('SessEmoTreeID')->unsigned()->nullable();
			$table->integer('SessEmoRecID')->nullable();
			$table->integer('SessEmoDefID')->unsigned()->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessSite', function(Blueprint $table)
		{
			$table->increments('SiteSessID');
			$table->string('SiteSessIPaddy')->nullable();
			$table->longText('SiteSessUserID')->nullable();
			$table->boolean('SiteSessIsMobile')->nullable();
			$table->string('SiteSessBrowser', 255)->nullable();
			$table->integer('SiteSessZoomPref')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessPage', function(Blueprint $table)
		{
			$table->increments('SessPageID');
			$table->integer('SessPageSessID')->unsigned()->nullable();
			$table->string('SessPageURL')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Tokens', function(Blueprint $table)
		{
			$table->increments('TokID');
			$table->string('TokType', 20)->nullable();
			$table->integer('TokUserID')->unsigned()->nullable();
			$table->integer('TokTreeID')->unsigned()->nullable();
			$table->integer('TokCoreID')->nullable();
			$table->string('TokTokToken', 255)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_UsersRoles', function(Blueprint $table)
		{
			$table->increments('RoleUserID');
			$table->integer('RoleUserUID')->unsigned()->nullable();
			$table->integer('RoleUserRID')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Contact', function(Blueprint $table)
		{
			$table->increments('ContID');
			$table->string('ContType')->nullable();
			$table->string('ContFlag')->default('Unread')->nullable();
			$table->string('ContEmail')->nullable();
			$table->string('ContSubject')->nullable();
			$table->longText('ContBody')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Emails', function(Blueprint $table)
		{
			$table->increments('EmailID');
			$table->integer('EmailTree')->unsigned()->nullable();
			$table->string('EmailType')->nullable();
			$table->string('EmailName')->nullable();
			$table->longText('EmailSubject')->nullable();
			$table->longText('EmailBody')->nullable();
			$table->integer('EmailOpts')->default('1')->nullable();
			$table->integer('EmailTotSent')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Emailed', function(Blueprint $table)
		{
			$table->increments('EmailedID');
			$table->integer('EmailedTree')->unsigned()->nullable();
			$table->integer('EmailedRecID')->nullable();
			$table->integer('EmailedEmailID')->unsigned()->nullable();
			$table->string('EmailedTo')->nullable();
			$table->integer('EmailedToUser')->unsigned()->nullable();
			$table->integer('EmailedFromUser')->unsigned()->nullable();
			$table->string('EmailedSubject')->nullable();
			$table->longText('EmailedBody')->nullable();
			$table->integer('EmailedOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_UsersActivity', function(Blueprint $table)
		{
			$table->increments('UserActID');
			$table->integer('UserActUser')->unsigned()->nullable();
			$table->string('UserActCurrPage')->nullable();
			$table->longText('UserActVal')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_LogActions', function(Blueprint $table)
		{
			$table->increments('LogID');
			$table->integer('LogUser')->unsigned()->nullable();
			$table->integer('LogDatabase')->unsigned()->nullable();
			$table->integer('LogTable')->unsigned()->nullable();
			$table->integer('LogField')->unsigned()->nullable();
			$table->string('LogAction', 20)->nullable();
			$table->string('LogOldName')->nullable();
			$table->string('LogNewName')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Zips', function(Blueprint $table)
		{
			$table->increments('ZipID');
			$table->string('ZipZip', 10)->nullable();
			$table->string('ZipLat')->nullable();
			$table->string('ZipLong')->nullable();
			$table->string('ZipCity', 100)->nullable();
			$table->string('ZipState')->nullable();
			$table->string('ZipCounty')->nullable();
			$table->string('ZipCountry', 100)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_AddyGeo', function(Blueprint $table)
		{
			$table->increments('AdyGeoID');
			$table->string('AdyGeoAddress')->nullable();
			$table->string('AdyGeoLat')->nullable();
			$table->string('AdyGeoLong')->nullable();
			$table->timestamps();
		});
	
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
    	Schema::drop('SL_Databases');
		Schema::drop('SL_Tables');
		Schema::drop('SL_Fields');
		Schema::drop('SL_Definitions');
		Schema::drop('SL_BusRules');
		Schema::drop('SL_Tree');
		Schema::drop('SL_Node');
		Schema::drop('SL_NodeResponses');
		Schema::drop('SL_Conditions');
		Schema::drop('SL_ConditionsVals');
		Schema::drop('SL_ConditionsNodes');
		Schema::drop('SL_ConditionsArticles');
		Schema::drop('SL_DataLoop');
		Schema::drop('SL_DataSubsets');
		Schema::drop('SL_DataHelpers');
		Schema::drop('SL_DataLinks');
		Schema::drop('SL_Images');
		Schema::drop('SL_SearchRecDump');
		Schema::drop('SL_DesignTweaks');
		Schema::drop('SL_Sess');
		Schema::drop('SL_SessLoops');
		Schema::drop('SL_NodeSavesPage');
		Schema::drop('SL_NodeSaves');
		Schema::drop('SL_SessEmojis');
		Schema::drop('SL_SessSite');
		Schema::drop('SL_SessPage');
		Schema::drop('SL_Tokens');
		Schema::drop('SL_UsersRoles');
		Schema::drop('SL_Contact');
		Schema::drop('SL_Emails');
		Schema::drop('SL_Emailed');
		Schema::drop('SL_UsersActivity');
		Schema::drop('SL_LogActions');
		Schema::drop('SL_Zips');
		Schema::drop('SL_AddyGeo');
	
    }
}
