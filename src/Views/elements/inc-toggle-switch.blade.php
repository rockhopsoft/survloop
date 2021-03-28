<!-- resources/views/vendor/survloop/elements/inc-toggle-switch.blade.php -->

<input name="{{ $fldName }}" id="toggle{{ $rand }}" 
	value="{{ intVal($currVal) }}" type="hidden">
<div class="toggleSwitch" data-rand="{{ $rand }}">
	<div class="fL pT5">
		{{ $options[0] }}
	</div>
	<div class="fL">
		<div id="toggleSwitchBtn{{ $rand }}" data-rand="{{ $rand }}"
			@if ($currVal == 1) class="toggleSwitchBtnOn"
			@else class="toggleSwitchBtn" 
			@endif >
			<div id="toggleSwitchCircle{{ $rand }}"></div>
		</div>
	</div>
	<div class="fL pT5">
		{{ $options[1] }}
	</div>
	<div class="fC"></div>
</div>



