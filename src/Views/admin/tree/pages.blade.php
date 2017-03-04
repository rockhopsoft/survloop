<!-- Stored in resources/views/vender/survloop/admin/tree/pages.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1><i class="fa fa-newspaper-o"></i> Site Pages</h1>
<div class="p20"></div>
<ul>
@forelse ($myPages as $tree)
    <li><a href="/dashboard/page/{{ $tree->TreeID }}"><h3 class="disIn">{{ $tree->TreeName }}</a></h3>
    - {{ $tree->TreeDesc }}</li>
@empty
    <i>No pages found.</i>
@endforelse
</ul>
<div class="p20"></div>
<hr>
<h1><i class="fa fa-plus mR5" aria-hidden="true"></i> Create A New Page</h1>
<hr class="mB20">
<form name="newPageForm" method="post" action="/dashboard/pages/list">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">
<h3><label><input type="checkbox" name="pageAdmOnly" value="1"
    onClick="if (this.checked) { document.getElementById('isNewAdmPag').style.display='inline'; } 
    else { document.getElementById('isNewAdmPag').style.display='none'; }"> Create An Admin-Only Page</h3></label>
<label for="newPageNameID"><h3>New Page Title</label></h3>
<input type="text" name="newPageName" id="newPageNameID" class="form-control" value="">
<div class="p10"></div>
<label for="newPageSlugID"><h3 class="disIn mR20">New Page URL</h3>
    {{ $GLOBALS['SL']->sysOpts["app-url"] }}/<div id="isNewAdmPag" class="disNon">dash/</div></label>
<input type="text" name="newPageSlug" id="newPageSlugID" class="form-control" value="">
<div class="p10"></div>
<input type="submit" class="btn btn-lg btn-primary" value="Create A New Page">
</form>

@endsection