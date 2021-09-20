<!-- resources/views/vendor/survloop/elements/inc-accordian.blade.php -->

@if ($isCard)
	<div class="mB15">
		<div class="slCard">
			<div class="mBn20">
@endif
<div id="accord{{ $accordID }}"
	class="slAccord{{ (($big) ? 'Big' : (($isText) ? 'Txt' : '')) }}">
	<div id="accordHead{{ $accordID }}" class="disBlo">
		<a id="hidivBtn{{ $accordID }}" href="javascript:;" class="hidivBtn accordHeadBtn">
			<div class="disBlo @if (!$big) accordHeadPad @endif " style="background: none;">
				<div class="accordHeadWrap" style="background: none;">
				@if ($type == 'textL')
					<div class="fL pR5">
						{!! $GLOBALS["SL"]->printAccordianBtn($accordID, $open, $big, $ico, $type) !!}
				    </div>
				@endif
					@if ($big) <div class="fL mT3"><h4>{!! $title !!}</h4></div>
					@elseif ($isText) <div class="fL mTn3">{!! $title !!}</div>
					@else <div class="fL mTn3"><h6>{!! $title !!}</h6></div>
					@endif
				@if ($type != 'textL')
					<div class="fR pR5">
						{!! $GLOBALS["SL"]->printAccordianBtn($accordID, $open, $big, $ico, $type) !!}
				    </div>
				@endif
				    <div class="fC"></div>
				</div>
			</div>
		</a>
	</div>
	<div id="hidiv{{ $accordID }}" style="display: @if ($open) block @else none @endif ;">
		{!! $body !!}
	</div>
</div>
@if ($isCard)
			</div>
		</div>
	</div>
@endif
