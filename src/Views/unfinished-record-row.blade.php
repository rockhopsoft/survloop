<!-- resources/views/vendor/survloop/unfinished-record-row.blade.php -->
<div class="wrapLoopItem"><a name="item{{ $cID }}"></a>
    <div id="wrapItem{{ $cID }}On" class="brdLgt round20 mB20 p20">
        <a href="/switch/{{ $tree }}/{{ $cID }}"><h3 class="m0">{!! $title !!}</h3></a>
        <div class="mB5">{!! $desc !!}</div>
        <a href="/switch/{{ $tree }}/{{ $cID }}" class="btn btn-xs btn-default mR10"
            ><i class="fa fa-pencil fa-flip-horizontal"></i> Continue</a>
        <a href="javascript:;" class="btn btn-xs btn-default mR10"
            onClick="if (confirm('{!! $warning !!}')) { window.location='/delSess/{{ $tree }}/{{ $cID }}'; }"
            ><i class="fa fa-trash-o"></i> Delete</a>
    </div>
</div>