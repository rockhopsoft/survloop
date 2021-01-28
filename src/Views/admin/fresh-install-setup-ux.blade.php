<!-- resources/views/vendor/survloop/admin/fresh-install-setup-ux.blade.php -->

@extends('vendor.survloop.master')
@section('content')
<center><div id="skinnySurv" class="treeWrapForm">

<div class="pT30">
    <h1 class="slBlueDark">Create Your First Survey</h1>
    <p>
    Each survey/form can be a simple series of questions 
    or an entire series of interactions that can be customized 
    for each visitor like a "choose your own adventure."
    </p>
    <p>
    Some databases collect information from more than one person, 
    at more than one time. But for now, please focus on the data 
    you want to collect when visitors land on your main website.
    </p>
</div>

<form name="mainPageForm" method="POST" 
    @if ($isFresh) action="/fresh/survey" 
    @else action="/dashboard/tree/new" 
    @endif >
<input type="hidden" name="freshSub" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<div class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">
        <label for="nameID">
            <b>Survey Name</b>
            <div class="subNote">
                e.g. <i>Main Data Intake</i>
            </div>
        </label>
    </div>
    <div class="nFld">
        <input id="TreeNameID" name="TreeName" 
            type="text" class="form-control" autocomplete="off"
            @if ($isFresh && isset($GLOBALS['SL']->sysOpts["site-name"])) 
                value="{{ $GLOBALS['SL']->sysOpts["site-name"] }} Main" 
            @elseif (isset($GLOBALS['SL']->dbRow->db_name)) 
                value="{{ $GLOBALS['SL']->dbRow->db_name }} Main" 
            @endif >
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">
        <label for="nameID">
            <b>Describe This Survey</b>
            <div class="subNote">
                e.g. <i>Visitors can design their own database.</i>
            </div>
        </label>
    </div>
    <div class="nFld">
        <input id="TreeDescID" name="TreeDesc" 
            type="text" class="form-control" autocomplete="off">
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">
        <label for="nameID">
            <b>Core Data Table Name</b>
            <p>
            Please create your first data table that will store the 
            <b>core records</b> of your database. These are the 
            backbone to which most other information will be related.
            </p>
            <div class="subNote">
                e.g. <i>Surveys, Submissions, Orders, Signups, 
                Inquiries, Audits, Reviews, Penguins,</i> or anything
            </div>
        </label>
    </div>
    <div class="nFld">
        <input id="TreeTableID" name="TreeTable" 
            type="text" class="form-control" autocomplete="off">
    </div>
    <div class="nodeHalfGap"></div>
</div>

<center><input type="submit" value="Create Experience"
    class="btn btn-lg btn-primary mT20"></center>

</form>

<div class="disNon">
    <iframe src="/dashboard/settings?refresh=1"></iframe>
</div>

</div></center>
<div class="p30"><br /></div>

@endsection
