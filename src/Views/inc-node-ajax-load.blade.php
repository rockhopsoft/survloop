<!-- Stored in resources/views/vender/survloop/inc-node-ajax-load.blade.php -->
@if (isset($pre)) <div>{!! $pre !!}</div> @endif
<div id="n{{ $nID }}ajaxLoad" class="w100">{!! $spinner !!}</div>
<script type="text/javascript"> $(document).ready(function(){ 
    $("#n{{ $nID }}ajaxLoad").load("{!! $load !!}");
}); </script>
@if (isset($post)) <div>{!! $post !!}</div> @endif