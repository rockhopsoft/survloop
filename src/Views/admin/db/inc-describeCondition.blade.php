<!-- resources/views/vendor/survloop/admin/db/inc-describeCondition.blade.php -->
<input type="hidden" name="condIDs[]" value="{{ $cond->CondID }}" >

@if (isset($cond->CondOperator))
    @if ($cond->CondTag == '#NodeDisabled')
        <b class="red fPerc125">{{ $cond->CondTag }}</b>
    @elseif (trim($cond->CondOperator) == 'CUSTOM')
        {{ $cond->CondTag }} Condition
    @else
        {{ $cond->CondTag }} Condition <span class="slGreenDark fPerc80">(
        @if (intVal($cond->CondLoop) > 0)
            {{ $GLOBALS["DB"]->getLoopName($cond->CondLoop) }}
        @else 
            {{ $GLOBALS["DB"]->tbl[$cond->CondTable] }}
        @endif
        : {{ $GLOBALS["DB"]->getFullFldNameFromID($cond->CondField, false) }}
        
        @if (trim($cond->CondOperator) == 'EXISTS=')
            @if ($cond->CondOperDeet == 0)
                <i>zero records exist</i>
            @else
                <i>exactly {{ $cond->CondOperDeet }} @if ($cond->CondOperDeet == 1) record exists @else records exist @endif </i>
            @endif
        @elseif (trim($cond->CondOperator) == 'EXISTS>')
            @if ($cond->CondOperDeet == 0)
                <i>at least one record exists</i>
            @elseif ($cond->CondOperDeet > 0)
                <i>more than {{ $cond->CondOperDeet }} @if ($cond->CondOperDeet == 1) record @else records @endif exists</i>
            @else
                <i>less than {{ ((-1)*$cond->CondOperDeet) }} @if ($cond->CondOperDeet == -1) record @else records @endif exist</i>
            @endif
        @elseif (sizeof($cond->condVals) > 0)
            @if (sizeof($cond->condVals) == 1)
                @if ($cond->CondOperator == '{')
                    <span class="gry9">is</span>  
                @elseif ($cond->CondOperator == '}')
                    <span class="gry9">is not</span> 
                @endif
                @foreach ($cond->condFldResponses["vals"] as $j => $valInfo)
                    @if (trim($valInfo[0]) == trim($cond->condVals[0])) {{ $valInfo[1] }} @endif 
                @endforeach
            @else
                @if ($cond->CondOperator == '{')
                    <span class="gry9">is in {</span> 
                @elseif ($cond->CondOperator == '}')
                    <span class="gry9">is not in {</span>
                @endif
                @foreach ($cond->condVals as $i => $val)
                    @if ($i > 0) , @endif
                    @foreach ($cond->condFldResponses["vals"] as $j => $valInfo)
                        @if (trim($valInfo[0]) == trim($val)) {{ $valInfo[1] }} @endif 
                    @endforeach
                @endforeach
                <span class="gry9">}</span>
            @endif
        @endif
        )</span>
    @endif
@endif
