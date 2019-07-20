<!-- generated from resources/views/vendor/survloop/forms/inc-drop-opts-ashrae.blade.php -->
@if (isset($hasCanada) && $hasCanada)
    <option value="" @if (!isset($fltClimate) || trim($fltClimate) == '') SELECTED @endif >All Climate Zones</option>
    <option value="US" @if (isset($fltClimate) && trim($fltClimate) == 'US') SELECTED @endif >All of U.S.</option>
    <option value="Canada" @if (isset($fltClimate) && trim($fltClimate) == 'Canada') SELECTED @endif 
        >All of Canada</option>
@else
    <option value="" @if (!isset($fltClimate) || trim($fltClimate) == '') SELECTED @endif >All of U.S.</option>
@endif
@foreach ([ '1A', '2A', '2B', '3A', '3B', '3C', '4A', '4B', '4C', '5A', '5B', '6A', '6B', '7A', '7B' ] as $zone)
    <option value="{{ $zone }}" @if (isset($fltClimate) && $fltClimate == $zone) SELECTED @endif 
        >Climate Zone {{ $zone }} ({{ $GLOBALS["SL"]->states->getAshraeZoneLabel($zone) }})</option>
@endforeach