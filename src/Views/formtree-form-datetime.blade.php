<!-- Stored in resources/views/survloop/formtree-form-datetime.blade.php -->

{!! $nodePrompt !!}
<div class="nFld{{ $isOneLinerFld }}">
    <input type="text" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $dateStr }}" {{ $onKeyUp }} 
        class="dateFld form-control input-lg disIn mR20" >
    at <div class="disIn mL20">{!! $formTime !!}</div>
</div>
<script type="text/javascript"> $(function() { $( "#n{{ $nID }}FldID" ).datepicker({ maxDate: "+0d" }); }); </script>