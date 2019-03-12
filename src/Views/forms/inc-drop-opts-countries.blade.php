<!-- generated from resources/views/vendor/survloop/forms/inc-drop-opts-countries.blade.php -->
<option value="" @if ($cntry == '') SELECTED @endif ></option>
@foreach ($countryList as $name)
    <option value="{{ $name }}" @if ($cntry == $name) SELECTED @endif >{{ $name }}</option>
@endforeach