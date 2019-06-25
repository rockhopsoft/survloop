<!-- resources/views/vendor/survloop/elements/inc-accordian.blade.php -->
@if ($isCard) <div class="mB15"><div class="slCard"><div class="mTn15 mBn20"> @endif
<div id="accord{{ $accordID }}" class="slAccord{{ (($big) ? 'Big' : '') }}">
	<div id="accordHead{{ $accordID }}" class="disBlo">
		<a id="hidivBtn{{ $accordID }}" class="hidivBtn" href="javascript:;">
			<div class="disBlo pT15 pB15">
				<div class="fL">
					@if ($big) <h4>{!! $title !!}</h4>
					@else <h6>{!! $title !!}</h6>
					@endif
				</div>
				<div class="fR">
			    	<i id="hidivBtnAcc{{ $accordID }}" 
			    		class="fa fa-chevron-<?= (($open) ? 'up' : 'down') ?>" aria-hidden="true"></i>
			    </div>
			    <div class="fC"></div>
			</div>
		</a>
	</div>
	<div id="hidiv{{ $accordID }}" class="dis<?= (($open) ? 'Blo' : 'Non') ?> p15">
		{!! $body !!}
	</div>
</div>
@if ($isCard) </div></div></div> @endif