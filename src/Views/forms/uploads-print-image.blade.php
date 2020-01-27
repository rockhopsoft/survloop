<!-- resources/views/survloop/forms/uploads-print-image.blade.php -->
@if (!$GLOBALS['SL']->isPrintView())
	<div class="pT20 pB15">
		<div class="w100 disBlo brdInfo" style="height: {{ (2+$height) }}px; overflow: hidden;">
@else
	<div class="mTn10">
@endif
    <a href="{{ $upDeets['filePub'] }}" target="_blank" class="disBlo w100" 
        ><img src="{{ $upDeets['filePub'] }}" class="w100"
    	@if ($GLOBALS['SL']->isPrintView()) border=0 @else border=1 @endif
        alt="{{ ((isset($upRow->up_stored_file)) ? $upRow->up_stored_file : 'Uploaded Image') }}"
        ></a>
@if (!$GLOBALS['SL']->isPrintView())
	</div>
@endif
</div>