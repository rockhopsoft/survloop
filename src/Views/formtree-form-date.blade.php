<!-- Stored in resources/views/survloop/formtree-form-date.blade.php -->

<input type="hidden" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $dateStr }}" 
    class=" @if (isset($xtraClass)) {{ $xtraClass }} @endif " data-nid="{{ $nID }}" >
<div class="timeWrap"><nobr>
    <select name="n{{ $nID }}fldMonth" id="n{{ $nID }}fldMonthID" onChange="formDateChange('{{ $nID }}');" 
        class="form-control form-control-lg  @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mR5" style="width: 120px;"
        {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="00" @if ($month == 'MM' || intVal($month) == 0) SELECTED @endif >month</option>
        @foreach ([1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 
            8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'] as $m => $mm)
            <option @if ($m < 10) value="0{{ $m }}" @else value="{{ $m }}" @endif 
                @if ($m == $month) SELECTED @endif >{{ $mm }}</option>
        @endforeach
    </select><select name="n{{ $nID }}fldDay" id="n{{ $nID }}fldDayID" onChange="formDateChange('{{ $nID }}');" 
        class="form-control form-control-lg  @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mR5 mL5" style="width: 70px;"
        {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="00" @if ($day == 'DD' || intVal($day) == 0) SELECTED @endif >day</option>
        @for ($i = 1; $i < 32; $i++)
            <option @if ($i < 10) value="0{{ $i }}" @else value="{{ $i }}" @endif 
                @if ($i == $day) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select><select name="n{{ $nID }}fldYear" id="n{{ $nID }}fldYearID" onChange="formDateChange('{{ $nID }}');" 
        class="form-control form-control-lg  @if (isset($xtraClass)) {{ $xtraClass }} @endif fL mL5" style="width: 100px;"
        {!! $GLOBALS["SL"]->tabInd() !!}>
        <option value="0000" @if ($year == 'YYYY' || intVal($year) == 0) SELECTED @endif >year</option>
        @for ($i = intVal(date("Y")); $i > (intVal(date("Y"))-100); $i--)
            <option @if ($i < 10) value="0{{ $i }}" @else value="{{ $i }}" @endif
                @if ($i == $year) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select>
</nobr></div>


