<!-- resources/views/survloop/forms/formtree-time.blade.php -->

<div class="timeWrap fPerc125 disBlo"><nobr>
    <select name="n{{ $nID }}fldHr" id="n{{ $nID }}fldHrID" data-nid="{{ $nID }}" 
        class="timeDrop form-control form-control-lg mR5 @if (isset($xtraClass)) {{ $xtraClass }} @endif "
        {!! $GLOBALS["SL"]->tabInd() !!} style="display: inline;" >
        <option value="-1" @if ($timeArr[0] == -1) SELECTED @endif >hour</option>
    @for ($i=1; $i < 13; $i++)
        <option value="{{ $i }}" @if ($i == $timeArr[0]) SELECTED @endif >{{ $i }}</option>
    @endfor
    </select><select name="n{{ $nID }}fldMin" id="n{{ $nID }}fldMinID" data-nid="{{ $nID }}" 
        class="timeDrop form-control form-control-lg mL5 mR5 @if (isset($xtraClass)) {{ $xtraClass }} @endif "
        {!! $GLOBALS["SL"]->tabInd() !!} style="display: inline;" >
        <option value="-1" >min</option>
    @for ($i=0; $i < 60; $i+=5)
        <option value="{{ $i }}" @if ($i == $timeArr[1] || ($timeArr[1] == -1 && $i == 0)) SELECTED @endif >
            @if ($i < 10) 0{{ $i }} @else {{ $i }} @endif </option>
    @endfor
    </select><select name="n{{ $nID }}fldPM" id="n{{ $nID }}fldPMID" data-nid="{{ $nID }}" 
        class="timeDrop form-control form-control-lg mL5 @if (isset($xtraClass)) {{ $xtraClass }} @endif "
        {!! $GLOBALS["SL"]->tabInd() !!} style="display: inline;" >
        <option value="AM" @if ($timeArr[3] == 'AM') SELECTED @endif >AM</option>
        <option value="PM" @if ($timeArr[3] == 'PM') SELECTED @endif >PM</option>
    </select>
</nobr></div>
