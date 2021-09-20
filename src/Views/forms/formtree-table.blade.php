<!-- resources/views/survloop/forms/formtree-table.blade.php -->

<div class="nPrompt">{!! $nodePromptText !!}</div>
<div class="nFld">
<table id="sprdTbl{{ $nIDtxt }}" class="table slSpreadTbl">

    <tr>
    @if ($hasCol1)
        <th class="sprdRowLab">&nbsp;</th>
    @endif
    @forelse ($tableDat["cols"] as $k => $col)
        <th id="n{{ $nIDtxt }}tbl{{ $k }}head"
            class=" @if (trim($tableDat['rowCol']) != ''
                || ($hasCol1 && $k > 0)
                || (!$hasCol1 && $k > 1) ) cl1 @endif " >
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
    @if (trim($tableDat["rowCol"]) == '')
        <th class="c1">&nbsp;</th>
    @endif
    </tr>

@forelse ($tableDat["rows"] as $j => $row)
    @if (trim($tableDat["rowCol"]) != '' && trim($tableDat["month"]) == '')
        <input name="n{{ $nIDtxt }}tbl{{ $j }}fldDef"
            id="n{{ $nIDtxt }}tbl{{ $j }}fldDefID"
            type="hidden" value="{{ $row['leftVal'] }}">
    @else
        <input name="n{{ $nIDtxt }}tbl{{ $j }}fldRow"
            id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" type="hidden"
            value="{{ ((isset($row['id'])) ? $row['id'] : -3) }}">
    @endif
    <tr id="n{{ $nIDtxt }}tbl{{ $j }}row"
        class=" @if ($j%2 > 0) rw2 @endif " style="display:
        @if ((isset($row['id']) && $row['id'] > 0)
            || ($j == 0 && sizeof($tableDat['rows']) == 0))
            table-row;
        @else
            none;
        @endif " >
    @if ($hasCol1)
        <td id="n{{ $nIDtxt }}tbl{{ $j }}rowLab" class="sprdRowLab">
            <div id="n{{ $nIDtxt }}tbl{{ $j }}rowLabWrap">
            @if (isset($row["leftTxt"]))
                {!! $GLOBALS["SL"]->swapMonthNum($row["leftTxt"]) !!}
            @endif
            </div>
        </td>
    @endif
    @if (isset($row["cols"]) && sizeof($row["cols"]) > 0)
        @foreach ($row["cols"] as $k => $col)
            <td id="n{{ $nIDtxt }}tbl{{ $j }}row{{ $k }}col"
                class="sprdFld
                @if (trim($tableDat['rowCol']) != ''
                    || ($hasCol1 && $k > 0)
                    || (!$hasCol1 && $k > 1)) cl1 @endif ">
            @if (is_array($col))
                @if (sizeof($col) > 0)
                    {!! str_replace('nFld', '',
                        str_replace('nFld mT0', '', $col[0])) !!}
                @endif
            @else
                {!! str_replace('nFld', '',
                    str_replace('nFld mT0', '', $col)) !!}
            @endif
            </td>
        @endforeach
    @endif
    @if (trim($tableDat["rowCol"]) == '')
        <td id="n{{ $nIDtxt }}tbl{{ $j }}rowDelTd"
            class="taC"><div class="pT5">
            <a href="javascript:;" class="delSprdTblRow" data-row-ind="{{ $j }}"
                data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}"
                ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
        </div></td>
    @endif
    </tr>
@empty
@endforelse

@if (trim($tableDat["rowCol"]) == '')
    @for ($j = sizeof($tableDat["rows"]); $j < $node->nodeRow->node_char_limit; $j++)
        <input name="n{{ $nIDtxt }}tbl{{ $j }}fldRow" type="hidden"
            id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" value="-3">
        <tr id="n{{ $nIDtxt }}tbl{{ $j }}row"
            class=" @if ($j%2 > 0) rw2 @endif "
            style="display:
            @if ($j < sizeof($tableDat['rows'])
                || ($j == 0 && sizeof($tableDat['rows']) == 0))
                table-row;
            @else
                none;
            @endif " >
        @if ($hasCol1)
            <td id="n{{ $nIDtxt }}tbl{{ $j }}rowLab" class="sprdRowLab">
                <div id="n{{ $nIDtxt }}tbl{{ $j }}rowLabWrap">
                @if (isset($GLOBALS["SL"]->x["rowLabelMore"])
                    && isset($GLOBALS["SL"]->x["rowLabelMore"][$j]))
                    {!! $GLOBALS["SL"]->x["rowLabelMore"][$j] !!}
                @endif
                @if (isset($tableDat["loopLbl"])
                    && trim($tableDat["loopLbl"]) != '')
                    {{ $tableDat["loopLbl"] }} #{{ (1+$j) }}
                @endif
                </div>
            </td>
        @endif
        @forelse ($tableDat["cols"] as $k => $col)
            <td id="n{{ $nIDtxt }}tbl{{ $j }}row{{ $k }}col" class="sprdFld
                @if (($hasCol1 && $k > 0) || (!$hasCol1 && $k > 1))
                    cl1
                @endif ">{!! str_replace('tbl?', 'tbl' . $j,
                    $GLOBALS["SL"]->replaceTabInd($tableDat["blnk"][$k]))
                !!}</td>
        @empty
        @endforelse
        @if (trim($tableDat["rowCol"]) == '')
            <td id="n{{ $nIDtxt }}tbl{{ $j }}rowDelTd"
                class="taC"><div class="pT5">
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
            @if ($hasCol1)
                colspan="{{ (sizeof($tableDat['cols'])+2) }}"
            @else
                colspan="{{ (sizeof($tableDat['cols'])+1) }}"
            @endif >
            <a id="addSprdTbl{{ $nIDtxt }}Btn" href="javascript:;"
                data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}"
                data-row-max="{{ $tableDat['maxRow'] }}"
                class="btn btn-ico btn-secondary addSprdTblRow"
                >Add {{ $tableDat["loopLbl"] }}
                <i class="fa fa-plus" aria-hidden="true"></i></a>
        </td>
    </tr>
@endif

</table></div> <!-- end nFld -->