<!-- Stored in resources/views/survloop/formtree-form-table.blade.php -->
<div id="node{{ $nID }}{{ trim($GLOBALS['SL']->currCyc['tbl'][1]) }}" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    @if (isset($node->nodeRow->NodePromptText) && trim($node->nodeRow->NodePromptText) != '')
        <div class="nPrompt">{!! $node->nodeRow->NodePromptText !!}</div>
    @endif
    <div class="nFld"><table class="table slSpreadTbl">
    <tr><th class="sprdRowLab">&nbsp;</th>
    @forelse ($tableDat["cols"] as $k => $col)
        <th class=" @if ($k > 0 && $k%2 == 0) cl2 @else cl1 @endif " >
        @if (isset($col->nodeRow->NodePromptText)) {!! $col->nodeRow->NodePromptText !!} @endif
        </th>
    @empty @endforelse
    </tr>
    @forelse ($tableDat["rows"] as $j => $row)
        <?php $rowName = 'n' . $nID . trim($GLOBALS["SL"]->currCyc["tbl"][1]) . 'fld' . $j; ?>
        <tr id="{{ $rowName }}row" class=" @if ($j%2 == 0) rw2 @endif " >
            @if (trim($this->tableDat["rowCol"]) != '')
                <td class="sprdRowLab">{!! $row["leftTxt"] !!}</td>
                <input type="hidden" name="n{{ $nID }}tbl{{ $j }}fldDef" id="n{{ $nID }}tbl{{ $j }}fldDefID" 
                    value="{{ $row['leftVal'] }}" >
            @else
                
            @endif
            @forelse ($row["cols"] as $k => $col)
                <td class="sprdFld @if ($k > 0 && $k%2 == 0) cl2 @else cl1 @endif ">
                {!! str_replace('nFld', '', str_replace('nFld mT0', '', $col)) !!}</td>
            @empty
            @endforelse
        </tr>
    @empty
    @endforelse
    </table></div> <!-- end nFld -->
</div> <!-- end nodeWrap -->
<?php /* <pre> {!! print_r($tableDat) !!} </pre> */ ?>