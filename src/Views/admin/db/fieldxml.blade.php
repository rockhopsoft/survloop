<!-- resources/views/vendor/survloop/admin/db/fieldxml.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>:
    <nobr>Fields XML Options</nobr>
</h1>
<a href="/dashboard/db/all" class="btn btn-xs btn-default">All Database Details</a>

<div class="clearfix p10"></div>

<ul>
<li><b>Public Data (Default)</b>     - These fields are always included in resulting XML files</li>
<li><b>Private Data</b>             - These fields are included in public XML files only if the user chose to make them public </li>
<li><b>Sensitive Data</b>             - These fields are only included in admin or super user XML files</li>
<li><b>Internal Use</b>             - These fields are never included in XML files</li>
</ul>

<form name="fldXMLForm" action="/dashboard/db/fieldXML/save" method="post" target="hidFrame">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="changedFld" id="changedFldID" value="-3">
<input type="hidden" name="changedFldSetting" id="changedFldSettingID" value="-3">
</form>
<script type="text/javascript">
function saveXmlSetting(FldID, newSetting) {
    document.getElementById('changedFldID').value = FldID;
    document.getElementById('changedFldSettingID').value = newSetting;
    document.fldXMLForm.submit();
    return true;
}
</script>
<table border=0 class="FldDescs" >

@forelse ($tblsOrdered as $tbl)

    @if ($tblFldLists[$tbl->TblID] && sizeof($tblFldLists[$tbl->TblID]) > 0)
    
        <tr><td colspan=4 class="p20 headerBrkRow f20" >
            Table: <b>{!! $GLOBALS['SL']->tblEng[$tbl->TblID] !!}</b> 
            ({{ number_format(sizeof($tblFldLists[$tbl->TblID])) }})
        </td></tr>
        @foreach ($tblFldLists[$tbl->TblID] as $cnt => $fld)
            <tr @if ($cnt%2 == 0) class="row2" @endif >
            <td><a href="/dashboard/db/field/{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] }}/{{ $fld->FldName }}" 
                class="f14"><i class="fa fa-pencil"></i></a></td>
            <td class="w40 pB10">
                <span class="f8"><i>{{ $GLOBALS['SL']->tblEng[$fld->FldTable] }}</i></span><br />
                <b>{{ $fld->FldEng }}</b><br />
                <div class="f10">{{ $FldDataTypes[$fld->FldType][1] }}
                @if (intVal($fld->FldForeignTable) > 0) 
                    - <i class="fa fa-link"></i>{!! view('vendor.survloop.admin.db.inc-getTblName', [
                        "id" => $fld->FldForeignTable, "link" => 0, "xtraTxt" => ' ID', "xtraLnk" => ''
                    ])->render() !!}
                @endif
                </div>
            </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->FldID }}" id="fldXML{{ $fld->FldID }}a" value="1"
                        @if ($fld->FldOpts%5 > 0 && $fld->FldOpts%7 > 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->FldID }}, this.value);" 
                        > Public Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->FldID }}" id="fldXML{{ $fld->FldID }}b" value="7"
                        @if ($fld->FldOpts%7 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->FldID }}, this.value);" 
                        > Private Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->FldID }}" id="fldXML{{ $fld->FldID }}c" value="11"
                        @if ($fld->FldOpts%11 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->FldID }}, this.value);" 
                        > Sensitive Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->FldID }}" id="fldXML{{ $fld->FldID }}c" value="13"
                        @if ($fld->FldOpts%13 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->FldID }}, this.value);" 
                        > Internal Use</label>
                </td>
            </tr>
            
        @endforeach
        
    @endif

@empty

@endforelse

</table>

<div class="p20"></div><div class="p20"></div>
@endsection