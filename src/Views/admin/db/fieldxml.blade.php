<!-- resources/views/vendor/survloop/admin/db/fieldxml.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container"><div class="slCard">
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
    <nobr>Field Privacy Settings</nobr> 
</h1>
<h3>Options of each field printed in Reports & XML Exports</h3>
<ul>
<li><b class="slBlueDark">Public Data</b> - 
    (Default) These fields are always included in resulting XML files</li>
<li><b class="slBlueDark">Private Data</b> - 
    These fields are included in public XML files only if the user chose to make them public </li>
<li><b class="slBlueDark">Sensitive Data</b> - 
    These fields are only included in admin or super user XML files</li>
<li><b class="slBlueDark">Internal Use</b> - 
    These fields are never included in XML files</li>
</ul>

<form name="mainPageForm" action="/dashboard/db/fieldXML/save" 
    method="post" target="hidFrame">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="changedFld" id="changedFldID" value="-3">
<input type="hidden" name="changedFldSetting" id="changedFldSettingID" value="-3">
</form>
<script type="text/javascript">
function saveXmlSetting(FldID, newSetting) {
    document.getElementById('changedFldID').value = FldID;
    document.getElementById('changedFldSettingID').value = newSetting;
    document.mainPageForm.submit();
    return true;
}
</script>
<table border=0 class="FldDescs" >

@forelse ($tblsOrdered as $tbl)

    @if ($tblFldLists[$tbl->tbl_id] && sizeof($tblFldLists[$tbl->tbl_id]) > 0)
    
        <tr><td colspan=4 class="p20 headerBrkRow fPerc125" >
            Table: <b>{!! $GLOBALS['SL']->tblEng[$tbl->tbl_id] !!}</b> 
            ({{ number_format(sizeof($tblFldLists[$tbl->tbl_id])) }})
        </td></tr>
        @foreach ($tblFldLists[$tbl->tbl_id] as $cnt => $fld)
            <tr @if ($cnt%2 == 0) class="row2" @endif >
            <td><a href="/dashboard/db/field/{{ 
                $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
                }}/{{ $fld->fld_name }}"><i class="fa fa-pencil"></i></a></td>
            <td class="w40 pB10">
                <span class="fPerc80"><i>{{ $GLOBALS['SL']->tblEng[$fld->fld_table] }}</i></span><br />
                <b>{{ $fld->fld_eng }}</b><br />
                <div class="fPerc80">{{ $FldDataTypes[$fld->fld_type][1] }}
                @if (intVal($fld->fld_foreign_table) > 0) 
                    - <i class="fa fa-link"></i>{!! view(
                        'vendor.survloop.admin.db.inc-getTblName', 
                        [
                            "id"      => $fld->fld_foreign_table, 
                            "link"    => 0, 
                            "xtraTxt" => ' ID', 
                            "xtraLnk" => ''
                        ]
                    )->render() !!}
                @endif
                </div>
            </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->fld_id }}" 
                        id="fldXML{{ $fld->fld_id }}a" value="1"
                        @if ($fld->fld_opts%5 > 0 && $fld->fld_opts%7 > 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->fld_id }}, this.value);" 
                        > Public Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->fld_id }}" 
                        id="fldXML{{ $fld->fld_id }}b" value="7"
                        @if ($fld->fld_opts%7 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->fld_id }}, this.value);" 
                        > Private Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->fld_id }}" 
                        id="fldXML{{ $fld->fld_id }}c" value="11"
                        @if ($fld->fld_opts%11 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->fld_id }}, this.value);" 
                        > Sensitive Data</label>
                </td>
                <td class="w15">
                    <label><input type="radio" name="fldXML{{ $fld->fld_id }}" 
                        id="fldXML{{ $fld->fld_id }}c" value="13"
                        @if ($fld->fld_opts%13 == 0) CHECKED @endif
                        onClick="return saveXmlSetting({{ $fld->fld_id }}, this.value);" 
                        > Internal Use</label>
                </td>
            </tr>
            
        @endforeach
        
    @endif

@empty

@endforelse

</table>
</div></div>

<div class="p20"></div><div class="p20"></div>
@endsection