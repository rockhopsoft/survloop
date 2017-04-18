<!-- resources/views/vendor/survloop/inc-sortable.blade.php -->

<center><h1 class="mTn20">{!! $sortTitle !!}</h1>
<div style="width: 75%; min-width: 500px;">
<ul id="sortable">
@forelse($sorts as $sort)
    <li id="item-{{ $sort[0] }}">
        <div class="col-md-11 taL"><i class="fa fa-ellipsis-v slBlueLight mR20"></i> {!! $sort[1] !!}</div>
        <div class="col-md-1 taR slGrey"></div>
        <div class="clearfix"></div>
    </li>
@empty
    No values found.
@endforelse
</ul></div></center>

<script type="text/javascript">
$(function() { 
    $("#sortable").sortable({
        axis: "y",
        update: function (event, ui) {
            document.getElementById("hidFrameID").src="{{ $submitURL }}&"+$(this).sortable("serialize");
        }
    });
    $("#sortable").disableSelection(); 
});
</script>
