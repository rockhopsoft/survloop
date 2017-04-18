<a href="javascript:;" title="{{ $emo['verb'] }}" id="{{ $spot }}e{{ $emo['id'] }}" class="emojiTagBtn"
    >{!! $emo["html"] !!} @if ($cnt > 0) <span class="badge">{{ $cnt }}</span> @endif </a>
<script type="text/javascript"> $(function() { 
@if ($isActive) $("#{{ $spot }}e{{ $emo['id'] }}Tag").addClass("active"); 
@else $("#{{ $spot }}e{{ $emo['id'] }}Tag").removeClass("active");
@endif
}); </script>