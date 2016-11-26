<option value="-3" @if ($presel == -3) SELECTED @endif > @if (!isset($blankDefTxt)) (select table) @else {{ $blankDefTxt }} @endif </option>
@forelse ($GLOBALS["DB"]->tbls as $tblID)
	<option value="{{ $tblID }}" @if (isset($presel) && $presel == $tblID) SELECTED @endif 
		>{{ $GLOBALS["DB"]->tblEng[$tblID] }}</option>
@empty
@endforelse