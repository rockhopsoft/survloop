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
        @forelse ($myPages as $tree)
            <div class="pB10">
                <a href="/dashboard/page/{{ $tree->TreeID }}?all=1&refresh=1" class="btn btn-default fL"
                    ><i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i> {{ $tree->TreeName }}</a>
                    @if ($tree->TreeDesc) <div class="disIn mL20 slGrey">{{ $tree->TreeDesc }}</div> @endif
                <div class="fR mT10">
                    @if ($tree->TreeOpts%3 == 0)
                        @if ($tree->TreeOpts%7 == 0)
                            <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dashboard">/dashboard</a>
                        @else
                            <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ $tree->TreeSlug }}" 
                                >/dash/{{ $tree->TreeSlug }}</a>
                        @endif
                    @else
                        <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $tree->TreeSlug }}"
                            >/{{ $tree->TreeSlug }}</a>
                    @endif
                </div>
                <div class="fC"></div>
            </div>
        @empty
            <i>No pages found.</i>
        @endforelse
        <div class="mT20 mB10"><a href="javascript:;" id="newPage" class="btn btn-xs btn-default"
            ><i class="fa fa-plus" aria-hidden="true"></i> Create A New Page</a>
        </div>
        <div id="newPageForm" class="disNon mB20">
            <form name="newPageForm" method="post" action="/dashboard/pages/list">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="sub" value="1">
            <hr>
            <label for="newPageNameID" class="mT0"><h3 class="mT0">New Page Title:</label></h3>
            <input type="text" name="newPageName" id="newPageNameID" class="form-control" value="">
            <div class="p10"></div>
            <label for="newPageSlugID"><h3 class="disIn">New Page URL:</h3>
                {{ $GLOBALS['SL']->sysOpts["app-url"] }}/<div id="isNewAdmPag" class="disNon">dash/</div></label>
            <input type="text" name="newPageSlug" id="newPageSlugID" class="form-control" value="">
            <div class="p5"></div>
            <b><label><input type="checkbox" name="pageAdmOnly" value="1"
                onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
                else { document.getElementById('isNewAdmPag').style.display='none'; }"> 
                Admin-Only Page</b></label>
            <div class="p10"></div>
            <input type="submit" class="btn btn-lg btn-primary" value="Create Page">
            </form>
            <hr class="mB10">
        </div>
        <div class="mT20 mB10">
            @if (!$autopages["contact"])
                <a class="btn btn-xs btn-default fL" href="/dashboard/pages/list/add-contact"
                    >Auto-Create: Contact Page</a>
            @endif
            <div class="fC"></div>
        </div>
        
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        <h3>Excerpts & Instructions</h3>
        <div class="slGrey pB10">
            Excerpts are useful for chunks of content, small or large, which need to appear the same way in multiple 
            places through the system. Excerpts can be including in any node via "<?= '{'.'{Excerpt Name}'.'}' ?>".
        </div>
        @forelse($blurbRows as $blurb)
            <a href="/dashboard/blurbs/{{ $blurb->DefID }}" class="btn btn-default">
            <i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i> {{ $blurb->DefSubset }}</a>
            <br /><br />
        @empty
            <i>No excerpts/instructions found.</i>
        @endforelse
        <div class="mT20 mB10"><a href="javascript:;" id="newBlurb" class="btn btn-xs btn-default"
                ><i class="fa fa-plus mR5" aria-hidden="true"></i> Create A New Excerpt</a>
        </div>
        <div id="newBlurbForm" class="disNon">
            <form name="newBlurbForm" method="post" action="/dashboard/pages/list">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
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