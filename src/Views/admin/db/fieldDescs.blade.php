<!-- resources/views/vendor/survloop/admin/db/fieldDescs.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<h1><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>: Field Descriptions</h1>
@if ($view == 'replicas' || $view == 'generics' || $view == 'uniques')
    <a href="/dashboard/db/fieldDescs" class="btn btn-sm btn-secondary mR10">All Field Descriptions</a>
@endif

<div class="clearfix p5"></div>

<div class="row mB20">
@if ($view == 'replicas' || $view == 'generics' || $view == 'uniques')
    <div class="col-6">
        @if ($view != 'replicas' && $view != 'generics')
            <h3 class="m0">Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</h3>
        @else 
            <a href="/dashboard/db/fieldDescs?view=uniques" 
                >Unique Fields ({{ $fldTots[0][0] }}/{{ $fldTots[0][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[0][0]/$fldTots[0][1]) }} %;"></div></div>
    </div><div class="col-6">
        @if ($view == 'replicas') 
            <h3 class="m0">Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</h3>
        @else
            <a href="/dashboard/db/fieldDescs?view=replicas" 
                >Replica Fields ({{ $fldTots[1][0] }}/{{ $fldTots[1][1] }})</a>
        @endif
        <div class="progBar"><div style="width: {{ round(100*$fldTots[1][0]/$fldTots[1][1]) }}%;"></div></div>
    </div>
@else
    <div class="col-6">
        <h2 class="m0">All Tables, All Fields</h2>
        {{ ($fldTots[0][0]+$fldTots[1][0]) }}/{{ ($fldTots[0][1]+$fldTots[1][1]) }} described
        <!-- 
        <a href="/dashboard/db/fieldDescs?view=uniques"
            >Unique Fields Only</a> - 
        <a href="/dashboard/db/fieldDescs?view=replicas"
            >Replica Fields Only</a>
        -->
    </div>
    <div class="col-6"><div class="progBar"><div style="width: {{ 
        round(100*($fldTots[0][0]+$fldTots[1][0])/($fldTots[0][1]+$fldTots[1][1])) }}%;"></div></div>
    </div>
@endif
</div>

<form name="fieldDescForm" action="/dashboard/db/fieldDescs?table={{ 
    $tblNext }}{{ $viewParam }}&save=1#tbl{{ $tblNext }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="changedFlds" id="changedFldsID" value=",">
<input type="hidden" name="changedFldsGen" id="changedFldsGenID" value=",">
@foreach ($GLOBALS["SL"]->tbls as $loopTblID)
    @if ($tblID != $loopTblID)
        <div class="card">
            <a href="/dashboard/db/fieldDescs?table={{ 
                $loopTblID }}{{ $viewParam }}#tbl{{ $loopTblID }}">
            <div class="card-header">
                <h3>{{ $GLOBALS["SL"]->tbl[$loopTblID] }} 
                ({{ (sizeof($GLOBALS["SL"]->fldTypes[$GLOBALS["SL"]->tbl[$loopTblID]])-1) }} 
                {{ $fldLabel }})
                <i class="fa fa-chevron-down float-right" aria-hidden="true"></i></h3>
            </div></a>
        </div>
    @else
        <div class="nodeAnchor"><a name="tbl{{ $loopTblID }}"></a></div>
        <div class="card">
            <div class="card-header">
                <h3>{{ $GLOBALS["SL"]->tbl[$tblID] }} 
                    ({{ $tblFldLists->count() }} {{ $fldLabel }})</h3>
            </div>
            <div class="card-body">
                <div class="pL10 pR10"><div class="row slGrey">
                    <div class="col-4">Field Label</div>
                    <div class="col-6">Full Description</div>
                    <div class="col-2">Internal Notes</div>
                </div></div>
                @forelse ($tblFldLists as $cnt => $fld)
                    <div class="p10 @if ($cnt%2 == 0) row2 @endif ">
                        <div class="row" >
                            <div class="col-4">
                                <input name="FldEng{{ $fld->fld_id }}" class="form-control bordC w100"
                                    @if ($fld->fld_spec_type == 'Generic') 
                                        onKeyUp="logFldGenDescChange({{ $fld->fld_id }});"
                                    @else onKeyUp="logFldDescChange({{ $fld->fld_id }});"
                                    @endif value="{{ str_replace('"', '\\"', $fld->fld_eng) }}" >
                                <div class="fPerc80">
                                    <a href="/dashboard/db/field/{{ 
                                        $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
                                        }}/{{ $fld->fld_name }}" class="mR5" target="_blank"
                                        ><i class="fa fa-pencil fa-flip-horizontal"></i></a>
                                    <b>{{ $GLOBALS['SL']->tblEng[$fld->fld_table] }} : {{ $fld->fld_name }}</b> - 
                                    {{ $FldDataTypes[$fld->fld_type][1] }}
                                    @if (intVal($fld->fld_foreign_table) > 0)
                                        - <i class="fa fa-link"></i>{!! view(
                                            'vendor.survloop.admin.db.inc-getTblName', 
                                            [
                                                "id" => $fld->fld_foreign_table, 
                                                "link" => 0, 
                                                "xtraTxt" => ' ID', 
                                                "xtraLnk" => ''
                                            ]
                                        )->render() !!}
                                    @endif
                                    @if (trim($fld->fld_values) != '' || trim($tblFldVals[$fld->fld_id]) != '')
                                        - <span class="slGrey">{{ $tblFldVals[$fld->fld_id] 
                                        }}</span>
                                    @endif
                                </div>
                            </div><div class="col-6">
                                <textarea name="FldDesc{{ $fld->fld_id }}" 
                                    class="form-control w100" 
                                @if ($fld->fld_spec_type == 'Generic') 
                                    onKeyUp="logFldGenDescChange({{ $fld->fld_id }});"
                                @else onKeyUp="logFldDescChange({{ $fld->fld_id }});"
                                @endif >{{ $fld->fld_desc }}</textarea>
                            </div><div class="col-2">
                                <textarea name="FldNotes{{ $fld->fld_id }}" 
                                    class="form-control w100 slGrey fPerc80"
                                @if ($fld->fld_spec_type == 'Generic') onKeyUp="logFldGenDescChange({{ $fld->fld_id }});"
                                @else onKeyUp="logFldDescChange({{ $fld->fld_id }});"
                                @endif >{{ $fld->fld_notes }}</textarea>
                            </div>
                        </div>
                        @if (trim($tblFldQuestion[$fld->fld_id]) != '')
                            <div class="pT10">
                                <span class="slGrey">Question:</span>
                                <span class="slBlueDark">{{ $tblFldQuestion[$fld->fld_id] }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                @endforelse
                <a class="btn btn-lg btn-primary btn-block mT20" data-toggle="tooltip" data-placement="top" 
                    title="*Saving will push changes to all copies of this field (Replicas)."
                    onClick="submitFldDescChanges();" href="javascript:;" 
                    ><i class="fa fa-floppy-o mR5"></i> Save All Changes To This Table's Fields</a>
            </div>
        </div>
    @endif
@endforeach
</form>

<script type="text/javascript">
function logFldDescChange(fldID) {
    if (document.getElementById("changedFldsID").value.indexOf(","+fldID+",") < 0) {
        document.getElementById("changedFldsID").value += ""+fldID+",";
    }
}
function submitFldDescChanges() {
    document.fieldDescForm.submit();
    setTimeout("document.getElementById('changedFldsID').value=','", 1000);
    setTimeout("document.getElementById('changedFldsGenID').value=','", 1000);
}
@if ($view == 'generics')
    function logFldGenDescChange(fldID) {
        if (document.getElementById("changedFldsGenID").value.indexOf(","+fldID+",") < 0) {
            document.getElementById("changedFldsGenID").value += ""+fldID+",";
        }
    }
    </script><span class="red">*</span> <b>WARNING:</b> Saving changes to Generic field descriptions here will push 
        those changes to all copies of the field (its Replicas).<br /><br />
@else 
    </script>
@endif

<div class="p20"></div><div class="p20"></div>
@endsection