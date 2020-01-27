<!-- resources/views/vender/survloop/admin/tree/trees.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="row">
    <div class="col-xl-7">
    
        <div class="slCard nodeWrap">
        <h2><i class="fa fa-snowflake-o"></i> Surveys & Forms</h2>
        <div class="slGrey pB10">
            Surveys can be one or countless pages long. At their core, 
            they are branching trees of possible user experiences. 
            Here you can edit or create new surveys.
        </div>
        <table class="table table-striped">
        
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%3 > 0 
                && $tree->tree_opts%7 == 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0
                && $tree->tree_opts%43 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%3 > 0 
                && $tree->tree_opts%7 > 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0
                && $tree->tree_opts%43 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        
        @if ($GLOBALS["SL"]->sysHas('volunteers'))
            @forelse ($myTrees as $tree)
                @if ($tree->tree_opts%17 == 0 
                    && $tree->tree_opts%7 == 0) 
                    {!! view(
                        'vendor.survloop.admin.tree.trees-row', 
                        [ "tree" => $tree ]
                    )->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myTrees as $tree)
                @if ($tree->tree_opts%17 == 0 
                    && $tree->tree_opts%7 > 0) 
                    {!! view(
                        'vendor.survloop.admin.tree.trees-row', 
                        [ "tree" => $tree ]
                    )->render() !!}
                @endif
            @empty @endforelse
        @endif
        
        @if ($GLOBALS["SL"]->sysHas('partners'))
            @forelse ($myTrees as $tree)
                @if ($tree->tree_opts%41 == 0 
                    && $tree->tree_opts%7 == 0 
                    && $tree->tree_opts%17 > 0) 
                    {!! view(
                        'vendor.survloop.admin.tree.trees-row', 
                        [ "tree" => $tree ]
                    )->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myTrees as $tree)
                @if ($tree->tree_opts%41 == 0 
                    && $tree->tree_opts%7 > 0 
                    && $tree->tree_opts%17 > 0) 
                    {!! view(
                        'vendor.survloop.admin.tree.trees-row', 
                        [ "tree" => $tree ]
                    )->render() !!}
                @endif
            @empty @endforelse
        @endif
        
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%43 == 0 
                && $tree->tree_opts%7 == 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%43 == 0 
                && $tree->tree_opts%7 > 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%3 == 0 
                && $tree->tree_opts%7 == 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0
                && $tree->tree_opts%43 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myTrees as $tree)
            @if ($tree->tree_opts%3 == 0 
                && $tree->tree_opts%7 > 0 
                && $tree->tree_opts%17 > 0 
                && $tree->tree_opts%41 > 0
                && $tree->tree_opts%43 > 0) 
                {!! view(
                    'vendor.survloop.admin.tree.trees-row', 
                    [ "tree" => $tree ]
                )->render() !!}
            @endif
        @empty @endforelse
        </table>
        </div>
        
    </div><div class="col-xl-5">
    
        <div class="slCard nodeWrap slGrey">
            {!! view('vendor.survloop.admin.tree.inc-legend-perms')->render() !!}
        </div>
        
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <div class="slCard nodeWrap slGrey">
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
                    <i class="fa fa-eye" aria-hidden="true"></i> Admin-Only Survey</label>
                <label class="disBlo"><input type="checkbox" name="pageStfOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                    else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-key" aria-hidden="true"></i> Staff Survey</label>
            @if ($GLOBALS["SL"]->sysHas('partners'))
                <label class="disBlo mB5"><input type="checkbox" name="pagePrtOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                    else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-university" aria-hidden="true"></i> Partners Survey</label>
            @endif
            @if ($GLOBALS["SL"]->sysHas('volunteers'))
                <label class="disBlo mB5"><input type="checkbox" name="pageVolOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                    else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer Survey</label>
            @endif
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
        </div>
        
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection