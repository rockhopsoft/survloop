<!-- generated from resources/views/vendor/survloop/inc-drop-opts-ashrae.blade.php -->
<option value="" @if (!isset($fltClimate) || trim($fltClimate) == '') SELECTED @endif >All United States</option>
@foreach ([ '1A', '2A', '2B', '3A', '3B', '3C', '4A', '4B', '4C', '5A', '5B', '6A', '6B', '7A', '7B' ] as $zone)
    <option value="{{ $zone }}" @if (isset($fltClimate) && $fltClimate == $zone) SELECTED @endif 
        >Climate Zone {{ $zone }}</option>
@endforeach