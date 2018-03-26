<!-- resources/views/vendor/survloop/admin/db/fieldDescs.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Field Descriptions</h1>
@if ($FldDescsView == 'replicas' || $FldDescsView == 'generics' || $FldDescsView == 'uniques')
    <a href="/dashboard/db/fieldDescs/all" class="btn btn-xs btn-default mR10">All Field Descriptions</a>
@endif

<div class="clearfix p10"></div>

<table border=0 class="w100" ><tr>
@if ($FldDescsView == 'replicas' || $FldDescsView == 'generics' || $FldDescsView == 'uniques')
    <td class="vaT pR20 w50">
        @if ($FldDescsView != 'replicas' && $FldDescsView != 'generics')
            <h2 style="margin: 0px;">Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</h2>
        @else 
            <a href="/dashboard/db/fieldDescs/uniques" class="f18">Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[0][0]/$fldTots[0][1]) }} %;"></div></div></td>
    <td class="vaT pL20 pR20 w50">
        @if ($FldDescsView == 'replicas') 
            <h2 style="margin: 0px;">Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</h2>
        @else
            <a href="/dashboard/db/fieldDescs/replicas" class="f18">Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[1][0]/$fldTots[1][1]) }}%;"></div></div></td>
@else
    <td class="w25"><h2 style="margin: 0px;">ALL Fields ({{ ($fldTots[0][0]+$fldTots[1][0]) }}/{{ ($fldTots[0][1]+$fldTots[1][1]) }})</h2></td>
    <td class="w75"><div class="progBar"><div style="width: {{ round(100*($fldTots[0][0]+$fldTots[1][0])/($fldTots[0][1]+$fldTots[1][1])) }}%;"></div></div></td></tr>
    <tr><td colspan=2 ><a href="/dashboard/db/fieldDescs/uniques">Unique Fields Only</a> - <a href="/dashboard/db/fieldDescs/replicas">Replica Fields Only</a></td>
@endif
</tr></table><br />
<form name="mainPageForm" action="/dashboard/db/fieldDescs/save" method="post" target="hidFrame">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="changedFLds" id="changedFLdsID" value=",">
<input type="hidden" name="changedFLdsGen" id="changedFLdsGenID" value=",">
<script type="text/javascript">
function logFldDescChange(FldID) {
    if (document.getElementById("changedFLdsID").value.indexOf(","+FldID+",") < 0) {
        document.getElementById("changedFLdsID").value += FldID+",";
    }
}
function submitFldDescChanges() {
    document.mainPageForm.submit();
    setTimeout("document.getElementById('changedFLdsID').value=','", 1000);
    setTimeout("document.getElementById('changedFLdsGenID').value=','", 1000);
}
@if ($FldDescsView == 'generics')
    function logFldGenDescChange(FldID) {
        if (document.getElementById("changedFLdsGenID").value.indexOf(","+FldID+",") < 0) {
            document.getElementById("changedFLdsGenID").value += FldID+",";
        }
    }
    </script><span class="red">*</span> <b>WARNING:</b> Saving changes to Generic field descriptions here will push those changes to all copies of the field (its Replicas).<br /><br />
@else 
    </script>
@endif
<table border=0 class="FldDescs" >
<tr><td colspan=5 class="p20 headerBrkRow f24" >
    @if ($fldLabel == '') Database @else {{ str_replace('s', '', $fldLabel) }} Fields ({{ number_format(sizeof($fldTot)) }}) @endif
    @if ($FldDescsViewAll)
        <span class="f16">Showing All - </span><a href="{{ $baseURL }}" class="f12 undL">Show Empties Only</a>
    @else
        <span class="f16">Showing Empties Only - </span><a href="{{ $baseURL }}/all" class="f12 undL">Show All</a>
    @endif
</td></tr>

@forelse ($tblFldLists as $tblID => $flds)
    @if ($flds && sizeof($flds) > 0)
        <tr><td colspan=5 class="p20 headerBrkRow f20" >
            Table: <b>{{ $GLOBALS['SL']->tblEng[$tblID] }}</b> 
            ({{ number_format(sizeof($flds)) }} {{ $fldLabel }})
        </td></tr>
        <tr>
            <td class="w5"> </td>
            <td class="w20"><i>Field Info</i></td>
            <td class="w50"><i>Descriptions</i></td>
            <td class="w20"><i>Notes</i></td>
            <td class="w5"><i>Save Changes</i></td>
        </tr>
        @foreach ($flds as $cnt => $fld)
            
            <tr @if ((1+$cnt)%2 == 0) class="row2" @endif >
            <td class="w5"><a href="/dashboard/db/field/{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] 
                }}/{{ $fld->FldName }}" class="fPerc133 m5"><i class="fa fa-pencil"></i></a></td>
            <td class="w20">
                <span class="f8"><i>{{ $GLOBALS['SL']->tblEng[$fld->FldTable] }}</i></span><br />
                <b>{{ $fld->FldEng }}</b><br />
                <div class="f10">
                    {{ $FldDataTypes[$fld->FldType][1] }}
                    @if (intVal($fld->FldForeignTable) > 0) 
                        - <i class="fa fa-link"></i>{!! view('vendor.survloop.admin.db.inc-getTblName', [
                            "id" => $fld->FldForeignTable, "link" => 0, "xtraTxt" => ' ID', "xtraLnk" => ''
                        ])->render() !!}
                    @endif
                    @if (trim($fld->FldValues) != '' || trim($tblFldVals[$fld->FldID]) != '')
                        - <span class="f8"><i>{{ $tblFldVals[$fld->FldID] }}</i></span>
                    @endif
                </div></td>
            <td class="w50"><textarea name="FldDesc{{ $fld->FldID }}" class="eDescs bordC" style="height: 80px; width: 100%;" 
                @if ($fld->FldSpecType == 'Generic')
                    onKeyUp="logFldGenDescChange({{ $fld->FldID }});"
                @else
                    onKeyUp="logFldDescChange({{ $fld->FldID }});"
                @endif
                >{{ $fld->FldDesc }}</textarea></td>
            <td class="w20 gray9"><textarea name="FldNotes{{ $fld->FldID }}" class="eNotes bordC" style="height: 80px; width: 100%;"
                @if ($fld->FldSpecType == 'Generic')
                    onKeyUp="logFldGenDescChange({{ $fld->FldID }});"
                @else
                    onKeyUp="logFldDescChange({{ $fld->FldID }});"
                @endif
                >{{ $fld->FldNotes }}</textarea></td>
            <td class="w5" @if ($fld->FldSpecType == 'Generic') data-toggle="tooltip" data-placement="top" 
                title="*Saving will push changes to all copies of this field (Replicas)." @endif
                ><nobr><a href="javascript:;" onClick="submitFldDescChanges();" class="fPerc133 m5" 
                ><i class="fa fa-floppy-o"></i></a> 
                @if ($fld->FldSpecType == 'Generic') <span class="red">*</span> @endif
                </nobr></td>
            </tr>
            
        @endforeach
    @endif
@empty
@endforelse

</table></form>

<div class="p20"></div><div class="p20"></div>
@endsection