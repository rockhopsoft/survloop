@if (!isset($min))
    if (treeMajorSects.length > {{ $maj }} && treeMajorSects[{{ $maj }}].length > 3) treeMajorSects[{{ $maj 
        }}][3]="{{ $status }}";
@else
    if (treeMinorSects.length > {{ $maj }} && treeMinorSects[{{ $maj }}].length > {{ $min }} && treeMinorSects[{{ $maj 
        }}][{{ $min }}].length > 3) treeMinorSects[{{ $maj }}][{{ $min }}][3]="{{ $status }}";
@endif