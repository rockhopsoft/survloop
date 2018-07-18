<?php 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SLCreateTables extends Migration
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
			$table->foreign('DbUser')->references('id')->on('users');
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
			$table->foreign('TblDatabase')->references('DbID')->on('SL_Databases');
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
			$table->foreign('TblExtend')->references('TblID')->on('SL_Tables');
			$table->integer('TblNumFields')->default('0')->nullable();
			$table->integer('TblNumForeignKeys')->default('0')->nullable();
			$table->integer('TblNumForeignIn')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Fields', function(Blueprint $table)
		{
			$table->increments('FldID');
			$table->integer('FldDatabase')->unsigned()->nullable();
			$table->foreign('FldDatabase')->references('DbID')->on('SL_Databases');
			$table->integer('FldTable')->unsigned()->nullable();
			$table->foreign('FldTable')->references('TblID')->on('SL_Tables');
			$table->integer('FldOrd')->default('0')->nullable();
			$table->string('FldSpecType', 10)->default('Unique')->nullable();
			$table->integer('FldSpecSource')->default('-3')->nullable();
			$table->foreign('FldSpecSource')->references('FldID')->on('SL_Fields');
			$table->string('FldName')->nullable();
			$table->string('FldEng')->nullable();
			$table->string('FldAlias')->nullable();
			$table->longText('FldDesc')->nullable();
			$table->longText('FldNotes')->nullable();
			$table->integer('FldForeignTable')->default('-3')->nullable();
			$table->foreign('FldForeignTable')->references('TblID')->on('SL_Tables');
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
			$table->foreign('DefDatabase')->references('DbID')->on('SL_Databases');
			$table->integer('DefSet')->unsigned()->default('Value Ranges')->nullable();
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
			$table->foreign('RuleDatabase')->references('DbID')->on('SL_Databases');
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
			$table->foreign('TreeDatabase')->references('DbID')->on('SL_Databases');
			$table->integer('TreeUser')->unsigned()->nullable();
			$table->foreign('TreeUser')->references('id')->on('users');
			$table->string('TreeType', 30)->default('Primary Public')->nullable();
			$table->string('TreeName')->nullable();
			$table->longText('TreeDesc')->nullable();
			$table->string('TreeSlug')->nullable();
			$table->integer('TreeRoot')->unsigned()->nullable();
			$table->foreign('TreeRoot')->references('NodeID')->on('SL_Node');
			$table->integer('TreeFirstPage')->unsigned()->nullable();
			$table->foreign('TreeFirstPage')->references('NodeID')->on('SL_Node');
			$table->integer('TreeLastPage')->unsigned()->nullable();
			$table->foreign('TreeLastPage')->references('NodeID')->on('SL_Node');
			$table->integer('TreeCoreTable')->unsigned()->nullable();
			$table->foreign('TreeCoreTable')->references('TblID')->on('SL_Tables');
			$table->integer('TreeOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Node', function(Blueprint $table)
		{
			$table->increments('NodeID');
			$table->integer('NodeTree')->unsigned()->nullable();
			$table->foreign('NodeTree')->references('TreeID')->on('SL_Tree');
			$table->integer('NodeParentID')->default('-3')->nullable();
			$table->foreign('NodeParentID')->references('NodeID')->on('SL_Node');
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
			$table->foreign('NodeResNode')->references('NodeID')->on('SL_Node');
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
			$table->foreign('CondDatabase')->references('DbID')->on('SL_Databases');
			$table->string('CondTag', 100)->nullable();
			$table->longText('CondDesc')->nullable();
			$table->string('CondOperator', 50)->default('{')->nullable();
			$table->string('CondOperDeet', 100)->nullable();
			$table->integer('CondField')->unsigned()->nullable();
			$table->foreign('CondField')->references('FldID')->on('SL_Fields');
			$table->integer('CondTable')->unsigned()->nullable();
			$table->foreign('CondTable')->references('TblID')->on('SL_Tables');
			$table->integer('CondLoop')->unsigned()->nullable();
			$table->foreign('CondLoop')->references('DataLoopID')->on('SL_DataLoop');
			$table->integer('CondOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ConditionsVals', function(Blueprint $table)
		{
			$table->increments('CondValID');
			$table->integer('CondValCondID')->unsigned()->nullable();
			$table->foreign('CondValCondID')->references('CondID')->on('SL_Conditions');
			$table->string('CondValValue')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ConditionsNodes', function(Blueprint $table)
		{
			$table->increments('CondNodeID');
			$table->integer('CondNodeCondID')->unsigned()->nullable();
			$table->foreign('CondNodeCondID')->references('CondID')->on('SL_Conditions');
			$table->integer('CondNodeNodeID')->unsigned()->nullable();
			$table->foreign('CondNodeNodeID')->references('CondNodeID')->on('SL_ConditionsNodes');
			$table->integer('CondNodeLoopID')->unsigned()->nullable();
			$table->foreign('CondNodeLoopID')->references('DataLoopID')->on('SL_DataLoop');
			$table->timestamps();
		});
		Schema::create('SL_ConditionsArticles', function(Blueprint $table)
		{
			$table->increments('ArticleID');
			$table->integer('ArticleCondID')->unsigned()->nullable();
			$table->foreign('ArticleCondID')->references('ArticleID')->on('SL_ConditionsArticles');
			$table->string('ArticleURL')->nullable();
			$table->string('ArticleTitle')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DataLoop', function(Blueprint $table)
		{
			$table->increments('DataLoopID');
			$table->integer('DataLoopTree')->unsigned()->nullable();
			$table->foreign('DataLoopTree')->references('TreeID')->on('SL_Tree');
			$table->integer('DataLoopRoot')->unsigned()->nullable();
			$table->foreign('DataLoopRoot')->references('NodeID')->on('SL_Node');
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
			$table->foreign('DataSubTree')->references('TreeID')->on('SL_Tree');
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
			$table->foreign('DataHelpTree')->references('TreeID')->on('SL_Tree');
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
			$table->foreign('DataLinkTree')->references('TreeID')->on('SL_Tree');
			$table->integer('DataLinkTable')->unsigned()->nullable();
			$table->foreign('DataLinkTable')->references('TblID')->on('SL_Tables');
			$table->timestamps();
		});
		Schema::create('SL_Images', function(Blueprint $table)
		{
			$table->increments('ImgID');
			$table->integer('ImgDatabase')->unsigned()->nullable();
			$table->foreign('ImgDatabase')->references('DbID')->on('SL_Databases');
			$table->integer('ImgUserID')->unsigned()->nullable();
			$table->foreign('ImgUserID')->references('id')->on('users');
			$table->string('ImgFileOrig')->nullable();
			$table->string('ImgFileLoc')->nullable();
			$table->string('ImgFullFilename')->nullable();
			$table->string('ImgTitle')->nullable();
			$table->string('ImgCredit')->nullable();
			$table->string('ImgCreditUrl')->nullable();
			$table->integer('ImgNodeID')->unsigned()->nullable();
			$table->foreign('ImgNodeID')->references('NodeID')->on('SL_Node');
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
			$table->foreign('SchRecDmpTreeID')->references('TreeID')->on('SL_Tree');
			$table->integer('SchRecDmpRecID')->nullable();
			$table->longText('SchRecDmpDump')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_DesignTweaks', function(Blueprint $table)
		{
			$table->increments('TweakID');
			$table->string('TweakVersionAB')->nullable();
			$table->integer('TweakSubmissionProgress')->unsigned()->nullable();
			$table->foreign('TweakSubmissionProgress')->references('NodeID')->on('SL_Node');
			$table->string('TweakIPaddy')->nullable();
			$table->string('TweakTreeVersion')->nullable();
			$table->string('TweakUniqueStr')->nullable();
			$table->integer('TweakUserID')->unsigned()->nullable();
			$table->foreign('TweakUserID')->references('id')->on('users');
			$table->string('TweakIsMobile')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Sess', function(Blueprint $table)
		{
			$table->increments('SessID');
			$table->integer('SessUserID')->unsigned()->nullable();
			$table->foreign('SessUserID')->references('id')->on('users');
			$table->integer('SessTree')->unsigned()->nullable();
			$table->foreign('SessTree')->references('TreeID')->on('SL_Tree');
			$table->integer('SessCoreID')->unsigned()->nullable();
			$table->integer('SessCurrNode')->unsigned()->nullable();
			$table->foreign('SessCurrNode')->references('NodeID')->on('SL_Node');
			$table->integer('SessLoopRootJustLeft')->unsigned()->nullable();
			$table->foreign('SessLoopRootJustLeft')->references('NodeID')->on('SL_Node');
			$table->integer('SessAfterJumpTo')->unsigned()->nullable();
			$table->foreign('SessAfterJumpTo')->references('NodeID')->on('SL_Node');
			$table->integer('SessZoomPref')->nullable();
			$table->boolean('SessIsMobile')->nullable();
			$table->string('SessBrowser', 100)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessLoops', function(Blueprint $table)
		{
			$table->increments('SessLoopID');
			$table->integer('SessLoopSessID')->unsigned()->nullable();
			$table->foreign('SessLoopSessID')->references('SessID')->on('SL_Sess');
			$table->string('SessLoopName', 50)->nullable();
			$table->integer('SessLoopItemID')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_SessEmojis', function(Blueprint $table)
		{
			$table->increments('SessEmoID');
			$table->integer('SessEmoUserID')->unsigned()->nullable();
			$table->foreign('SessEmoUserID')->references('id')->on('users');
			$table->integer('SessEmoTreeID')->unsigned()->nullable();
			$table->foreign('SessEmoTreeID')->references('TreeID')->on('SL_Tree');
			$table->integer('SessEmoRecID')->nullable();
			$table->integer('SessEmoDefID')->unsigned()->nullable();
			$table->foreign('SessEmoDefID')->references('DefID')->on('SL_Definitions');
			$table->timestamps();
		});
		Schema::create('SL_NodeSavesPage', function(Blueprint $table)
		{
			$table->increments('PageSaveID');
			$table->integer('PageSaveSession')->unsigned()->nullable();
			$table->foreign('PageSaveSession')->references('SessID')->on('SL_Sess');
			$table->integer('PageSaveNode')->unsigned()->nullable();
			$table->foreign('PageSaveNode')->references('NodeID')->on('SL_Node');
			$table->integer('PageSaveLoopItemID')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_NodeSaves', function(Blueprint $table)
		{
			$table->increments('NodeSaveID');
			$table->integer('NodeSaveSession')->unsigned()->nullable();
			$table->foreign('NodeSaveSession')->references('SessID')->on('SL_Sess');
			$table->integer('NodeSaveLoopItemID')->nullable();
			$table->integer('NodeSaveNode')->unsigned()->nullable();
			$table->foreign('NodeSaveNode')->references('NodeID')->on('SL_Node');
			$table->string('NodeSaveVersionAB')->nullable();
			$table->string('NodeSaveTblFld', 100)->nullable();
			$table->longText('NodeSaveNewVal')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Tokens', function(Blueprint $table)
		{
			$table->increments('TokID');
			$table->string('TokType', 20)->nullable();
			$table->integer('TokUserID')->unsigned()->nullable();
			$table->foreign('TokUserID')->references('id')->on('users');
			$table->integer('TokTreeID')->unsigned()->nullable();
			$table->foreign('TokTreeID')->references('TreeID')->on('SL_Tree');
			$table->integer('TokCoreID')->nullable();
			$table->string('TokTokToken', 255)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_UsersRoles', function(Blueprint $table)
		{
			$table->increments('RoleUserID');
			$table->integer('RoleUserUID')->unsigned()->nullable();
			$table->foreign('RoleUserUID')->references('id')->on('users');
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
			$table->increments('EmailsID');
			$table->integer('EmailsTree')->unsigned()->nullable();
			$table->foreign('EmailsTree')->references('TreeID')->on('SL_Tree');
			$table->string('EmailsType')->nullable();
			$table->string('EmailsName')->nullable();
			$table->longText('EmailsSubject')->nullable();
			$table->longText('EmailsBody')->nullable();
			$table->integer('EmailsOpts')->default('1')->nullable();
			$table->integer('EmailsTotSent')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Emailed', function(Blueprint $table)
		{
			$table->increments('EmailedID');
			$table->integer('EmailedTree')->unsigned()->nullable();
			$table->foreign('EmailedTree')->references('TreeID')->on('SL_Tree');
			$table->integer('EmailedRecID')->nullable();
			$table->integer('EmailedEmailID')->unsigned()->nullable();
			$table->foreign('EmailedEmailID')->references('EmailsID')->on('SL_Emails');
			$table->string('EmailedTo')->nullable();
			$table->integer('EmailedToUser')->unsigned()->nullable();
			$table->foreign('EmailedToUser')->references('id')->on('users');
			$table->integer('EmailedFromUser')->unsigned()->nullable();
			$table->foreign('EmailedFromUser')->references('id')->on('users');
			$table->string('EmailedSubject')->nullable();
			$table->longText('EmailedBody')->nullable();
			$table->integer('EmailedOpts')->default('1')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_UsersActivity', function(Blueprint $table)
		{
			$table->increments('UserActID');
			$table->integer('UserActUser')->unsigned()->nullable();
			$table->foreign('UserActUser')->references('id')->on('users');
			$table->string('UserActCurrPage')->nullable();
			$table->longText('UserActVal')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_LogActions', function(Blueprint $table)
		{
			$table->increments('LogID');
			$table->integer('LogUser')->unsigned()->nullable();
			$table->foreign('LogUser')->references('id')->on('users');
			$table->integer('LogDatabase')->unsigned()->nullable();
			$table->foreign('LogDatabase')->references('DbID')->on('SL_Databases');
			$table->integer('LogTable')->unsigned()->nullable();
			$table->foreign('LogTable')->references('TblID')->on('SL_Tables');
			$table->integer('LogField')->unsigned()->nullable();
			$table->foreign('LogField')->references('FldID')->on('SL_Fields');
			$table->string('LogAction', 20)->nullable();
			$table->string('LogOldName')->nullable();
			$table->string('LogNewName')->nullable();
			$table->timestamps();
		});
		Schema::create('SL_ZipAshrae', function(Blueprint $table)
		{
			$table->increments('AshrID');
			$table->string('AshrZone', 2)->nullable();
			$table->string('AshrState', 2)->nullable();
			$table->string('AshrCounty', 50)->nullable();
			$table->timestamps();
		});
		Schema::create('SL_Zips', function(Blueprint $table)
		{
			$table->increments('ZipID');
			$table->string('ZipZip', 10)->nullable();
			$table->string('ZipLat')->nullable();
			$table->string('ZipLong')->nullable();
			$table->string('ZipCity')->nullable();
			$table->string('ZipState', 5)->nullable();
			$table->string('ZipCounty')->nullable();
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
		Schema::drop('SL_SessEmojis');
		Schema::drop('SL_NodeSavesPage');
		Schema::drop('SL_NodeSaves');
		Schema::drop('SL_Tokens');
		Schema::drop('SL_UsersRoles');
		Schema::drop('SL_Contact');
		Schema::drop('SL_Emails');
		Schema::drop('SL_Emailed');
		Schema::drop('SL_UsersActivity');
		Schema::drop('SL_LogActions');
		Schema::drop('SL_ZipAshrae');
		Schema::drop('SL_Zips');
		
    }
}

        