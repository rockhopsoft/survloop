<option value="N" @if (!isset($presel) || $presel == 'N') SELECTED @endif >N (unlimited)</option>
@for ($i = 0; $i < 100; $i++)
    <option value="{{ $i }}" @if ($presel == (''.$i.'')) SELECTED @endif >{{ $i }}</option>
@endfor
