<!-- Stored in resources/views/survloop/formtree-form-time.blade.php -->

<div class="timeWrap f26 disIn"><nobr>
    <select name="n{{ $nID }}fldHr" id="n{{ $nID }}fldHrID" 
        class="timeDrop form-control{{ $inputMobileCls }} disIn mR10">
        <option value="0" @if ($timeArr[0] == -1) SELECTED @endif >hour</option>
    @for ($i=1; $i<13; $i++)
        <option value="{{ $i }}" @if ($i == $timeArr[0]) SELECTED @endif >{{ $i }}</option>
    @endfor
    </select> : <select name="n{{ $nID }}fldMin" id="n{{ $nID }}fldMinID" 
        class="timeDrop form-control{{ $inputMobileCls }} disIn mL10 mR10">
        <option value="0" >min</option>
    @for ($i=0; $i<60; $i+=5)
        <option value="{{ $i }}" @if ($i == $timeArr[1] || ($timeArr[1] == -1 && $i == 0)) SELECTED @endif >
            @if ($i<10) 0{{ $i }} @else {{ $i }} @endif </option>
    @endfor
    </select>
    <select name="n{{ $nID }}fldPM" id="n{{ $nID }}fldPMID" 
        class="timeDrop form-control{{ $inputMobileCls }} disIn mL10">
        <option value="AM" @if ($timeArr[3] == 'AM') SELECTED @endif >AM</option>
        <option value="PM" @if ($timeArr[3] == 'PM') SELECTED @endif >PM</option>
    </select>
</nobr></div>
