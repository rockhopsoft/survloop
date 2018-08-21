<!-- resources/views/vendor/survloop/admin/db/fieldDescs.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<h1><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Field Descriptions</h1>
@if ($view == 'replicas' || $view == 'generics' || $view == 'uniques')
    <a href="/dashboard/db/fieldDescs" class="btn btn-xs btn-default mR10">All Field Descriptions</a>
@endif

<div class="clearfix p5"></div>

<div class="row mB20">
@if ($view == 'replicas' || $view == 'generics' || $view == 'uniques')
    <div class="col-md-6">
        @if ($view != 'replicas' && $view != 'generics')
            <h3 class="m0">Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</h3>
        @else 
            <a href="/dashboard/db/fieldDescs?view=uniques" 
                >Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[0][0]/$fldTots[0][1]) }} %;"></div></div>
    </div><div class="col-md-6">
        @if ($view == 'replicas') 
            <h3 class="m0">Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</h3>
        @else
            <a href="/dashboard/db/fieldDescs?view=replicas" 
                >Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[1][0]/$fldTots[1][1]) }}%;"></div></div>
    </div>
@else
    <div class="col-md-6">
        <h2 class="m0">All Tables, All Fields</h2>
        {{ ($fldTots[0][0]+$fldTots[1][0]) }}/{{ ($fldTots[0][1]+$fldTots[1][1]) }} described
        <!-- 
        <a href="/dashboard/db/fieldDescs?view=uniques">Unique Fields Only</a> - 
        <a href="/dashboard/db/fieldDescs?view=replicas">Replica Fields Only</a>
        -->
    </div>
    <div class="col-md-6"><div class="progBar"><div style="width: {{ 
        round(100*($fldTots[0][0]+$fldTots[1][0])/($fldTots[0][1]+$fldTots[1][1])) }}%;"></div></div>
    </div>
@endif
</div>

<form name="fieldDescForm" action="/dashboard/db/fieldDescs?table={{ $tblNext }}{{ $viewParam }}&save=1#tbl{{ $tblNext 
    }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="changedFLds" id="changedFLdsID" value=",">
<input type="hidden" name="changedFLdsGen" id="changedFLdsGenID" value=",">
@foreach ($GLOBALS["SL"]->tbls as $loopTblID)
    @if ($tblID != $loopTblID)
        <div class="panel panel-default">
            <a href="/dashboard/db/fieldDescs?table={{ $loopTblID }}{{ $viewParam }}#tbl{{ $loopTblID }}">
            <div class="panel-heading">
                <h3 class="panel-title">{{ $GLOBALS["SL"]->tbl[$loopTblID] }} 
                    ({{ (sizeof($GLOBALS["SL"]->fldTypes[$GLOBALS["SL"]->tbl[$loopTblID]])-1) }} 
                    {{ $fldLabel }})
                    <i class="fa fa-chevron-down pull-right" aria-hidden="true"></i></h3>
            </div></a>
        </div>
    @else
        <div class="nodeAnchor"><a name="tbl{{ $loopTblID }}"></a></div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{{ $GLOBALS["SL"]->tbl[$tblID] }} 
                    ({{ $tblFldLists->count() }} {{ $fldLabel }})</h3>
            </div>
            <div class="panel-body">
                <div class="pL10 pR10"><div class="row slGrey">
                    <div class="col-md-4">Field Label</div>
                    <div class="col-md-6">Full Description</div>
                    <div class="col-md-2">Internal Notes</div>
                </div></div>
                @forelse ($tblFldLists as $cnt => $fld)
                    <div class="p10 @if ($cnt%2 == 0) row2 @endif ">
                        <div class="row" >
                            <div class="col-md-4">
                                <input name="FldEng{{ $fld->FldID }}" class="form-control bordC w100"
                                    @if ($fld->FldSpecType == 'Generic') 
                                        onKeyUp="logFldGenDescChange({{ $fld->FldID }});"
                                    @else onKeyUp="logFldDescChange({{ $fld->FldID }});"
                                    @endif value="{{ str_replace('"', '\\"', $fld->FldEng) }}" >
                                <div class="fPerc80">
                                    <a href="/dashboard/db/field/{{ 
                                        $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] 
                                        }}/{{ $fld->FldName }}"
                                        class="mR5" target="_blank"><i class="fa fa-pencil fa-flip-horizontal"></i></a>
                                    <b>{{ $GLOBALS['SL']->tblEng[$fld->FldTable] }} : {{ $fld->FldName }}</b> - 
                                    {{ $FldDataTypes[$fld->FldType][1] }}
                                    @if (intVal($fld->FldForeignTable) > 0)
                                        - <i class="fa fa-link"></i>{!! 
                                            view('vendor.survloop.admin.db.inc-getTblName', [
                                                "id" => $fld->FldForeignTable, "link" => 0, "xtraTxt" => ' ID', 
                                                "xtraLnk" => '' ])->render() !!}
                                    @endif
                                    @if (trim($fld->FldValues) != '' || trim($tblFldVals[$fld->FldID]) != '')
                                        - <span class="slGrey">{{ $tblFldVals[$fld->FldID] }}</span>
                                    @endif
                                </div>
                            </div><div class="col-md-6">
                                <textarea name="FldDesc{{ $fld->FldID }}" class="form-control w100" 
                                @if ($fld->FldSpecType == 'Generic') onKeyUp="logFldGenDescChange({{ $fld->FldID }});"
                                @else onKeyUp="logFldDescChange({{ $fld->FldID }});"
                                @endif >{{ $fld->FldDesc }}</textarea>
                            </div><div class="col-md-2">
                                <textarea name="FldNotes{{ $fld->FldID }}" class="form-control w100 slGrey fPerc80"
                                @if ($fld->FldSpecType == 'Generic') onKeyUp="logFldGenDescChange({{ $fld->FldID }});"
                                @else onKeyUp="logFldDescChange({{ $fld->FldID }});"
                                @endif >{{ $fld->FldNotes }}</textarea>
                            </div>
                        </div>
                        @if (trim($tblFldQuestion[$fld->FldID]) != '')
                            <div class="pT10">
                                <span class="slGrey">Question:</span>
                                <span class="slBlueDark">{{ $tblFldQuestion[$fld->FldID] }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                @endforelse
                <a class="btn btn-lg btn-primary w100 mT20" data-toggle="tooltip" data-placement="top" 
                    title="*Saving will push changes to all copies of this field (Replicas)."
                    onClick="submitFldDescChanges();" href="javascript:;" 
                    ><i class="fa fa-floppy-o mR5"></i> Save All Changes To This Table's Fields</a>
            </div>
        </div>
    @endif
@endforeach
</form>

<script type="text/javascript">
function logFldDescChange(FldID) {
    if (document.getElementById("changedFLdsID").value.indexOf(","+FldID+",") < 0) {
        document.getElementById("changedFLdsID").value += FldID+",";
    }
}
function submitFldDescChanges() {
    document.fieldDescForm.submit();
    setTimeout("document.getElementById('changedFLdsID').value=','", 1000);
    setTimeout("document.getElementById('changedFLdsGenID').value=','", 1000);
}
@if ($view == 'generics')
    function logFldGenDescChange(FldID) {
        if (document.getElementById("changedFLdsGenID").value.indexOf(","+FldID+",") < 0) {
            document.getElementById("changedFLdsGenID").value += FldID+",";
        }
    }
    </script><span class="red">*</span> <b>WARNING:</b> Saving changes to Generic field descriptions here will push 
        those changes to all copies of the field (its Replicas).<br /><br />
@else 
    </script>
@endif

<div class="p20"></div><div class="p20"></div>
@endsection