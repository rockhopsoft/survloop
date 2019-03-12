<!-- generated from resources/views/vendor/survloop/reports/inc-tbl-head-sort.blade.php -->
@if (isset($srtVal) && trim($srtVal) != '' && isset($sort) && is_array($sort) && sizeof($sort) == 2)
    <a href="javascript:;" class="sortScoresBtn" data-sort-type="{{ $srtVal }}" ><nobr>
        {!! $eng !!}
    @if ($srtVal == $sort[0])
        @if ($sort[1] == 'desc') <i class="fa fa-caret-down" aria-hidden="true"></i>
        @else <i class="fa fa-caret-up" aria-hidden="true"></i>
        @endif
    @endif
    </nobr></a>
@else
    {!! $eng !!}
@endif