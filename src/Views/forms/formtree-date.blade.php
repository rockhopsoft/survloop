<!-- resources/views/survloop/forms/formtree-date.blade.php -->

<input type="hidden" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $dateStr }}" 
    class=" @if (isset($xtraClass)) {{ $xtraClass }} @endif " data-nid="{{ $nID }}" >
<div class="timeWrap"><nobr>
    <select name="n{{ $nID }}fldMonth" id="n{{ $nID }}fldMonthID" 
        class="form-control form-control-lg slDateChange @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mR5" 
        style="width: 110px;" data-nid="{{ $nID }}" data-nid-txt="{{ $nIDtxt }}" {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="00" @if ($month == 'MM' || intVal($month) == 0) SELECTED @endif >month</option>
        @foreach ([1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 
            8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'] as $m => $mm)
            <option @if ($m < 10) value="0{{ $m }}" @else value="{{ $m }}" @endif 
                @if ($m == $month) SELECTED @endif >{{ $mm }}</option>
        @endforeach
    </select><select name="n{{ $nID }}fldDay" id="n{{ $nID }}fldDayID"
        class="form-control form-control-lg slDateChange @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mR5 mL5" 
        style="width: 110px;" data-nid="{{ $nID }}" data-nid-txt="{{ $nIDtxt }}" {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="00" @if ($day == 'DD' || intVal($day) == 0) SELECTED @endif >day</option>
        @for ($i = 1; $i < 32; $i++)
            <option @if ($i < 10) value="0{{ $i }}" @else value="{{ $i }}" @endif 
                @if ($i == $day) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select><select name="n{{ $nID }}fldYear" id="n{{ $nID }}fldYearID"
        class="form-control form-control-lg slDateChange 
            @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mL5" 
        style="width: 110px;" data-nid="{{ $nID }}" 
            data-nid-txt="{{ $nIDtxt }}" {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="0000" 
            @if ($year == 'YYYY' || intVal($year) == 0) SELECTED @endif
            >year</option>
        @for ($i = $startYear; $i > (intVal(date("Y"))-100); $i--)
            <option @if ($i < 10) value="0{{ $i }}" @else value="{{ $i }}" @endif
                @if ($i == $year) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select>
</nobr></div>


