<!-- generated from resources/views/vendor/survloop/forms/inc-drop-opts-states.blade.php -->
@if ($all)
    @if (isset($hasCanada) && $hasCanada)
        <option value="" 
            @if (!isset($state) || trim($state) == '') SELECTED @endif 
            >All U.S. & Canada</option>
        <option value="US" 
            @if (isset($state) && trim($state) == 'US') SELECTED @endif 
            >All United States</option>
    @else
        <option value="" 
            @if (!isset($state) || trim($state) == '') SELECTED @endif 
            >All United States</option>
    @endif
@endif
@foreach ($stateList as $abbr => $name)
    <option value="{{ $abbr }}" 
        @if ($state == $abbr) SELECTED @endif 
        >{{ $name }} ({{ $abbr }})</option>
@endforeach
@if (isset($hasCanada) && $hasCanada)
    <option value="" DISABLED ></option>
    @if ($all)
        <option value="Canada" 
            @if (isset($state) && trim($state) == 'Canada') SELECTED @endif 
            >All Canada</option>
    @endif
    @foreach ($stateListCa as $abbr => $name)
        <option value="{{ $abbr }}" 
            @if ($state == $abbr) SELECTED @endif 
            >{{ $name }} ({{ $abbr }})</option>
    @endforeach
@endif