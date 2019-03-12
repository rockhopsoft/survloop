<!-- resources/views/vendor/survloop/elements/inc-var-dump-branches.blade.php -->
@forelse ($dataBranches as $i => $branch)
    <div> @for ($j = 0; $j <= $i; $j++) - @endfor {{ $branch["branch"] }} 
    @if (trim($branch["loop"]) != '') ({{ $branch["loop"] }}) @endif
    {{ $branch["itemID"] }}</div>
@empty
@endforelse