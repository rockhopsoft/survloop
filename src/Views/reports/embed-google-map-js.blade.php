/* resources/views/vendor/survloop/reports/embed-google-map-js.blade.php */
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
        url: '{{ $GLOBALS["SL"]->sysOpts["app-url"] }}/gen-kml/{{ $filename }}.kml?rand={{ rand(10000, 100000) }}',
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
