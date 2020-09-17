<!-- resources/views/vendor/survloop/elements/inc-accordian.blade.php -->

@if ($isCard) 
	<div class="mB15">
		<div class="slCard">
@endif
<div id="accord{{ $accordID }}" 
	class="slAccord{{ (($big) ? 'Big' : (($isText) ? 'Txt' : '')) }}">
	<div id="accordHead{{ $accordID }}" class="disBlo">
		<a id="hidivBtn{{ $accordID }}" href="javascript:;"
			class="hidivBtn accordHeadBtn">
			<div class="disBlo" style="background: none;
				@if (!$big) padding: 20px 0px; @endif ">
				<div class="accordHeadWrap" style="background: none;">
					@if ($big) <div class="fL mT3"><h4>{!! $title !!}</h4></div>
					@elseif ($isText) <div class="fL mTn3">{!! $title !!}</div>
					@else <div class="fL mTn3"><h6>{!! $title !!}</h6></div>
					@endif
					<div class="fR">
				    	<i id="hidivBtnAcc{{ $accordID }}" aria-hidden="true" 
				    	@if ($ico == 'caret')
					    	@if ($open) class="fa fa-caret-up mTn5 mR5"
					    	@else class="fa fa-caret-down mTn5 mR5"
				    		@endif style="font-size: 22px; color: #000;"
				    	@else
					    	@if ($open) class="fa fa-chevron-up"
					    	@else class="fa fa-chevron-down"
				    		@endif
					    	@if ($big) style="margin-top: -3px;"
					    	@elseif ($isText) style="font-size: 16px;" 
					    	@endif
				    	@endif ></i>
				    </div>
				    <div class="fC"></div>
				</div>
			</div>
		</a>
	</div>
	<div id="hidiv{{ $accordID }}"
		style="display: @if ($open) block @else none @endif ;">
		{!! $body !!}
	</div>
</div>
@if ($isCard)
		</div>
	</div>
@endif
