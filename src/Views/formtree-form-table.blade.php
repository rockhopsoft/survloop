<!-- Stored in resources/views/survloop/formtree-form-table.blade.php -->
<div id="node{{ $nIDtxt }}{{ trim($GLOBALS['SL']->currCyc['tbl'][1]) }}" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div class="nPrompt">{!! $nodePromptText !!}</div>
    <div class="nFld"><table class="table slSpreadTbl">
    <tr>
    @if (trim($tableDat["rowCol"]) != '') <th class="sprdRowLab">&nbsp;</th> @endif
    @forelse ($tableDat["cols"] as $k => $col)
        <th class=" @if (trim($tableDat['rowCol']) != '' || $k > 0) cl1 @endif " >
        @if (isset($col->nodeRow->NodePromptText)) {!! $col->nodeRow->NodePromptText !!} @endif
        @if (isset($col->NodePromptText)) {!! $col->NodePromptText !!} @endif
        </th>
    @empty @endforelse
    @if (trim($tableDat["rowCol"]) == '') <th class="c1">&nbsp;</th> @endif
    </tr>
    @forelse ($tableDat["rows"] as $j => $row)
        <tr id="n{{ $nIDtxt }}tbl{{ $j }}row" class=" @if ($j%2 == 0) rw2 @endif " >
            @if (trim($tableDat["rowCol"]) != '')
                <td class="sprdRowLab">{!! $row["leftTxt"] !!}</td>
                <input type="hidden" name="n{{ $nIDtxt }}tbl{{ $j }}fldDef" id="n{{ $nIDtxt }}tbl{{ $j }}fldDefID" 
                    value="{{ $row['leftVal'] }}" >
            @else
                <input type="hidden" name="n{{ $nIDtxt }}tbl{{ $j }}fldRow" id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" 
                    value="{{ $row['id'] }}" >
            @endif
            @forelse ($row["cols"] as $k => $col)
                <td class="sprdFld @if (trim($tableDat['rowCol']) != '' || $k > 0) cl1 @endif ">
                    {!! str_replace('nFld', '', str_replace('nFld mT0', '', $col)) !!}</td>
            @empty
            @endforelse
            @if (trim($tableDat["rowCol"]) == '')
                <td class="taC"><div class="pT5"><a href="javascript:;" class="delSprdTblRow" 
                    data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}" data-row-ind="{{ $j }}"
                    ><i class="fa fa-trash-o" aria-hidden="true"></i></a></div></td>
            @endif
        </tr>
    @empty
    @endforelse
    @if (trim($tableDat["rowCol"]) == '')
        @for ($j = sizeof($tableDat["rows"]); $j < $node->nodeRow->NodeCharLimit; $j++)
            <tr id="n{{ $nIDtxt }}tbl{{ $j }}row" class=" @if ($j%2 == 0) rw2 @endif 
                @if ($j < sizeof($tableDat['rows'])) disRow @else disNon @endif " >
                <input type="hidden" name="n{{ $nIDtxt }}tbl{{ $j }}fldRow" id="n{{ $nIDtxt }}tbl{{ $j }}fldRowID" 
                    value="-3">
                @forelse ($tableDat["cols"] as $k => $col)
                    <td class="sprdFld @if ($k > 0) cl1 @endif ">{!! str_replace('tbl?', 'tbl' . $j, 
                        $GLOBALS["SL"]->replaceTabInd($tableDat["blnk"][$k])) !!}</td>
                @empty
                @endforelse
                @if (trim($tableDat["rowCol"]) == '')
                    <td class="taC"><div class="pT5"><a href="javascript:;" class="delSprdTblRow" 
                        data-nid="{{ $nID }}" data-nidtxt="{{ $nIDtxt }}" data-row-ind="{{ $j }}"
                        ><i class="fa fa-trash-o" aria-hidden="true"></i></a></div></td>
                @endif
            </tr>
        @endfor
        <tr id="n{{ $nIDtxt }}rowAdd" >
            <td colspan="{{ sizeof($tableDat['cols']) }}">&nbsp;</td>
            <td class="taR"><a id="addSprdTbl{{ $nIDtxt }}Btn" href="javascript:;" data-nid="{{ $nID }}"
                data-nidtxt="{{ $nIDtxt }}" data-row-max="{{ $tableDat['maxRow'] }}" 
                class="btn btn-ico btn-secondary disBlo addSprdTblRow" 
                ><i class="fa fa-plus" aria-hidden="true"></i></a></td>
        </tr>
    @endif
    
    </table></div> <!-- end nFld -->
</div> <!-- end nodeWrap -->