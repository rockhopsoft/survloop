<!-- resources/views/vendor/survloop/embed-google-map.blade.php -->
<div class="row">
    <div class="col-lg-8">
        <div id="map{{ $nID }}" class="embedMapA"></div>
    </div><div class="col-lg-4">
        <div id="map{{ $nID }}ajax" class="embedMapDescA">
        @if ($docDesc) {!! $docDesc !!} 
        @else <h3>Click a point on the map for more details.</h3> 
        @endif
        </div>
    </div>
</div>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $GLOBALS['SL']->sysOpts['google-map-key2'] 
    }}&callback=initMap{{ $nID }}">
</script>