<!-- generated from resources/views/vendor/survloop/forms/inc-drop-opts-ashrae-groups.blade.php -->
@foreach ([ 'Hot-Humid', 'Mixed-Humid', 'Cold', 'Very Cold', 'Subarctic' ] as $zoneGroup)
    <option value="{{ $zoneGroup }}" 
        @if (isset($fltClimate) && $fltClimate == $zoneGroup) SELECTED @endif 
        >{{ $zoneGroup }} Climate</option>
@endforeach
