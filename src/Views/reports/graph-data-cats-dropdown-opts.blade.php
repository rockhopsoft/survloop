<!-- resources/views/vendor/survloop/reports/graph-data-cats-dropdown-opts.blade.php -->

@forelse ($collections as $c => $collection)
    <option DISABLED >- - {{ $collection->title }} - -</option>
    @forelse ($collection->groups as $g => $group)
        <option value="{{ $c }}g{{ $g }}"
            @if ($c == $GLOBALS["CUST"]->dataCat
                && $g == $GLOBALS["CUST"]->dataCatGroup)
                SELECTED
            @endif >{{ $group->title }}</option>
    @empty
    @endforelse
@empty
@endforelse
