<!-- resources/views/survloop/forms/formtree-table.blade.php -->
<div id="node{{ $nIDtxt }}{{ trim($GLOBALS['SL']->currCyc['tbl'][1]) }}" 
    class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">{!! $nodePromptText !!}</div>
    <div class="nFld"><table id="sprdTbl{{ $nIDtxt }}" class="table slSpreadTbl">
    <tr>
    @if (trim($tableDat["rowCol"]) != '') 
        <th class="sprdRowLab">&nbsp;</th>
    @endif
    @forelse ($tableDat["cols"] as $k => $col)
        <th class=" @if (trim($tableDat['rowCol']) != '' || $k > 0) cl1 @endif " >
        @if (isset($col->nodePromptText) 
            && trim($col->nodePromptText) != '') 
            {!! $col->nodePromptText !!}
        @elseif (isset($col->nodeRow->node_prompt_text)
            && trim($col->nodeRow->node_prompt_text) != '')
            {!! $col->nodeRow->node_prompt_text !!}
        @elseif (isset($col->node_prompt_text))
            {!! $col->node_prompt_text !!}
        @endif
        </th>
    @empty
    @endforelse
    @if (trim($tableDat["rowCol"]) == '') <th class="c1">&nbsp;</th> @endif
    </tr>
    @forelse ($tableDat["rows"] as $j => $row)
        <tr id="n{{ $nIDtxt }}tbl{{ $j }}row" class=" @if ($j%2 > 0) rw2 @endif " >
            @if (trim($tableDat["rowCol"]) != '' || trim($tableDat["month"]) != '')
                <td id="n{{ $nIDtxt }}tbl{{ $j }}rowLab" class="sprdRowLab">
                    {!! $GLOBALS["SL"]->swapMonthNum($row["leftTxt"]) !!}
                </td>
            @endif
            @if (trim($tableDat["rowCol"]) != '' && trim($tableDat["month"]) == '')
                <input type="hidden" name="n{{ $nIDtxt }}tbl{{ $j }}fldDef" 
                    id="n{{ $nIDtxt }}tbl{{ $j }}fldDefID" 
                    value="{{ $row['leftVal'] }}"
                    >
            @else
                <input type="hidden" name="n{{ $nIDtxt }}tbl{{ $j }}fldRow" 
                    id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" 
                    value="{{ $row['id'] }}" >
            @endif
            @forelse ($row["cols"] as $k => $col)
                <td id="n{{ $nIDtxt }}tbl{{ $j }}row{{ $k }}col" class="sprdFld 
                    @if (trim($tableDat['rowCol']) != '' || $k > 0) cl1 @endif ">
                    @if (is_array($col))
                        {!! str_replace('nFld', '', 
                            str_replace('nFld mT0', '', $col[0])) !!}
                    @else
                        {!! str_replace('nFld', '', 
                            str_replace('nFld mT0', '', $col)) !!}
                    @endif
                </td>
            @empty
            @endforelse
            @if (trim($tableDat["rowCol"]) == '')
                <td class="taC"><div class="pT5">
                    <a href="javascript:;" class="delSprdTblRow" 
                        data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}" 
                        data-row-ind="{{ $j }}"
                        ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                </div></td>
            @endif
        </tr>
    @empty
    @endforelse
    @if (trim($tableDat["rowCol"]) == '')
        @for ($j = sizeof($tableDat["rows"]); $j < $node->nodeRow->node_char_limit; $j++)
            <tr id="n{{ $nIDtxt }}tbl{{ $j }}row" class=" @if ($j%2 > 0) rw2 @endif 
                @if ($j < sizeof($tableDat['rows']) 
                    || ($j == 0 && sizeof($tableDat['rows']) == 0)) 
                    disRow 
                @else
                    disNon
                @endif " >
                <input name="n{{ $nIDtxt }}tbl{{ $j }}fldRow" type="hidden"
                    id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" value="-3">
                @forelse ($tableDat["cols"] as $k => $col)
                    <td id="n{{ $nIDtxt }}tbl{{ $j }}row{{ $k }}col" class="sprdFld 
                        @if ($k > 0) cl1 @endif ">{!! str_replace('tbl?', 'tbl' . $j, 
                        $GLOBALS["SL"]->replaceTabInd($tableDat["blnk"][$k])) !!}</td>
                @empty
                @endforelse
                @if (trim($tableDat["rowCol"]) == '')
                    <td class="taC"><div class="pT5">
                        <a href="javascript:;" class="delSprdTblRow" 
                            data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}" 
                            data-row-ind="{{ $j }}"
                            ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                    </div></td>
                @endif
            </tr>
        @endfor
        <tr id="n{{ $nIDtxt }}rowAdd" >
            <td class="brdBotNon taR" 
                colspan="{{ (sizeof($tableDat['cols'])+1) }}">
                <a id="addSprdTbl{{ $nIDtxt }}Btn" href="javascript:;" 
                    data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}" 
                    data-row-max="{{ $tableDat['maxRow'] }}" 
                    class="btn btn-ico btn-secondary addSprdTblRow" 
                    >Add <i class="fa fa-plus" aria-hidden="true"></i></a>
            </td>
        </tr>
    @endif
    
    </table></div> <!-- end nFld -->
</div> <!-- end nodeWrap -->