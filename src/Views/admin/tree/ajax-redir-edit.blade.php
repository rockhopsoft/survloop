<!-- Stored in resources/views/vender/survloop/admin/tree/ajax-redir-edit.blade.php -->
<form name="redirFormT{{ $tree->TreeID }}" method="post" action="/dashboard/pages/list?redirEdit={{ $tree->TreeID }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<div class="row mT10">
    <div class="col-md-4"><nobr> 
        @if ($tree->TreeOpts%3 == 0 || $tree->TreeOpts%17 == 0 || $tree->TreeOpts%41 == 0 || $tree->TreeOpts%43 == 0) 
            /dash/ @else / @endif 
        <input type="text" name="redirFrom" id="redirFromID" class="form-control disIn" 
            value="{{ $tree->TreeSlug }}" autocomplete="off"></nobr>
    </div><div class="col-md-2">
        <div class="pT5 slGreenDark"><nobr>redirects to</nobr></div>
    </div><div class="col-md-5">
        <input type="text" name="redirTo" id="redirToID" class="form-control" autocomplete="off"
            value="{{ $tree->TreeName }}">
    </div><div class="col-md-1">
        <input type="submit" class="btn btn-primary" value="Save">
    </div>
</div>
</form>