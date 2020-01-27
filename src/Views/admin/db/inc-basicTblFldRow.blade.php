<!-- resources/views/vendor/survloop/admin/db/inc-basicTblFldRow.blade.php -->
<tr><td>
    @if (isset($fld->fld_table) && intVal($fld->fld_table) > 0 
        && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]]))
        <div class="nodeAnchor">
            <div class="nodeAnchor"><a name="{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
            }}{{ $fld->fld_name }}"></a></div>
        </div>
    @endif
    <div class="row">
        <div class="col-9">
            @if ($tblLinks > 0 && $dbAllowEdits && !$isPrint)
                <a @if ($fld->fld_table > 0 && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]])) 
                    href="/dashboard/db/field/{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
                        }}/{{ $fld->fld_name }}"
                @else
                    href="/dashboard/db/field/generic/{{ $fld->fld_name }}/{{ $fld->fld_name }}"
                @endif
                    ><i class="fa fa-pencil fa-flip-horizontal mR5"></i> <h4 class="disIn">
                    @if ($tblID != $fld->fld_table && isset($GLOBALS['SL']->tbl[$fld->fld_table]))
                        <span class="slGreenDark">{{ $GLOBALS['SL']->tbl[$fld->fld_table] }}:</span>
                    @endif {{ $fld->fld_eng }}</h4></a>
            @else 
                <h4 class="disIn"> @if ($tblID != $fld->fld_table && isset($GLOBALS['SL']->tbl[$fld->fld_table]))
                    <span class="slGreenDark">{{ $GLOBALS['SL']->tbl[$fld->fld_table] }}:</span>
                @endif {{ $fld->fld_eng }}</h4>
            @endif
            <a id="fldSpecBtn{{ $fld->fld_id }}" href="javascript:;"></a>
            @if ($fld->fld_spec_type == 'Replica') 
                <span class="slGrey fPerc80" data-toggle="tooltip" data-placement="top" 
                    title="Replica field (copy of a Generic field)"><sup>^</sup></span>
            @endif
            
            <div id="fldSpecA{{ $fld->fld_id }}" class=" @if ($isAll && $fld->fld_id > 0) disNon @else disBlo @endif ">
                @if (trim($fld->fld_desc) != '') <div class="pL20">{!! $fld->fld_desc !!}</div> @endif
                @if (trim($fld->fld_notes) != '') 
                    <div class="pL20 slGrey"><i class="mR5">Notes:</i> {!! $fld->fld_notes !!}</div>
                @endif
                @if ($fld->fld_id > 0 && isset($dbBusRulesFld[$fld->fld_id]))
                    <div><a href="/dashboard/db/bus-rules/edit/{{ $dbBusRulesFld[$fld->fld_id][0] }}" 
                        data-toggle="tooltip" data-placement="top" target="_blank" 
                        title="{!! str_replace('"', "'", strip_tags($dbBusRulesFld[$fld->fld_id][1])) !!}" 
                        ><i class="fa fa-university"></i></a></div>
                @endif
            </div>
            
            @if (trim($FldValues) != '' || trim($fld->fld_default) != '')
                <div class="pL20 slGrey">
                @if (trim($FldValues) != '')
                    <i class="mR5">Values:</i>
                    @if (strpos($FldValues, 'Def::') !== false)
                        {{ str_replace('Def::', '', $FldValues) }} <span class="fPerc80">(Definitions)</span>
                    @else
                        {{ $FldValues }}
                    @endif
                @endif
                @if (trim($fld->fld_default) != '')
                    <i class="slGrey mL10">( default value: {{ $fld->fld_default }} )</i>
                @endif
                </div>
            @endif
            
        </div>
        <div class="col-3 taR slGrey">
        
            @if ($tblID > 0 && isset($GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]]) && isset($fld->fld_name))
                {!! $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]] 
                    . (($tblID != $fld->fld_table && isset($GLOBALS['SL']->tbl[$fld->fld_table])) 
                        ? '<span class="slGreenDark">' . $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
                        . $fld->fld_name . '</span>' : $fld->fld_name) !!}<br />
            @endif
            @if (strpos($fld->fld_key_type, 'Primary') !== false)
                @if ($fld->fld_key_struct == 'Composite') Composite, @endif
                <b>Primary Key,</b> 
            @endif
            <nobr><i>
            @if (isset($FldDataTypes[$fld->fld_type])) {{ $FldDataTypes[$fld->fld_type][1] }}  @endif
            @if ($fld->fld_is_index == 1) <span class="slGrey fPerc80">Indexed</span> @endif
            </i></nobr>
            @if (trim($fldForeignPrint) != '' || trim($fldGenerics) != '')
                <br />@if (trim($fldForeignPrint) != '') {!! $fldForeignPrint !!} @endif
                @if (trim($fldGenerics) != '') {!! $fldGenerics !!} @endif
            @endif
            
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @if ($isAll && $fld->fld_id > 0) 
                <div id="fldSpec{{ $fld->fld_id }}" class="disBlo p20 m20">
                    {!! view(
                        'vendor.survloop.admin.db.fieldSpecifications', 
                        [ 
                            "fld"             => $fld, 
                            "fldSfx"        => $fld->fld_id,
                            "FldDataTypes"    => $FldDataTypes,
                            "help"            => $help, 
                            "edit"            => false, 
                            "chkDis"        => ' disabled ',
                            "defSet"        => ((strpos($fld->fld_values, 'Def::') !== false 
                                || strpos($fld->fld_values, 'DefX::') !== false) 
                                ? trim(str_replace('Def::', '', str_replace('DefX::', '', $fld->fld_values))) : '')
                        ]
                    )->render() !!}
                </div>
            @else 
                <div id="fldSpec{{ $fld->fld_id }}" class="disNon p20 m20"></div>
            @endif
        </div>
    </div>
<td></tr>
