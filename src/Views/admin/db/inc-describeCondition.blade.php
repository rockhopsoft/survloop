<!-- resources/views/vendor/survloop/admin/db/inc-describeCondition.blade.php -->
<input type="hidden" name="condIDs[]" value="{{ $cond->CondID }}" >

@if (isset($cond->CondOperator))
    @if (isset($nID) && intVal($nID) > 0 && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
        && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->CondID]))
        <span style="margin-right: -4px;">NOT</span>
    @endif
    @if ($cond->CondTag == '#NodeDisabled')
        <b class="red">{{ $cond->CondTag }}</b>
    @elseif (trim($cond->CondOperator) == 'AB TEST')
        <i>%AB: {{ $cond->CondDesc }}</i>
    @elseif (trim($cond->CondOperator) == 'CUSTOM')
        {{ $cond->CondTag }}
    @else
        @if (isset($hideDeets) && $hideDeets)
            <a id="showCond{{ ((isset($nID)) ? $nID . 'n' : '') }}{{ $cond->CondID }}" 
                class="nodeShowCond slGreenDark" href="javascript:;">{{ $cond->CondTag }}
            </a><div id="condDeets{{ ((isset($nID)) ? $nID . 'n' : '') }}{{ $cond->CondID }}" class="disNon">
        @endif
        @if (trim($cond->CondOperator) == 'COMPLEX')
            <span class="fPerc80">
            @forelse ($cond->condVals as $i => $val)
                @if ($i > 0) AND @endif
                @if (intVal($val) < 0) 
                    <span style="margin-right: -4px;">NOT</span> {{ $GLOBALS["SL"]->getCondByID((-1)*$val) }} 
                @else {{ $GLOBALS["SL"]->getCondByID($val) }} @endif
            @empty
            @endforelse
            </span>
        @else
            <span class="slGreenDark">
            @if ($cond->CondOperator == 'URL-PARAM')
                @if (sizeof($cond->condVals) == 1)
                    /url/?{{ $cond->CondOperDeet }}={{ $cond->condFldResponses["vals"][0][1] }}
                @endif
            @else
                @if (isset($cond->CondLoop) && intVal($cond->CondLoop) > 0)
                    {{ $GLOBALS['SL']->getLoopName($cond->CondLoop) }} : 
                @elseif (isset($cond->CondTable) && intVal($cond->CondTable) > 0 
                    && isset($GLOBALS['SL']->tbl[$cond->CondTable]))
                    {{ $GLOBALS['SL']->tbl[$cond->CondTable] }} : 
                @endif
                {{ $GLOBALS['SL']->getFullFldNameFromID($cond->CondField, false) }}
                @if (trim($cond->CondOperator) == 'EXISTS=')
                    @if ($cond->CondOperDeet == 0)
                        <i>zero records exist</i>
                    @else
                        <i>exactly {{ $cond->CondOperDeet }} 
                            @if ($cond->CondOperDeet == 1) record exists @else records exist @endif </i>
                    @endif
                @elseif (trim($cond->CondOperator) == 'EXISTS>')
                    @if ($cond->CondOperDeet == 0)
                        <i>at least one record exists</i>
                    @elseif ($cond->CondOperDeet > 0)
                        <i>more than {{ $cond->CondOperDeet }} 
                            @if ($cond->CondOperDeet == 1) record @else records @endif exists</i>
                    @else
                        <i>less than {{ ((-1)*$cond->CondOperDeet) }} 
                            @if ($cond->CondOperDeet == -1) record @else records @endif exist</i>
                    @endif
                @elseif (sizeof($cond->condVals) > 0)
                    @if (sizeof($cond->condVals) == 1)
                        @if ($cond->CondOperator == '{')
                            <span class="slGrey">is</span>  
                        @elseif ($cond->CondOperator == '}')
                            <span class="slGrey">is not</span> 
                        @endif
                        @foreach ($cond->condFldResponses["vals"] as $j => $valInfo)
                            @if (trim($valInfo[0]) == trim($cond->condVals[0])) {{ $valInfo[1] }} @endif 
                        @endforeach
                    @else
                        @if ($cond->CondOperator == '{')
                            <span class="slGrey">is in {</span> 
                        @elseif ($cond->CondOperator == '}')
                            <span class="slGrey">is not in {</span>
                        @endif
                        @foreach ($cond->condVals as $i => $val)
                            @if ($i > 0) , @endif
                            @foreach ($cond->condFldResponses["vals"] as $j => $valInfo)
                                @if (trim($valInfo[0]) == trim($val)) {{ $valInfo[1] }} @endif 
                            @endforeach
                        @endforeach
                        <span class="slGrey">}</span>
                    @endif
                @endif
            @endif
            </span>
        @endif
        @if (isset($hideDeets) && $hideDeets)
            </div>
        @endif
    @endif
@endif
