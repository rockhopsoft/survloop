<!-- resources/views/vendor/survloop/admin/db/ajaxTblFldSelectorF.blade.php -->

<input type="hidden" name="RuleFields" id="RuleFieldsID" value="{{ urldecode($rF) }}">
@forelse ($fldList as $i => $fld)
    @if ($i > 0) ,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @endif
    <nobr>{!! $fld[1] !!}
    <a href="javascript:;" onClick="return delFld({{ $fld[0] }});" 
        class="red f12 mL5"><span class="glyphicon glyphicon-remove"></span></a></nobr>
@empty
@endforelse
<br />

<span class="fPerc80">Add Field:</span> 
<select name="addFT" id="addFTID" onChange="changeTbl();">{!! $tblDrop !!}</select>
<script type="text/javascript"> 
function changeTbl() { $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/{{ $rF }}?addT="+document.getElementById("addFTID").value+""); } 
function delFld(delF) { $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/{{ $rF }}?delF="+delF+""); }
</script>

@if (intVal($addT) > 0)
    <select name="addF" id="addFID">{!! $fldDrop !!}</select>
    <a href="javascript:;" id="addFbtn"><i class="fa fa-plus-circle"></i></a>
    <script type="text/javascript"> 
    $(document).ready(function(){ $("#addFbtn").click(function(){
        $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/{{ $rF }}?addF="+document.getElementById("addFID").value+""); });
    });
    </script>
@else
    ...
@endif

