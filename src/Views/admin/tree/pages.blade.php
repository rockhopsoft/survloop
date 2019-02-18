<!-- Stored in resources/views/vender/survloop/admin/tree/pages.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="row">
    <div class="col-md-7">
    
        <div class="slCard nodeWrap">
        <h2><i class="fa fa-newspaper-o"></i> Site Pages <span class="slGrey">& Redirects</span></h2>
        <div class="slGrey pB10">
            Pages are used to manage content throughout your website, both public and admin, 
            and they are build as a one-page Experience/Tree.
            This means you can use conditional login on chunks of content within each page, and more safely
            integrate many widgets on one page.
        </div>
        <table class="table table-striped">
        
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%7 == 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0 
                && $tree->TreeOpts%43 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%7 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0 
                && $tree->TreeOpts%43 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myRdr["home"] as $redir)
            {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
        @empty @endforelse
        
        @if ($GLOBALS["SL"]->sysHas('volunteers'))
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 == 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 > 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myRdr["volun"] as $redir)
                {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
            @empty @endforelse
        @endif
        
        @if ($GLOBALS["SL"]->sysHas('partners'))
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%41 == 0 && $tree->TreeOpts%7 == 0 && $tree->TreeOpts%17 > 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%41 == 0 && $tree->TreeOpts%7 > 0 && $tree->TreeOpts%17 > 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty @endforelse
            @forelse ($myRdr["partn"] as $redir)
                {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
            @empty @endforelse
        @endif
        
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%43 == 0 && $tree->TreeOpts%7 == 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0)
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%43 == 0 && $tree->TreeOpts%7 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0)
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myRdr["admin"] as $redir)
            {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
        @empty @endforelse
        
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 == 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0 
                && $tree->TreeOpts%43 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0 
                && $tree->TreeOpts%43 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty @endforelse
        @forelse ($myRdr["admin"] as $redir)
            {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
        @empty @endforelse
        
        </table>
        </div>
        
    </div><div class="col-md-5">
        
        <div class="slCard nodeWrap slGrey">
            <div class="row">
                <div class="col-6">
                    {!! view('vendor.survloop.admin.tree.inc-legend-perms')->render() !!}
                </div><div class="col-6">
                    <div class="mB5"><u>Special Page Types</u></div>
                    <div class="mB5"><i class="fa fa-list-alt mR5"></i> Report for Survey</div>
                    <div class="mB5"><i class="fa fa-search mR5" aria-hidden="true"></i> Search Results</div>
                    <div class="mB5"><i class="fa fa-home mR5" aria-hidden="true"></i> Home/Dashboard Page</div>
                </div>
            </div>
        </div>
    
        <div class="slCard nodeWrap">
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <form name="mainPageForm" method="post" action="/dashboard/pages/list">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="sub" value="1">
        <div id="newPageForm" class="row2 p20 mT20 mB20">
            <a id="hidivBtnNewPage" class="hidivBtn" href="javascript:;"
                ><h3 class="m0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create New Page</h3></a>
            <div id="hidivNewPage" class="disNon mT20">
                <div class="row">
                    <div class="col-6">
                        <label><input type="checkbox" name="pageIsReport" value="1"
                            onClick="if (this.checked) { document.getElementById('reportPageTreeID').style.display='block';} 
                            else { document.getElementById('reportPageTreeID').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-list-alt"></i> Report for Survey
                            <select name="reportPageTree" id="reportPageTreeID" class="form-control disNon" 
                                autocomplete="off">{!! $GLOBALS["SL"]->allTreeDropOpts() !!}
                            </select>
                        </label>
                    </div><div class="col-6">
                        <label><input type="checkbox" name="pageAdmOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-eye" aria-hidden="true"></i> Admin-Only Page</label>
                        <label><input type="checkbox" name="pageStfOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-key" aria-hidden="true"></i> Staff Page</label>
                    @if ($GLOBALS["SL"]->sysHas('partners'))
                        <label><input type="checkbox" name="pagePrtOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-university" aria-hidden="true"></i> Partners Page</label>
                    @endif
                    @if ($GLOBALS["SL"]->sysHas('volunteers'))
                        <label><input type="checkbox" name="pageVolOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer Page</label>
                    @endif
                    </div>
                </div>
                <label for="newPageNameID" class="mT10"><b>New Page Title:</b></label>
                <input type="text" name="newPageName" id="newPageNameID" class="form-control" value="" 
                    autocomplete="off" onBlur="slugOnBlur(this, 'newPageSlugID');">
                <div class="p10"></div>
                <label for="newPageSlugID"><b>New Page URL:</b><br />{{ 
                    $GLOBALS['SL']->sysOpts["app-url"] }}/<div id="isNewAdmPag" class="disNon">dash/</div></label>
                <input type="text" name="newPageSlug" id="newPageSlugID" class="form-control" value="" 
                    autocomplete="off">
                <div class="p10"></div>
                <div class="fR taR">
                @if (!$autopages["contact"])
                    Auto-Create:<br />
                @endif
                @if (!$autopages["contact"])
                    <a class="btn btn-sm btn-secondary" href="/dashboard/pages/list/add-contact"
                        >Contact Page</a>
                @endif
                </div>
                <input type="submit" class="btn btn-lg btn-primary" value="Create New Page">
                </form>
                <div class="fC"></div>
            </div>
        </div>
        </div>
        
        <div class="slCard nodeWrap">
        <form name="mainRedirForm" method="post" action="/dashboard/pages/list">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="subRedir" value="1">
        <div id="newRedirForm" class="row2 p20 mT20 mB20">
            <a id="hidivBtnNewRedir" class="hidivBtn" href="javascript:;"
                ><h3 class="m0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create New Redirect</h3></a>
            <div id="hidivNewRedir" class="disNon mT20">
                <div>
                <label><input type="checkbox" name="redirAdmOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmRedir').style.display='inline'; 
                        document.getElementById('isNewAdmRedir').style.display='inline'; } 
                    else { document.getElementById('isNewAdmRedir').style.display='none'; 
                        document.getElementById('isNewAdmRedir').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-eye" aria-hidden="true"></i> Admin-Only</label>
                <label><input type="checkbox" name="redirStfOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmRedir').style.display='inline'; 
                        document.getElementById('isNewAdmRedir').style.display='inline'; } 
                    else { document.getElementById('isNewAdmRedir').style.display='none'; 
                        document.getElementById('isNewAdmRedir').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-key" aria-hidden="true"></i> Staff</label>
            @if ($GLOBALS["SL"]->sysHas('partners'))
                <label class="mL20"><input type="checkbox" name="redirPrtOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmRedir').style.display='inline'; 
                        document.getElementById('isNewAdmRedir2').style.display='inline'; } 
                    else { document.getElementById('isNewAdmRedir').style.display='none'; 
                        document.getElementById('isNewAdmRedir2').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-university" aria-hidden="true"></i> Partner</label>
            @endif
            @if ($GLOBALS["SL"]->sysHas('volunteers'))
                <label class="mL20"><input type="checkbox" name="redirVolOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmRedir').style.display='inline'; 
                        document.getElementById('isNewAdmRedir2').style.display='inline'; } 
                    else { document.getElementById('isNewAdmRedir').style.display='none'; 
                        document.getElementById('isNewAdmRedir2').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer</label>
            @endif
                </div>
                <label for="newRedirNameID" class="mT10"><b>Redirect This URL:</b><br />{{ 
                    $GLOBALS['SL']->sysOpts["app-url"] }}/<div id="isNewAdmRedir" class="disNon">dash/</div></label>
                <input type="text" name="newRedirFrom" id="newRedirFromID" class="form-control" autocomplete="off">
                <div class="p10"></div>
                <label for="newRedirSlugID"><b>To This URL:</b></label>
                <input type="text" name="newRedirTo" id="newRedirToID" class="form-control" autocomplete="off">
                <div class="p10"></div>
                <input type="submit" class="btn btn-lg btn-primary" value="Create New Redirect">
                <div class="fC"></div>
            </div>
        </div>
        </form>
        </div>
        
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection