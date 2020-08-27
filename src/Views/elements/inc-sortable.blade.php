<!-- resources/views/vendor/survloop/elements/inc-sortable.blade.php -->
<ul id="sortable{{ $sortID }}" class="slSortable" 
    @if (isset($submitURL)) data-url="{{ $submitURL }}" @endif >
@forelse($sorts as $sort)
    <li id="item-{{ $sort[0] }}">
        <div class="col-11 taL">
            <i class="fa fa-ellipsis-v slBlueDark mR20"></i> 
            {!! $sort[1] !!}
        </div>
        <div class="col-1 taR slGrey"></div>
        <div class="clearfix"></div>
    </li>
@empty
    <li>No values found.</li>
@endforelse
</ul>