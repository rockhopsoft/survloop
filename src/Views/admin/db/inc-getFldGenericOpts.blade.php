@forelse ($dbFldGenerics as $id => $fld)
    <option value="{{ $fld[0] }}" @if ($presel == $fld[0]) SELECTED @endif >{{ $fld[1] }}</option>
@empty
@endforelse