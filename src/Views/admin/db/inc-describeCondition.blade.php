<!-- resources/views/vendor/survloop/admin/db/inc-describeCondition.blade.php -->
<input type="hidden" name="condIDs[]" value="{{ $cond->cond_id }}" >

@if (isset($cond->cond_operator))
    @if (isset($nID) && intVal($nID) > 0 && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
        && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->cond_id]))
        <span style="margin-right: -4px;">NOT</span>
    @endif
    @if ($cond->cond_tag == '#NodeDisabled')
        <b class="red">{{ $cond->cond_tag }}</b>
    @elseif (trim($cond->cond_operator) == 'AB TEST')
        <i>%AB: {{ $cond->cond_desc }}</i>
    @elseif (trim($cond->cond_operator) == 'CUSTOM')
        {{ $cond->cond_tag }}
    @else
        @if (isset($hideDeets) && $hideDeets)
            <a id="showCond{{ ((isset($nID)) ? $nID . 'n' : '') 
                }}{{ $cond->cond_id }}" 
                class="nodeShowCond slGreenDark" 
                href="javascript:;">{{ $cond->cond_tag }}
            </a>
            <div id="condDeets{{ ((isset($nID)) ? $nID . 'n' : '') 
                }}{{ $cond->cond_id }}" class="disNon">
        @endif
        @if (trim($cond->cond_operator) == 'COMPLEX')
            <span class="fPerc80">
            @forelse ($cond->condVals as $i => $val)
                @if ($i > 0) AND @endif
                @if (intVal($val) < 0) 
                    <span style="margin-right: -4px;">NOT</span> 
                    {{ $GLOBALS["SL"]->getCondByID((-1)*$val) }} 
                @else {{ $GLOBALS["SL"]->getCondByID($val) }} 
                @endif
            @empty
            @endforelse
            </span>
        @else
            <span class="slGreenDark">
            @if ($cond->cond_operator == 'URL-PARAM')
                @if (sizeof($cond->condVals) == 1)
                    /url/?{{ $cond->cond_oper_deet }}={{ 
                        $cond->condFldResponses["vals"][0][1] }}
                @endif
            @else
                @if (isset($cond->cond_loop) && intVal($cond->cond_loop) > 0)
                    {{ $GLOBALS['SL']->getLoopName($cond->cond_loop) }} : 
                @elseif (isset($cond->cond_table) && intVal($cond->cond_table) > 0 
                    && isset($GLOBALS['SL']->tbl[$cond->cond_table]))
                    {{ $GLOBALS['SL']->tbl[$cond->cond_table] }} : 
                @endif
                {{ $GLOBALS['SL']->getFullFldNameFromID($cond->cond_field, false) }}

                @if (trim($cond->cond_operator) == 'EXISTS=')
                    @if ($cond->cond_oper_deet == 0)
                        <i>zero records exist</i>
                    @else
                        <i>exactly {{ $cond->cond_oper_deet }} 
                        @if ($cond->cond_oper_deet == 1) record exists 
                        @else records exist 
                        @endif </i>
                    @endif
                @elseif (trim($cond->cond_operator) == 'EXISTS>')
                    @if ($cond->cond_oper_deet == 0)
                        <i>at least one record exists</i>
                    @elseif ($cond->cond_oper_deet > 0)
                        <i>more than {{ $cond->cond_oper_deet }} 
                        @if ($cond->cond_oper_deet == 1) record 
                        @else records 
                        @endif exists</i>
                    @else
                        <i>less than {{ ((-1)*$cond->cond_oper_deet) }} 
                        @if ($cond->cond_oper_deet == -1) record 
                        @else records 
                        @endif exist</i>
                    @endif
                @elseif (sizeof($cond->condVals) > 0)
                    @if (sizeof($cond->condVals) == 1)
                        @if ($cond->cond_operator == '{')
                            <span class="slGrey">is</span>  
                        @elseif ($cond->cond_operator == '}')
                            <span class="slGrey">is not</span> 
                        @endif
                        @foreach ($cond->condFldResponses["vals"] as $j => $valInfo)
                            @if (trim($valInfo[0]) == trim($cond->condVals[0]))
                                {{ $valInfo[1] }}
                            @endif 
                        @endforeach
                    @else
                        @if ($cond->cond_operator == '{')
                            <span class="slGrey">is in {</span> 
                        @elseif ($cond->cond_operator == '}')
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
