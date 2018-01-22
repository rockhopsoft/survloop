<!-- Stored in resources/views/vender/survloop/admin/tree/pages.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h2><i class="fa fa-newspaper-o"></i> Site Pages</h2>
        <div class="slGrey pB10">
            Pages provide a similar end-result to those of WordPress, but are build as a one-page Experience/Tree.
            This means you can use conditional login on chunks of content within each page, and more safely
            integrate many widgets on one page.
        </div>
        <table class="table table-striped">
        @forelse ($myPages as $tree)
            <tr><td class="w50">
                <a href="/dashboard/page/{{ $tree->TreeID }}?all=1&refresh=1" class="fPerc133"
                    ><i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i> {{ $tree->TreeName }}</a>
            </td><td class="w50">
                @if ($tree->TreeOpts%3 == 0)
                    @if ($tree->TreeOpts%7 == 0)
                        <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dashboard" target="_blank">/dashboard</a>
                    @else
                        <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ $tree->TreeSlug }}" target="_blank" 
                            >/dash/{{ $tree->TreeSlug }}</a>
                    @endif
                @else
                    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $tree->TreeSlug }}" target="_blank"
                        >/{{ $tree->TreeSlug }}</a>
                @endif
                @if ($tree->TreeDesc) <div class="slGrey">{{ $tree->TreeDesc }}</div> @endif
            </td></tr>
        @empty
            <tr><td colspan=2 ><i>No pages found.</i></td></tr>
        @endforelse
        </table>
        <div class="mT20 mB10"><a href="javascript:;" id="newPage" class="btn btn-xs btn-default"
            ><i class="fa fa-plus" aria-hidden="true"></i> Create A New Page</a>
        </div>
        <div id="newPageForm" class="mB20 disNon">
            <div class="row row2 pT20 pB20 mB20">
                <form name="mainPageForm" method="post" action="/dashboard/pages/list">
                <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="sub" value="1">
                <div class="col-md-8">
                    <label for="newPageNameID" class="mT0"><h3 class="mT0">New Page Title:</label></h3>
                    <input type="text" name="newPageName" id="newPageNameID" class="form-control" value="" 
                        autocomplete="off">
                    <div class="p10"></div>
                    <label for="newPageSlugID"><h3 class="disIn">New Page URL:</h3>
                        {{ $GLOBALS['SL']->sysOpts["app-url"] }}/<div id="isNewAdmPag" class="disNon">dash/</div></label>
                    <input type="text" name="newPageSlug" id="newPageSlugID" class="form-control" value="" 
                        autocomplete="off">
                    <div class="p10"></div>
                    <input type="submit" class="btn btn-lg btn-primary" value="Create New Page">
                </div><div class="col-md-4">
                    <label class="disBlo mT10"><input type="checkbox" name="pageAdmOnly" value="1" class="mR5"
                        onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                        else { document.getElementById('isNewAdmPag').style.display='none'; }" autocomplete="off"> 
                        Admin-Only Page</label>
                    <label class="disBlo mT10"><input type="checkbox" name="pageIsReport" value="1" class="mR5"
                        onClick="if (this.checked) { document.getElementById('reportPageTreeID').style.display='block'; } 
                        else { document.getElementById('reportPageTreeID').style.display='none'; }" autocomplete="off"> 
                        Report for Form Tree
                        <select name="reportPageTree" id="reportPageTreeID" class="form-control disNon" 
                            autocomplete="off">{!! $GLOBALS["SL"]->allTreeDropOpts() !!}
                        </select>
                    </label>
                </div>
                </form>
            </div>
            @if (!$autopages["contact"])
                <a class="btn btn-xs btn-default fL" href="/dashboard/pages/list/add-contact"
                    >Auto-Create: Contact Page</a>
            @endif
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        <h3>Excerpts & Instructions</h3>
        <div class="slGrey pB10">
            Excerpts are useful for chunks of content, small or large, which need to appear the same way in multiple 
            places through the system. Excerpts can be including in any node via "<?= '{'.'{Excerpt Name}'.'}' ?>".
        </div>
        <table class="table table-striped">
        @forelse($blurbRows as $blurb)
            <tr><td><a href="/dashboard/blurbs/{{ $blurb->DefID }}" class="fPerc133">
                <i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i> {{ $blurb->DefSubset }}</a>
            </td></tr>
        @empty
            <tr><td><i>No excerpts/instructions found.</i></td></tr>
        @endforelse
        </table>
        <div class="mT20 mB10"><a href="javascript:;" id="newBlurb" class="btn btn-xs btn-default"
                ><i class="fa fa-plus mR5" aria-hidden="true"></i> Create A New Excerpt</a>
        </div>
        <div id="newBlurbForm" class="disNon">
            <form name="newBlurbForm" method="post" action="/dashboard/pages/list">
            <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="sublurb" value="1">
            <hr>
            <label for="newPageNameID" class="mT0"><h3 class="mT0">New Excerpt Name:</label></h3>
            <input type="text" name="newBlurbName" id="newBlurbNameID" class="form-control" value="">
            <div class="p10"></div>
            <input type="submit" class="btn btn-primary" value="Create Excerpt">
            </form>
            <hr class="mB10">
        </div>
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection