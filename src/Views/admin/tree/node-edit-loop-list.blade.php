<!-- resources/views/vendor/survloop/admin/tree/node-edit-loop-list.blade.php -->
<div class="row">
    <div class="col-6 nFld mT0">
        <select name="{{ $fld }}Type" id="{{ $fld }}TypeID" class="form-control form-control-lg" autocomplete="off" 
            onChange="changeLoopListType('{{ $fld }}');" >
            @if ($manualOpt) 
                <option value="manual" @if ($currDefinition == '' && $currLoopItems == '' && $currTblRecs == '') 
                SELECTED @endif > Manually type options below </option>
            @endif
            <option value="auto-def" @if ($currDefinition != '') SELECTED @endif
                > Pull from Definition Set </option>
            <option value="auto-loop" @if ($currLoopItems != '') SELECTED @endif
                > Pull from Entered Loop Items </option>
            <option value="auto-tbl" @if ($currTblRecs != '') SELECTED @endif
                > Pull from Entered Table Records </option>
        <select>
    </div>
    <div class="col-6 nFld mT0">
        <div id="{{ $fld }}Defs" class=" @if ($currDefinition != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}Definition" id="{{ $fld }}DefinitionID" autocomplete="off"
                class="form-control form-control-lg" onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currDefinition == '') SELECTED @endif > Select Definition Set... </option>
                @forelse ($defs as $def)
                    @if (trim($def->DefSubset) != '')
                        <option value="{{ $def->DefSubset }}" @if ($currDefinition == $def->DefSubset) SELECTED @endif 
                            >{{ $def->DefSubset }}</option>
                    @endif
                @empty
                @endforelse
                <option value="--STATES--" @if ($currDefinition == '--STATES--') SELECTED @endif >
                    @if ($GLOBALS["SL"]->sysOpts['has-canada']) U.S. & Canadian States @else U.S. States @endif
                    </option>
            </select>
        </div>
        <div id="{{ $fld }}Loops" class=" @if ($currLoopItems != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}LoopItems" id="{{ $fld }}LoopItemsID" autocomplete="off"
                class="form-control form-control-lg" onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currLoopItems == '') SELECTED @endif > Select Loop... </option>
                @forelse ($GLOBALS['SL']->dataLoops as $plural => $loop)
                    <option value="{{ $plural }}" @if ($currLoopItems == $plural) SELECTED @endif 
                        >{{ $plural }}</option>
                @empty
                @endforelse
            </select>
        </div>
        <div id="{{ $fld }}Tbls" class=" @if ($currTblRecs != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}Tables" id="{{ $fld }}TablesID" autocomplete="off"
                class="form-control form-control-lg" onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currTblRecs == '') SELECTED @endif > Select Data Table... </option>
                @forelse ($GLOBALS['SL']->tbl as $tID => $tblName)
                    <option value="{{ $tblName }}" @if ($currTblRecs == $tblName) SELECTED @endif >
                        @if ($GLOBALS['SL']->tblEng[$tID] != $tblName)
                            {{ $GLOBALS['SL']->tblEng[$tID] }} ({{ $tblName }})
                        @else {{ $GLOBALS['SL']->tblEng[$tID] }} @endif </option>
                @empty
                @endforelse
            </select>
        </div>
    </div>
</div>