<!-- resources/views/vendor/survloop/admin/db/inc-addCondition.blade.php -->
<input type="hidden" name="condID" value="-3">

<a href="javascript:void(0)" id="addCondLnk" class="btn btn-xs btn-default mB5"><i class="fa fa-plus"></i> Add Condition</a>
<div id="addCond" class="round10 brd p5 f18 disNon">
    <div class="p10">
        <b class="slBlueDark f22"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add New Condition:</b>
        
        @if (!isset($newOnly) || !$newOnly)
            <select id="oldCondsID" name="oldConds" class="form-control">
                <option value="0">Select a condition</option>
                <option value="-37">Create a new condition</option>
                <option value="0" disabled ></option>
                @forelse ($GLOBALS["DB"]->getCondList() as $cond)
                    <option value="{{ $cond->CondID }}">{{ $cond->CondTag }} - {{ $cond->CondDesc }}</option>
                @empty
                @endforelse
            </select>
        @endif
        
        <div id="createNewCond" class=" @if (!isset($newOnly) || !$newOnly) disNon @else disBlo @endif ">
            <div class="row mT10">
                <div class="col-md-4 f24">
                    Data Set:
                </div>
                <div class="col-md-8">
                    <select id="setSelectID" name="setSelect" class="form-control f20" autocomplete=off >
                        <option value="" SELECTED ></option>
                        <option value=""  DISABLED >SurvLoops:</option>
                        @forelse ($GLOBALS["DB"]->dataLoops as $loopName => $loopRow)
                            <option value="loop-{{ $loopRow->DataLoopID }}"> - {{ $loopName }}</option>
                        @empty
                        @endforelse
                        <option value="" DISABLED ></option>
                        {!! $GLOBALS["DB"]->tablesDropdown('12345', 'Database Tables:', ' - ', true) !!}
                    </select>
                </div>
            </div>
            <div id="fldSelect" ></div>
            <div id="valSelect" class="p20" ></div>
            <div id="nameIt" class="disBlo">
                <div class="row">
                    <div class="col-md-4 f24">
                        Hashtag:
                    </div>
                    <div class="col-md-1 f32 taR">
                        <b>#</b>
                    </div>
                    <div class="col-md-7">
                        <input type="text" id="condHashID" name="condHash" class="form-control f20">
                    </div>
                </div>
                <div class="row pT5">
                    <div class="col-md-4 f24">
                        Description:
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="condDescID" name="condDesc" class="form-control f20">
                    </div>
                </div>
                <div class="pT20 taC">
                    <input type="submit" value="Add Condition" class="btn btn-lg btn-primary" >
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript"> 
    $(document).ready(function(){
        $("#oldCondsID").change(function() {
            if (document.getElementById("oldCondsID").value >= 0) $("#createNewCond").slideUp('fast');
            else $("#createNewCond").slideDown('fast');
                
        });
        $("#setSelectID").change(function() {
            //alert("/dashboard/db/ajax/getSetFlds/"+encodeURIComponent(document.getElementById("setSelectID").value)+"");
            $("#fldSelect").load("/dashboard/db/ajax/getSetFlds/"+encodeURIComponent(document.getElementById("setSelectID").value)+"");
            document.getElementById("valSelect").innerHTML = '';
            //document.getElementById("nameIt").style.display = 'none';
        });
        $("#addCondLnk").click(function() { 
            $("#addCond").slideToggle('fast');
        });
    });
    </script>
</div>