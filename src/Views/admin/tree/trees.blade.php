<!-- Stored in resources/views/vender/survloop/admin/tree/trees.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h2><i class="fa fa-newspaper-o"></i> Surveys / Forms</h2>
        <div class="slGrey pB10">
            Surveys can be one or countless pages long. At their core, they are branching trees of possible user
            experiences. Here you can edit or create new surveys.
        </div>
        <table class="table table-striped">
        
        @forelse ($myTrees as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%7 == 0) 
                {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myTrees as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%7 > 0) 
                {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        
        @forelse ($myTrees as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 == 0) 
                {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myTrees as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 > 0) 
                {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        
        @if (isset($GLOBALS["SL"]->sysOpts['has-volunteers']) && intVal($GLOBALS["SL"]->sysOpts['has-volunteers']) == 1)
            @forelse ($myTrees as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 == 0) 
                    {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty
            @endforelse
            @forelse ($myTrees as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 > 0) 
                    {!! view('vendor.survloop.admin.tree.trees-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty
            @endforelse
        @endif
        
        </table>
        
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <form name="mainPageForm" method="post" action="/dashboard/surveys/list">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="sub" value="1">
        <div id="newTreeForm" class="row2 p20 mT20 mB20">
            <a id="hidivBtnNewTree" class="hidivBtn" href="javascript:;"
                ><h3 class="m0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create New Survey</h3></a>
            <div id="hidivNewTree" class="disNon mT20">
                <label class="disBlo"><input type="checkbox" name="pageAdmOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                    else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-key" aria-hidden="true"></i> Admin-Only Page</label>
                <label class="disBlo mB5"><input type="checkbox" name="pageVolOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                    else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer Page</label>
                <label for="newTreeNameID" class="mT10"><b>New Survey Title:</b></label>
                <input type="text" name="newTreeName" id="newTreeNameID" class="form-control" value="" 
                    autocomplete="off" onBlur="slugOnBlur(this, 'newTreeSlugID');">
                <div class="p10"></div>
                <label for="newTreeSlugID"><b>New Survey URL:</b><br />{{ $GLOBALS['SL']->sysOpts["app-url"] 
                    }}/<div id="isNewAdmPag" class="disNon">dashboard/</div>start/</label>
                <input type="text" name="newTreeSlug" id="newTreeSlugID" class="form-control" value="" 
                    autocomplete="off">
                <div class="p10"></div>
                <input type="submit" class="btn btn-lg btn-primary" value="Create New Survey">
                </form>
            </div>
        </div>
        <div class="p10"></div>
        
        <div class="slGrey">
            <div class="mB5"><u>Permissions</u></div>
            <div class="mB5"><i class="fa fa-key mR5" aria-hidden="true"></i> Admin-Only Survey</div>
            <div class="mB5"><i class="fa fa-hand-rock-o mR5" aria-hidden="true"></i> Volunteer Survey</div>
        </div>
    
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection