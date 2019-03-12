<!-- resources/views/vendor/survloop/admin/db/inc-basicTblFldRow.blade.php -->
<tr><td>
    @if (isset($fld->FldTable) && intVal($fld->FldTable) > 0 
        && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]]))
        <div class="nodeAnchor">
            <a name="{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] }}{{ $fld->FldName }}"></a>
        </div>
    @endif
    <div class="row">
        <div class="col-9">
            @if ($tblLinks > 0 && $dbAllowEdits && !$isPrint)
                <a @if ($fld->FldTable > 0 && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]])) 
                    href="/dashboard/db/field/{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] 
                        }}/{{ $fld->FldName }}"
                @else
                    href="/dashboard/db/field/generic/{{ $fld->FldName }}/{{ $fld->FldName }}"
                @endif
                    ><i class="fa fa-pencil fa-flip-horizontal mR5"></i> <h4 class="disIn">
                    @if ($tblID != $fld->FldTable && isset($GLOBALS['SL']->tbl[$fld->FldTable]))
                        <span class="slGreenDark">{{ $GLOBALS['SL']->tbl[$fld->FldTable] }}:</span>
                    @endif {{ $fld->FldEng }}</h4></a>
            @else 
                <h4 class="disIn"> @if ($tblID != $fld->FldTable && isset($GLOBALS['SL']->tbl[$fld->FldTable]))
                    <span class="slGreenDark">{{ $GLOBALS['SL']->tbl[$fld->FldTable] }}:</span>
                @endif {{ $fld->FldEng }}</h4>
            @endif
            <a id="fldSpecBtn{{ $fld->FldID }}" href="javascript:;"></a>
            @if ($fld->FldSpecType == 'Replica') 
                <span class="slGrey fPerc80" data-toggle="tooltip" data-placement="top" 
                    title="Replica field (copy of a Generic field)"><sup>^</sup></span>
            @endif
            
            <div id="fldSpecA{{ $fld->FldID }}" class=" @if ($isAll && $fld->FldID > 0) disNon @else disBlo @endif ">
                @if (trim($fld->FldDesc) != '') <div class="pL20">{!! $fld->FldDesc !!}</div> @endif
                @if (trim($fld->FldNotes) != '') 
                    <div class="pL20 slGrey"><i class="mR5">Notes:</i> {!! $fld->FldNotes !!}</div>
                @endif
                @if ($fld->FldID > 0 && isset($dbBusRulesFld[$fld->FldID]))
                    <div><a href="/dashboard/db/bus-rules/edit/{{ $dbBusRulesFld[$fld->FldID][0] }}" 
                        data-toggle="tooltip" data-placement="top" target="_blank" 
                        title="{!! str_replace('"', "'", strip_tags($dbBusRulesFld[$fld->FldID][1])) !!}" 
                        ><i class="fa fa-university"></i></a></div>
                @endif
            </div>
            
            @if (trim($FldValues) != '' || trim($fld->FldDefault) != '')
                <div class="pL20 slGrey">
                @if (trim($FldValues) != '')
                    <i class="mR5">Values:</i>
                    @if (strpos($FldValues, 'Def::') !== false)
                        {{ str_replace('Def::', '', $FldValues) }} <span class="fPerc80">(Definitions)</span>
                    @else
                        {{ $FldValues }}
                    @endif
                @endif
                @if (trim($fld->FldDefault) != '')
                    <i class="slGrey mL10">( default value: {{ $fld->FldDefault }} )</i>
                @endif
                </div>
            @endif
            
        </div>
        <div class="col-3 taR slGrey">
        
            @if ($tblID > 0 && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]]) && isset($fld->FldName))
                {!! $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]] 
                    . (($tblID != $fld->FldTable && isset($GLOBALS['SL']->tbl[$fld->FldTable])) 
                        ? '<span class="slGreenDark">' . $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] 
                        . $fld->FldName . '</span>' : $fld->FldName) !!}<br />
            @endif
            @if (strpos($fld->FldKeyType, 'Primary') !== false)
                @if ($fld->FldKeyStruct == 'Composite') Composite, @endif
                <b>Primary Key,</b> 
            @endif
            <nobr><i>
            @if (isset($FldDataTypes[$fld->FldType])) {{ $FldDataTypes[$fld->FldType][1] }}  @endif
            @if ($fld->FldIsIndex == 1) <span class="slGrey fPerc80">Indexed</span> @endif
            </i></nobr>
            @if (trim($fldForeignPrint) != '' || trim($fldGenerics) != '')
                <br />@if (trim($fldForeignPrint) != '') {!! $fldForeignPrint !!} @endif
                @if (trim($fldGenerics) != '') {!! $fldGenerics !!} @endif
            @endif
            
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @if ($isAll && $fld->FldID > 0) 
                <div id="fldSpec{{ $fld->FldID }}" class="disBlo p20 m20">
                    {!! view('vendor.survloop.admin.db.fieldSpecifications', [ 
                        "fld"             => $fld, 
                        "fldSfx"        => $fld->FldID,
                        "FldDataTypes"    => $FldDataTypes,
                        "help"            => $help, 
                        "edit"            => false, 
                        "chkDis"        => ' disabled ',
                        "defSet"        => ((strpos($fld->FldValues, 'Def::') !== false 
                            || strpos($fld->FldValues, 'DefX::') !== false) 
                            ? trim(str_replace('Def::', '', str_replace('DefX::', '', $fld->FldValues))) : '')
                    ])->render() !!}
                </div>
            @else 
                <div id="fldSpec{{ $fld->FldID }}" class="disNon p20 m20"></div>
            @endif
        </div>
    </div>
<td></tr>
