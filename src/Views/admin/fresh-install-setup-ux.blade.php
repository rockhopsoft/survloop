<!-- resources/views/vendor/survloop/admin/fresh-install-setup-ux.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="jumbotron"><center>
<h1>Create Your First <span class="slBlueDark">Experience</span>!</h1>
<p>
A <span class="slBlueDark">User Experience</span> is like one survey, a simple series of questions, <br />
or an entire series of interactions which must be<br />customized for every special snowflake visitor.
</p>
<p>
<span class="slGrey fPerc66">Some databases collect information from more than one person, at more than one time. 
But for now, <br />please focus on the data you want to collect when visitors land on your main website.</span>
</p>
</center></div>

<form method="POST" @if ($isFresh) action="/fresh/user-experience" @else action="/dashboard/tree/new" @endif >
<input type="hidden" name="freshSub" value="1">
{!! csrf_field() !!}
<center><div class="halfPageWidth pT20">

<div class="nodeWrap">
    <div class="nPrompt"><label for="nameID">
        <b><span class="slBlueDark">User Experience</span> Name</b>: 
        <span class="slGrey fPerc66">(eg. "SurvLoop Main")</span>
    </label></div>
    <div class="nFld"><input id="TreeNameID" name="TreeName" type="text" class="form-control"
        @if ($isFresh && isset($GLOBALS['SL']->sysOpts["site-name"])) 
            value="{{ $GLOBALS['SL']->sysOpts["site-name"] }} Main" 
        @elseif (isset($GLOBALS['SL']->dbRow->DbName)) 
            value="{{ $GLOBALS['SL']->dbRow->DbName }} Main" 
        @endif ></div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="nameID"><b>Describe This Experience</b>: <span class="slGrey fPerc66">(eg. "Visitors can design their own database.")</span></label></div>
    <div class="nFld"><input id="TreeDescID" name="TreeDesc" type="text" class="form-control"></div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="nameID">
    <b><span class="slBlueDark">Core Data Table</span> Name</b>:<br />
    Please create a table that will store the <b>core records</b> of your database. 
    These are the backbone which most other information will be related to. 
    <div class="slGrey fPerc66">(eg. "Surveys", "Submissions", "Orders", "Signups", "Inquiries", "Complaints", 
        "Audits", "Annual Compliance Reviews", "Studies", "Penguins", "Memes", "Imaginary Things")</div>
    </label></div>
    <div class="nFld"><input id="TreeTableID" name="TreeTable" type="text" class="form-control"></div>
</div>

<div class="nodeGap"></div>

<center><input type="submit" class="btn btn-xl btn-primary mT20" value="Create Experience"></center>

<div class="nodeGap"></div>
</form></div></center>

@endsection
