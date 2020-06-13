<!-- resources/views/vendor/survloop/reports/inc-uploads.blade.php -->
@if (isset($uploads) && sizeof($uploads) > 0)
	@if (!$GLOBALS['SL']->isPrintView())
	    <div class="row">
	    @foreach ($uploads as $i => $up)
	        @if ($i > 0 && $i%3 == 0) </div><div class="row"> @endif
	        <div class="col-md-4">
	        	<div class="pT20 pB20">
	        		{!! $up !!}
	        	</div>
	        </div>
	    @endforeach
	    </div>
	@else
		@if (sizeof($upMap["img"]) > 0)
			@foreach ($upMap["img"] as $j => $upInd)
				@if ($j < (sizeof($upMap["img"])-1)) 
					<div class="pB20 mB20">
						<div class="pT20 pB20">
				@endif
						{!! $uploads[$upInd] !!}
				@if ($j < (sizeof($upMap["img"])-1))
						</div>
						<hr>
					</div>
				@endif
			@endforeach
		@endif
		@if ((sizeof($upMap["vid"])+sizeof($upMap["fil"])) > 0)
		    <div class="row">
			<?php $cnt = 0; ?>
			@if (sizeof($upMap["vid"]) > 0)
				@foreach ($upMap["vid"] as $j => $upInd)
			        @if ($cnt > 0 && $cnt%3 == 0) </div><div class="row"> @endif
			        <div class="col-md-4">
			        	<div class="pT20 pB20">
			        		{!! $uploads[$upInd] !!}
			        	</div>
			        </div>
			        <?php $cnt++; ?>
				@endforeach
			@endif
			@if (sizeof($upMap["fil"]) > 0)
				@foreach ($upMap["fil"] as $j => $upInd)
					@if ($cnt > 0 && $cnt%3 == 0) </div><div class="row"> @endif
			        <div class="col-md-4">
			        	<div class="pT20 pB20">
			        		{!! $uploads[$upInd] !!}
			        	</div>
			        </div>
			        <?php $cnt++; ?>
				@endforeach
			@endif
			</div>
		@endif
	@endif
@endif