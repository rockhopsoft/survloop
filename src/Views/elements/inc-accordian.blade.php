<!-- resources/views/vendor/survloop/elements/inc-accordian.blade.php -->

@if ($isCard) 
	<div class="mB15">
		<div class="slCard">
			<div class="mTn15 mBn20">
@endif
<div id="accord{{ $accordID }}" 
	class="slAccord{{ (($big) ? 'Big' : (($isText) ? 'Txt' : '')) }}">
	<div id="accordHead{{ $accordID }}" class="disBlo">
		<a id="hidivBtn{{ $accordID }}" 
			class="hidivBtn" href="javascript:;">
			<div class="disBlo @if ($big) pT15 pB15 
				@else pT5 pB5 @endif ">
				<div class=" @if ($isText) fL fPerc80 mR5 
					@else fR @endif ">
			    	<i id="hidivBtnAcc{{ $accordID }}" aria-hidden="true" 
			    	@if ($open) class="fa fa-chevron-up" 
			    	@else class="fa fa-chevron-down" 
			    	@endif ></i>
			    </div>
				@if ($big) <div class="fL mT3"><h4>{!! $title !!}</h4></div>
				@elseif ($isText) <div class="fL mTn3">{!! $title !!}</div>
				@else <div class="fL mTn3"><h6>{!! $title !!}</h6></div>
				@endif
			    <div class="fC"></div>
			</div>
		</a>
	</div>
	<div id="hidiv{{ $accordID }}" class="p15" 
		style="display: @if ($open) block @else none @endif ;">
		{!! $body !!}
	</div>
</div>
@if ($isCard)
			</div>
		</div>
	</div>
@endif
