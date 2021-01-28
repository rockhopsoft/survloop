<!-- resources/views/vendor/survloop/admin/fresh-install-setup-db.blade.php -->

@extends('vendor.survloop.master')
@section('content')
<center><div id="skinnySurv" class="treeWrapForm">

<div class="pT30">
    <h1 class="slBlueDark">Create Your Core Database</h1>
    <p>
        Describe the data you want to organize, collect, and 
        share using Survloop. These system descriptions are 
        not intended for a general audience. These are for 
        internal use in documenting your database as you build it.
    </p>
    <!--
    <p>Don't worry, you can keep tweaking your entire database design forever.</p>
    -->
</div>

<form name="mainPageForm" method="POST" 
    @if ($isFresh) action="/fresh/database" 
    @else action="/dashboard/db/new" 
    @endif >
<input type="hidden" name="freshSub" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<!---
<div class="row">
    <div class="col-12">
        <div class="nodeWrap">
            <div class="nodeHalfGap"></div>
            <div class="nPrompt">
                <label for="SiteNameID">
                    <b>Site Name</b>
                    <div class="subNote">
                        e.g. My Survloop Project
                    </div>
                </label>
            </div>
            <div class="nFld">
                <input id="SiteNameID" name="SiteName" 
                    type="text" class="form-control"
                    autocomplete="off">
            </div>
            <div class="nodeHalfGap"></div>
        </div>
    </div>
</div>
--->

<div class="row">
    <div class="col-6">
        <div class="nodeWrap">
            <div class="nodeHalfGap"></div>
            <div class="nPrompt">
                <label for="DbNameID">
                    <b>Database Name</b>
                    <div class="subNote">
                        e.g. <i>My Data Project</i>
                    </div>
                </label>
            </div>
            <div class="nFld">
                <input id="DbNameID" name="DbName" 
                    type="text" class="form-control"
                    autocomplete="off">
            </div>
            <div class="nodeHalfGap"></div>
        </div>
    </div>
    <div class="col-6">
        <div class="nodeWrap">
            <div class="nodeHalfGap"></div>
            <div class="nPrompt">
                <label for="DbPrefixID">
                    <b>Data Table Prefix</b>
                    <div class="subNote">
                        e.g. <i>mdp</i>
                    </div>
                </label>
            </div>
            <div class="nFld">
                <input id="DbPrefixID" name="DbPrefix" 
                    type="text" class="form-control"
                    autocomplete="off">
            </div>
            <div class="nodeHalfGap"></div>
        </div>
    </div>
</div>

<div class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">
        <label for="DbDescID">
            <b>Tag Line</b> for your database/system
            <div class="subNote">
                e.g. <i>All Our Data Are Belong</i>
            </div>
        </label>
    </div>
    <div class="nFld">
        <input id="DbDescID" name="DbDesc" 
            type="text" class="form-control" autocomplete="off">
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">
        <label for="DbMissionID">
            <b>Mission Statement</b> for your project
            <div class="subNote">
                e.g. <i>Empower your complex databases...</i>
            </div>
        </label>
    </div>
    <div class="nFld">
        <textarea id="DbMissionID" name="DbMission" 
            class="form-control" autocomplete="off"></textarea>
    </div>
    <div class="nodeHalfGap"></div>
</div>

<center><input type="submit" value="Create Database"
    class="btn btn-xl btn-primary mT20"></center>

<div class="nodeGap"></div>

</form>

</div></center>
<div class="p30"><br /></div>

@endsection
