<!-- resources/views/vendor/survloop/admin/tree/node-edit-loop-list.blade.php -->
<div class="row">
    <div class="col-6 nFld mT0">
        <select name="{{ $fld }}Type" id="{{ $fld }}TypeID" 
            class="form-control form-control-lg" autocomplete="off" 
            onChange="changeLoopListType('{{ $fld }}');" >
            @if ($manualOpt) 
                <option value="manual" 
                @if ($currDefinition  == '' 
                    && $currLoopItems == '' 
                    && $currTblRecs   == '' 
                    && $currMonthFld  == '') 
                    SELECTED 
                @endif > Manually type options below </option>
            @endif
            <option value="auto-def" @if ($currDefinition != '') SELECTED @endif
                > Pull from Definition Set </option>
            <option value="auto-loop" @if ($currLoopItems != '') SELECTED @endif
                > Pull from entered loop items </option>
            <option value="auto-tbl" @if ($currTblRecs != '') SELECTED @endif
                > Pull from entered table records </option>
            <option value="auto-tbl-all" @if ($currTblAll != '') SELECTED @endif
                > Pull from all table records </option>
            <option value="auto-months" @if ($currMonthFld != '') SELECTED @endif
                > Months counting back from starting month field</option>
        <select>
    </div>
    <div class="col-6 nFld mT0">
        <div id="{{ $fld }}Defs" class=" @if ($currDefinition != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}Definition" id="{{ $fld }}DefinitionID"
                class="form-control form-control-lg" autocomplete="off" 
                onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currDefinition == '') SELECTED @endif
                    > Select Definition Set... </option>
                @forelse ($defs as $def)
                    @if (trim($def->def_subset) != '')
                        <option value="{{ $def->def_subset }}" 
                            @if ($currDefinition == $def->def_subset) SELECTED @endif 
                            >{{ $def->def_subset }}</option>
                    @endif
                @empty
                @endforelse
                <option value="--STATES--" 
                    @if ($currDefinition == '--STATES--') SELECTED @endif >
                    @if ($GLOBALS["SL"]->sysOpts['has-canada']) U.S. & Canadian States 
                    @else U.S. States 
                    @endif
                    </option>
            </select>
        </div>
        <div id="{{ $fld }}Loops" 
            class=" @if ($currLoopItems != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}LoopItems" id="{{ $fld }}LoopItemsID"
                class="form-control form-control-lg" autocomplete="off"
                onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currLoopItems == '') SELECTED @endif 
                    > Select Loop... </option>
                @forelse ($GLOBALS['SL']->dataLoops as $plural => $loop)
                    <option value="{{ $plural }}" 
                        @if ($currLoopItems == $plural) SELECTED @endif 
                        >{{ $plural }}</option>
                @empty
                @endforelse
            </select>
        </div>
        <div id="{{ $fld }}Tbls" 
            class=" @if ($currTblRecs != '' || $currTblAll != '') disBlo @else disNon @endif ">
            <select name="{{ $fld }}Tables" id="{{ $fld }}TablesID" autocomplete="off"
                class="form-control form-control-lg" onChange="changeLoopListType('{{ $fld }}');" >
                <option value="" @if ($currTblRecs == '' && $currTblAll == '') SELECTED @endif 
                    > Select Data Table... </option>
                @forelse ($GLOBALS['SL']->tbl as $tID => $tblName)
                    <option value="{{ $tblName }}" 
                        @if ($currTblRecs == $tblName || $currTblAll == $tblName) SELECTED @endif >
                        @if ($GLOBALS['SL']->tblEng[$tID] != $tblName)
                            {{ $GLOBALS['SL']->tblEng[$tID] }} ({{ $tblName }})
                        @else {{ $GLOBALS['SL']->tblEng[$tID] }} 
                        @endif </option>
                @empty
                @endforelse
            </select>
            <div id="{{ $fld }}TblCond" 
                class=" @if ($currTblAll != '') disBlo @else disNon @endif ">
                <select name="{{ $fld }}TableCond" id="{{ $fld }}TableCondID"
                    class="form-control form-control-lg" autocomplete="off">
                    <option value="0" @if (intVal($currTblAllCond) == 0) SELECTED @endif 
                        >Select a condition related to this table</option>
                @forelse ($GLOBALS['SL']->getCondList() as $c)
                    <option value="{{ $c->cond_id }}" 
                        @if (intVal($currTblAllCond) == $c->cond_id) SELECTED @endif 
                        >{{ $c->cond_tag }} - {{ $c->cond_desc }}</option>
                @empty
                @endforelse
                </select>
            </div>
        </div>
        <div id="{{ $fld }}Months" 
            class=" @if ($currMonthFld != '') disBlo @else disNon @endif ">
        @if (isset($GLOBALS["SL"]->x["nodeDropdownOpts"]))
            <select name="{{ $fld }}MonthFld" id="{{ $fld }}MonthFldID"
                class="form-control form-control-lg" autocomplete="off">
                <option value="" @if ($currMonthFld == '') SELECTED @endif
                    > Select field for starting month... </option>
                {!! $monthNodeOpts !!}
                <?php /*
                {!! $GLOBALS['SL']->fieldsDropdown($currMonthFld) !!}
                */ ?>
            </select>
        @endif
        </div>
    </div>
</div>