<!-- resources/views/vendor/survloop/reports/graph-data-type-dropdown-opts.blade.php -->

@forelse ($types as $type)
    <option value="{{ $type->slug }}"
        @if (in_array($type->slug, $skips)) DISABLED
        @elseif (isset($presel) && $presel == $type->slug) SELECTED
        @endif >{{ $type->title }}</option>
@empty
@endforelse
