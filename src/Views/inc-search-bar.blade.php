<!-- Stored in resources/views/vender/survloop/inc-search-bar.blade.php -->
<a name="search"></a>
@if (isset($pre)) <div>{!! $pre !!}</div> @endif
<div class="search-bar">
    <input type="text" id="searchBar{{ $nID }}t{{ $treeID }}" name="s{{ $nID }}" class="form-control input-lg searchBar" 
        @if (isset($search)) value="{{ $search }}" @else value="" @endif >
    <div class="search-btn-wrap"><a id="searchTxt{{ $nID }}t{{ $treeID }}" href="javascript:;"
        class="btn btn-info searchBarBtn" 
        @if (!isset($ajax) || intVal($ajax) == 0) target="_parent" @endif 
        ><i class="fa fa-search" aria-hidden="true"></i></a></div>
</div>
@if (isset($extra)) <div>{!! $extra !!}</div> @endif
@if (isset($post)) <div>{!! $post !!}</div> @endif