<!-- resources/views/vendor/survloop/admin/db/tableEdit.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
    <div class="slCard nodeWrap">

        <a href="/dashboard/db/table/{{ $tbl->tbl_name }}" 
            class="btn btn-sm btn-secondary pull-right">View Table</a>
        <h3>
            <span class="slBlueDark"><i class="fa fa-database"></i> 
            @if (isset($tbl->tbl_eng) && trim($tbl->tbl_eng) != '') Edit Table: {{ $tbl->tbl_eng }}
            @else Add New Table
            @endif
        </h3>

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
                    <input id="TblEngID" name="TblEng" value="{{ $tbl->tbl_eng }}" 
                        type="text" class="form-control" > 
                </fieldset>
                <div class="p5"></div>
                <fieldset class="form-group">
                    <label for="TblNameID">Database Name</label>
                    <input id="TblNameID" name="TblName" value="{{ $tbl->tbl_name }}" 
                        type="text" class="form-control" > 
                </fieldset>
                <div class="p5"></div>
                <fieldset class="form-group">
                    <label for="TblAbbrID">Abbreviation</label>
                    <input id="TblAbbrID" name="TblAbbr" value="{{ $tbl->tbl_abbr }}" 
                        type="text" class="form-control" > 
                </fieldset>
                <div class="p5"></div>
                <fieldset class="form-group">
                    <label for="TblTypeID">Type</label>
                    <select id="TblTypeID" name="TblType" class="form-control" > 
                        <option value="Data" 
                            @if ($tbl->tbl_type == 'Data') SELECTED @endif 
                            >Data</option>
                        <option value="Subset" 
                            @if ($tbl->tbl_type == 'Subset') SELECTED @endif 
                            >Subset</option>
                        <option value="Linking" 
                            @if ($tbl->tbl_type == 'Linking') SELECTED @endif 
                            >Linking</option>
                        <option value="Validation" 
                            @if ($tbl->tbl_type == 'Validation') SELECTED @endif 
                            >Validation</option>
                    </select>
                </fieldset>
                <div class="p5"></div>
                <fieldset class="form-group">
                    <label for="TblGroupID">Table Group</label>
                    <input id="TblGroupID" name="TblGroup" value="{{ $tbl->tbl_group }}" 
                        type="text" class="form-control" > 
                </fieldset>
                
            </div>
            <div class="col-md-6">
                
                <fieldset class="form-group">
                    <label for="TblDescID">Description</label>
                    <textarea id="TblDescID" name="TblDesc" class="form-control" 
                        style="height: 170px;">{{ $tbl->tbl_desc }}</textarea>
                </fieldset>
                <div class="p5"></div>
                <fieldset class="form-group">
                    <label for="TblNotesID">Notes</label>
                    <textarea id="TblNotesID" name="TblNotes" class="form-control" 
                        style="height: 170px;">{{ $tbl->tbl_notes }}</textarea>
                </fieldset>
                
            </div>
        </div>
            
        <div class="p20"></div>
        <div class="pB20">
            <input type="submit" class="btn btn-lg btn-primary pull-right" 
                @if (trim($tblName) == '') value="Add Table" 
                @else value="Save Changes" 
                @endif >
            <div class="red">
                <input type="checkbox" name="deleteTbl" id="deleteTblID" 
                    value="1" style="width: 20px;"> 
                <label for="deleteTblID">Delete</label>
            </div>
        </div>
        <div class="fC p20"></div>
            
        </form>

    </div>
</div>

@endsection
