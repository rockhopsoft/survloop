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
            @if ($tree->TreeOpts%3 > 0) 
                <tr>
                <td class="w70">
                    <div class="fPerc133">{{ $tree->TreeName }}</div>
                    <div class="pL5 slBlueDark">/start/{{ $tree->TreeSlug }}</div>
                    @if ($tree->TreeDesc) <div class="slGrey">{{ $tree->TreeDesc }}</div> @endif
                </td>
                <td class="w10 taR pT10 slGrey fPerc133"></td>
                <td class="w20 taR">
                    <a href="/dashboard/tree-{{ $tree->TreeID }}/map?all=1&alt=1" class="btn btn-primary mR10"
                        ><i class="fa fa-pencil" aria-hidden="true"></i></a>
                    <a href="/start/{{ $tree->TreeSlug }}" target="_blank" class="btn btn-default"
                        ><i class="fa fa-external-link" aria-hidden="true"></i></a>
                </td>
                </tr>
            @endif
        @empty
            <tr><td colspan=2 ><i>No public surveys/forms found.</i></td></tr>
        @endforelse

        @forelse ($myTrees as $tree)
            @if ($tree->TreeOpts%3 == 0) 
                <tr>
                <td class="w70">
                    <div class="fPerc133">{{ $tree->TreeName }}</div>
                    <div class="pL5 slBlueDark">/dashboard/start/{{ $tree->TreeSlug }}</div>
                    @if ($tree->TreeDesc) <div class="slGrey">{{ $tree->TreeDesc }}</div> @endif
                </td>
                <td class="w10 taR pT10 slGrey fPerc133"><i class="fa fa-key" aria-hidden="true"></i></td>
                <td class="w20 taR">
                    <a href="/dashboard/tree-{{ $tree->TreeID }}/map?all=1&alt=1" class="btn btn-primary mR10"
                        ><i class="fa fa-pencil" aria-hidden="true"></i></a>
                    <a href="/dashboard/start/{{ $tree->TreeSlug }}" target="_blank" class="btn btn-default"
                        ><i class="fa fa-external-link" aria-hidden="true"></i></a>
                </td>
                </tr>
            @endif
        @empty
            <tr><td colspan=2 ><i>No admin surveys/forms found.</i></td></tr>
        @endforelse
        
        </table>
        
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <form name="mainPageForm" method="post" action="/dashboard/trees/list">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="sub" value="1">
        <div id="newTreeForm" class="row2 p20 mT20 mB20">
            <h3 class="mT0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create A New Survey</h3>
            <label class="disBlo mT10"><input type="checkbox" name="pageAdmOnly" value="1" 
                onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                <i class="fa fa-key" aria-hidden="true"></i> Admin-Only Survey</label>
            <div class="p10"></div>
            <label for="newTreeNameID" class="mT0"><b>New Survey Title:</b></label>
            <input type="text" name="newTreeName" id="newTreeNameID" class="form-control" value="" 
                autocomplete="off" onBlur="slugOnBlur(this, 'newTreeSlugID');">
            <div class="p10"></div>
            <label for="newTreeSlugID"><b>New Survey URL:</b> {{ $GLOBALS['SL']->sysOpts["app-url"] }}/<div 
                id="isNewAdmPag" class="disNon">dashboard/</div>start/</label>
            <input type="text" name="newTreeSlug" id="newTreeSlugID" class="form-control" value="" 
                autocomplete="off">
            <div class="p10"></div>
            <input type="submit" class="btn btn-lg btn-primary" value="Create New Survey">
        </div>
        </form>
    
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection