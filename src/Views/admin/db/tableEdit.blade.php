<!-- resources/views/vendor/survloop/admin/db/tableEdit.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="slCard nodeWrap">
<a href="/dashboard/db/table/{{ $tbl->TblName }}" class="btn btn-secondary pull-right">View Table</a>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> {{ $GLOBALS['SL']->dbRow->DbName }}</span>:
    @if (isset($tbl->TblEng) && trim($tbl->TblEng) != '') Edit Table: {{ $tbl->TblEng }}
    @else Add New Table
    @endif
</h1>

<form name="mainPageForm" method="post"
    @if (trim($tblName) == '') action="/dashboard/db/addTable"
    @else action="/dashboard/db/table/{{ $tblName }}/edit"
    @endif >
<input type="hidden" name="tblEditForm" value="YES">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<div class="p10"></div>
<div class="row">
    <div class="col-md-6">

        <fieldset class="form-group">
            <label for="TblEngID">Plain English Name</label>
            <input id="TblEngID" name="TblEng" value="{{ $tbl->TblEng }}" type="text" class="form-control" > 
        </fieldset>
        <div class="p5"></div>
        <fieldset class="form-group">
            <label for="TblNameID">Database Name</label>
            <input id="TblNameID" name="TblName" value="{{ $tbl->TblName }}" type="text" class="form-control" > 
        </fieldset>
        <div class="p5"></div>
        <fieldset class="form-group">
            <label for="TblAbbrID">Abbreviation</label>
            <input id="TblAbbrID" name="TblAbbr" value="{{ $tbl->TblAbbr }}" type="text" class="form-control" > 
        </fieldset>
        <div class="p5"></div>
        <fieldset class="form-group">
            <label for="TblTypeID">Type</label>
            <select id="TblTypeID" name="TblType" class="form-control" > 
                <option value="Data" @if ($tbl->TblType == 'Data') SELECTED @endif >Data</option>
                <option value="Subset" @if ($tbl->TblType == 'Subset') SELECTED @endif >Subset</option>
                <option value="Linking" @if ($tbl->TblType == 'Linking') SELECTED @endif >Linking</option>
                <option value="Validation" @if ($tbl->TblType == 'Validation') SELECTED @endif >Validation</option>
            </select>
        </fieldset>
        
    </div>
    <div class="col-md-6">
        
        <fieldset class="form-group">
            <label for="TblDescID">Description</label>
            <textarea id="TblDescID" name="TblDesc" class="form-control" >{{ $tbl->TblDesc }}</textarea>
        </fieldset>
        <div class="p5"></div>
        <fieldset class="form-group">
            <label for="TblNotesID">Notes</label>
            <textarea id="TblNotesID" name="TblNotes" class="form-control" >{{ $tbl->TblNotes }}</textarea>
        </fieldset>
        <div class="p5"></div>
        <fieldset class="form-group">
            <label for="TblGroupID">Table Group</label>
            <input id="TblGroupID" name="TblGroup" value="{{ $tbl->TblGroup }}" type="text" class="form-control" > 
        </fieldset>
        
    </div>
</div>
    
<div class="p20"></div>
<div class="pB20">
    <input type="submit" class="btn btn-xl btn-primary pull-right" 
            @if (trim($tblName) == '') value="Add Table" @else value="Save Changes" @endif >
    <div class="red">
        <input type="checkbox" name="deleteTbl" id="deleteTblID" value="1" style="width: 20px;"> 
        <label for="deleteTblID">Delete</label>
    </div>
</div>
<div class="fC p20"></div>
    
</form>
</div>
</div>

@endsection
