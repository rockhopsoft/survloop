<!-- resources/views/vendor/survloop/unfinished-record-row.blade.php -->

<div class="wrapLoopItem"><a name="item{{ $cID }}"></a>
    <div id="wrapItem{{ $cID }}On" class="brdLgt round20 mB20 pL20 pR20">
        <div class="fL">
            <a href="/switch/{{ $tree }}/{{ $cID }}"><h3 class="m0">{!! $title !!}</h3></a>
            <div class="pB5">{!! $desc !!}</div>
        </div>
        <a href="/switch/{{ $tree }}/{{ $cID }}" class="btn-xs btn-default m10 fR"
            ><i class="fa fa-pencil fa-flip-horizontal"></i> Continue</a>
        <a href="javascript:;" class="btn-xs btn-default m10 fR"
            onClick="if (confirm('{!! $warning !!}')) { window.location='/delSess/{{ $tree }}/{{ $cID }}'; }"
            ><i class="fa fa-times"></i> Delete</a>
        <div class="fC"></div>
    </div>
</div>
