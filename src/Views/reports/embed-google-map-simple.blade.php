<!-- resources/views/vender/survloop/embed-google-map-simple.blade.php -->
<iframe id="map{{ $nID }}" height="{{ ((isset($height) && intVal($height) > 0) ? $height : 450) 
    }}" width="100%" frameborder="0" style="border:0"
  src="https://www.google.com/maps/embed/v1/place?key={{ $GLOBALS['SL']->sysOpts['google-map-key2'] 
    }}&q={{ urlencode($addy) }}&maptype={{ ((isset($maptype)) ? $maptype : 'satellite') }}" allowfullscreen>
</iframe>

<?php /*
<div id="map{{ $nID }}"></div>
<script>
function initMap{{ $nID }}() {
//alert('initMap{{ $nID }}()');
    var loc = { lat: {{ $lat }}, lng: {{ $lng }} };
    var map = new google.maps.Map(document.getElementById('map{{ $nID }}'), {zoom: 4, center: loc});
//alert('initMap{{ $nID }}()');
    var marker = new google.maps.Marker({ position: loc, map: map });
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $GLOBALS['SL']->sysOpts['google-map-key2'] 
    }}&callback=initMap{{ $nID }}">
</script>
*/ ?>