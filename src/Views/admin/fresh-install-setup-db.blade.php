<!-- resources/views/vendor/survloop/admin/fresh-install-setup-db.blade.php -->

@extends('vendor.survloop.master')
@section('content')

<div class="jumbotron"><center>
<h1>Create Your <span class="slBlueDark">Database</span>!</h1>
<h4>Describe the data you want to organize, collect, and share using <b class="slBlueDark">Survloop</b>.</h4>
<p><i class="slGrey mL20">Don't worry, you can keep tweaking your entire database design forever.</i></p>
</center></div>

<form name="mainPageForm" method="POST" 
    @if ($isFresh) action="/fresh/database" @else action="/dashboard/db/new" @endif >
<input type="hidden" name="freshSub" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<center><div class="halfPageWidth pT20">

<div class="row">
    <div class="col-8">
        <div class="nodeWrap">
            <div class="nPrompt"><label for="nameID"><b>Database Name</b>: 
                <span class="slGrey fPerc66">(eg. Survloop)</span></label></div>
            <div class="nFld"><input id="DbNameID" name="DbName" type="text" class="form-control"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="nodeWrap">
            <div class="nPrompt"><label for="nameID"><b>Abbreviation</b>: 
                <span class="slGrey fPerc66">(eg. SL)</span></label></div>
            <div class="nFld"><input id="DbPrefixID" name="DbPrefix" type="text" class="form-control"></div>
        </div>
    </div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="nameID"><b>Tag Line</b> for your database/system: 
        <span class="slGrey fPerc66">(eg. "All Our Data Are Belong")</span></label></div>
    <div class="nFld"><input id="DbDescID" name="DbDesc" type="text" class="form-control"></div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="nameID"><b>Mission Statement</b> for your project: 
        <span class="slGrey fPerc66">(eg. "Empower your complex databases...")</span></label></div>
    <div class="nFld"><textarea id="DbMissionID" name="DbMission" class="form-control"></textarea></div>
</div>

<div class="nodeGap"></div>

<center><input type="submit" class="btn btn-lg btn-primary mT20" value="Create Database"></center>

<div class="nodeGap"></div>
</form></div></center>

@endsection
