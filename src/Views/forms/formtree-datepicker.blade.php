<!-- resources/views/survloop/forms/formtree-datepicker.blade.php -->
<input type="text" name="{{ $fldName }}" id="{{ $fldName }}ID" 
    value="{{ (($dateStr != '') ? date('m/d/Y', strtotime($dateStr)) : '') }}" 
    class="dateFld form-control" {!! $tabInd !!} autocomplete="off" >