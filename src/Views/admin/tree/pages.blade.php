<!-- Stored in resources/views/vender/survloop/admin/tree/pages.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="row">
    <div class="col-md-7">
        
        <h2><i class="fa fa-newspaper-o"></i> Site Pages <span class="slGreenDark">& Redirects</span></h2>
        <div class="slGrey pB10">
            Pages are used to manage content throughout your website, both public and admin, 
            and they are build as a one-page Experience/Tree.
            This means you can use conditional login on chunks of content within each page, and more safely
            integrate many widgets on one page.<br />
            <nobr><span class="mR20"><i class="fa fa-list-alt"></i> Report for Survey</span></nobr>
            <nobr><span class="mR20"><i class="fa fa-key" aria-hidden="true"></i> Admin-Only Page</span></nobr>
            <nobr><span class="mR20"><i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer Page</span></nobr>
        </div>
        <table class="table table-striped">
        
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%7 == 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%7 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myRdr["home"] as $redir)
            {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
        @empty
        @endforelse
        
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 == 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myPages as $tree)
            @if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 > 0) 
                {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
            @endif
        @empty
        @endforelse
        @forelse ($myRdr["admin"] as $redir)
            {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
        @empty
        @endforelse
        
        @if (isset($GLOBALS["SL"]->sysOpts['has-volunteers']) && intVal($GLOBALS["SL"]->sysOpts['has-volunteers']) == 1)
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 == 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty
            @endforelse
            @forelse ($myPages as $tree)
                @if ($tree->TreeOpts%17 == 0 && $tree->TreeOpts%7 > 0) 
                    {!! view('vendor.survloop.admin.tree.pages-row', [ "tree" => $tree ])->render() !!}
                @endif
            @empty
            @endforelse
            @forelse ($myRdr["volun"] as $redir)
                {!! view('vendor.survloop.admin.tree.pages-row-redir', [ "redir" => $redir ])->render() !!}
            @empty
            @endforelse
        @endif
        
        </table>
        
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <form name="mainPageForm" method="post" action="/dashboard/pages/list">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="sub" value="1">
        <div id="newPageForm" class="row2 p20 mT20 mB20">
            <a id="hidivBtnNewPage" class="hidivBtn" href="javascript:;"
                ><h3 class="m0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create New Page</h3></a>
            <div id="hidivNewPage" class="disNon mT20">
                <div class="row">
                    <div class="col-md-6">
                        <label><input type="checkbox" name="pageIsReport" value="1"
                            onClick="if (this.checked) { document.getElementById('reportPageTreeID').style.display='block';} 
                            else { document.getElementById('reportPageTreeID').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-list-alt"></i> Report for Survey
                            <select name="reportPageTree" id="reportPageTreeID" class="form-control disNon" 
                                autocomplete="off">{!! $GLOBALS["SL"]->allTreeDropOpts() !!}
                            </select>
                        </label>
                    </div><div class="col-md-6">
                        <label><input type="checkbox" name="pageAdmOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-key" aria-hidden="true"></i> Admin-Only Page</label>
                        <label><input type="checkbox" name="pageVolOnly" value="1" 
                            onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                            else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                            <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer Page</label>
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
                    <a class="btn btn-xs btn-default" href="/dashboard/pages/list/add-contact"
                        >Contact Page</a>
                @endif
                </div>
                <input type="submit" class="btn btn-lg btn-primary" value="Create New Page">
                </form>
                <div class="fC"></div>
            </div>
        </div>
        <div class="p10"></div>
        
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
                    <i class="fa fa-key" aria-hidden="true"></i> Admin-Only</label>
                <label class="mL20"><input type="checkbox" name="redirVolOnly" value="1" 
                    onClick="if (this.checked) { document.getElementById('isNewAdmRedir').style.display='inline'; 
                        document.getElementById('isNewAdmRedir2').style.display='inline'; } 
                    else { document.getElementById('isNewAdmRedir').style.display='none'; 
                        document.getElementById('isNewAdmRedir2').style.display='none'; }" autocomplete="off"> 
                    <i class="fa fa-hand-rock-o" aria-hidden="true"></i> Volunteer</label>
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
        <div class="p10"></div>
        </form>
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection