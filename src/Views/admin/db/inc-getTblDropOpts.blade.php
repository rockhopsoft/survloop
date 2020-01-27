<option value="-3" @if ($presel == -3) SELECTED @endif > 
    @if (!isset($blankDefTxt)) (select table) @else {{ $blankDefTxt }} @endif
</option>
@forelse ($GLOBALS['SL']->tbls as $tblID)
    <option value="{{ $tblID }}" @if (isset($presel) && $presel == $tblID) SELECTED @endif 
        >{{ $GLOBALS['SL']->tblEng[$tblID] }}</option>
@empty
@endforelse