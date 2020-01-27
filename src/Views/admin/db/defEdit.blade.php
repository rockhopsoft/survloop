<!-- resources/views/vendor/survloop/admin/db/defEdit.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="slCard nodeWrap">
<h2>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
    @if ($defID > 0) Edit Definition {{ $def->def_subset }}: {{ $def->def_value }} 
    @else Add New Definition
    @endif
</h2>

<a href="/dashboard/db/all" class="btn btn-secondary mR10">All Database Details</a>
<a href="/dashboard/db/definitions" class="btn btn-secondary mR10">All Definitions</a>

<form name="mainPageForm" method="post" 
    @if (isset($def->def_id) && intVal($def->def_id) > 0) 
        action="/dashboard/db/definitions/edit-sub/{{ $def->def_id }}"
    @else action="/dashboard/db/definitions/add-sub{{ ((trim($subset) != '') ? '/' . urlencode($subset) : '') }}"
    @endif >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="defEditForm" value="YES">

<div class="row mT20">
    <div class="col-6">
        <label for="defSubsetID">Part of Set:</label>
        <select id="defSubsetID" name="defSubset" autocomplete="off" class="form-control"
            onChange="return chkSubset(this.value);" >
            <option value="" @if ($def->def_subset == '') SELECTED @endif ></option><option value="_">New Set</option>
            @if ($subList->isNotEmpty())
                @foreach ($subList as $set) {
                    <option value="{{ $set->def_subset }}" @if ($set->def_subset == $def->def_subset) SELECTED @endif 
                    	>{{ $set->def_subset }}</option>
                @endforeach
            @endif
        </select>
        <div id="newSubsetDiv" class="disNon pL20 mL20">
        	New Set: <input type="text" name="newSubset" value="" class="form-control">
        </div>
    
        <label for="defValueID" class="mT20">Definition Value:</label>
        <input type="text" id="defValueID" name="defValue" value="{{ $def->def_value }}" class="form-control">
        
        <label for="defDescriptionID" class="mT20">Value Description/Notes:</label>
        <textarea id="defDescriptionID" name="defDescription" class="form-control">{{ $def->def_description }}</textarea>
        
        <center><div class="p10"></div>
        <input type="submit" class="btn btn-lg btn-primary" 
            value=" @if (trim($subset) != '') Add Value @else Save Changes @endif " >
        </center>
    </div>
    <div class="col-6 p20 taR red ">
        <input type="checkbox" name="deleteDef" id="deleteDefID" value="1">
        <label for="deleteDefID">Delete Definition</label>
    </div>
</div>
</form>
</div>
</div>
<script type="text/javascript">
function chkSubset(newVal) {
    if (newVal == '_') document.getElementById('newSubsetDiv').style.display='block'; 
    else document.getElementById('newSubsetDiv').style.display='none';
}
</script>

<div class="adminFootBuff"></div>

@endsection
