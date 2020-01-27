<!-- resources/views/vender/survloop/admin/tree/ajax-redir-edit.blade.php -->
<form name="redirFormT{{ $tree->tree_id }}" method="post" 
    action="/dashboard/pages?redirEdit={{ $tree->tree_id }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<div class="row mT10">
    <div class="col-4"><nobr> 
        @if ($tree->tree_opts%3 == 0 || $tree->tree_opts%17 == 0 
            || $tree->tree_opts%41 == 0 || $tree->tree_opts%43 == 0) 
            /dash/ @else / @endif 
        <input type="text" name="redirFrom" id="redirFromID" class="form-control disIn" 
            value="{{ $tree->tree_slug }}" autocomplete="off"></nobr>
    </div><div class="col-2">
        <div class="pT5 slGreenDark"><nobr>redirects to</nobr></div>
    </div><div class="col-5">
        <input type="text" name="redirTo" id="redirToID" class="form-control" autocomplete="off"
            value="{{ $tree->tree_name }}">
    </div><div class="col-1">
        <input type="submit" class="btn btn-primary" value="Save">
    </div>
</div>
</form>