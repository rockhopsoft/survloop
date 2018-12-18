<!-- resources/views/vendor/survloop/embed-google-map.blade.php -->
<div class="row">
    <div class="col-lg-8">
        <div id="map{{ $nID }}" class="embedMapA"></div>
    </div><div class="col-lg-4">
        <div id="map{{ $nID }}ajax" class="embedMapDescA">
        @if ($docDesc) {!! $docDesc !!} @else <h3>Click a point on the map for more details.</h3> @endif
        </div>
    </div>
</div>
<script>
    var initZoom = {{ $mapCenter[2] }};
    if (document.body.clientWidth < 800) initZoom--;
    var mapOptions{{ $nID }} = {
        center: {
            lat: {{ $mapCenter[0] }},
            lng: {{ $mapCenter[1] }}
        },
        zoom: initZoom
    };

  function initMap{{ $nID }}() {
    var map{{ $nID }} = new google.maps.Map(document.getElementById('map{{ $nID }}'), mapOptions{{ $nID }});
    var kmlLayer = new google.maps.KmlLayer({
        url: '{{ $GLOBALS["SL"]->sysOpts["app-url"] }}/gen-kml/{{ $filename }}.kml',
        suppressInfoWindows: true,
        preserveViewport: true,
        map: map{{ $nID }}
    });
    kmlLayer.addListener('click', function(kmlEvent) {
        @if ($descAjax)
            $("#map{{ $nID }}ajax").load(kmlEvent.featureData.description);
        @else
            document.getElementById('map{{ $nID }}ajax').innerHTML = kmlEvent.featureData.description;
        @endif
        map{{ $nID }}.panTo(kmlEvent.latLng);
        setTimeout(function() { map{{ $nID }}.setZoom(8); }, 500);
    });
  }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $GLOBALS['SL']->sysOpts['google-map-key2'] 
    }}&callback=initMap{{ $nID }}">
</script>