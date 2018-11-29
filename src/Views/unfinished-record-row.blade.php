<!-- resources/views/vendor/survloop/unfinished-record-row.blade.php -->
<div class="wrapLoopItem"><a name="item{{ $cID }}"></a>
    <div id="wrapItem{{ $cID }}On" class="brdTopBluL pT15 pB15">
        <a href="/switch/{{ $tree }}/{{ $cID }}"><h5 class="m0">{!! $title !!}</h5></a>
        <div class="mB5">{!! $desc !!}</div>
        <a href="/switch/{{ $tree }}/{{ $cID }}" class="btn btn-sm btn-secondary mR10"
            ><i class="fa fa-pencil fa-flip-horizontal"></i> Continue</a>
        <a id="hidivBtnRec{{ $cID }}Del" class="hidivBtn btn btn-sm btn-danger mR10" href="javascript:;"
            ><i class="fa fa-trash-o"></i> Delete</a>
        <div id="hidivRec{{ $cID }}Del" class="disNon mT10 w100">
            <div class="alert alert-danger w100">
                <i class="fa fa-trash-o mR5" aria-hidden="true"></i> 
                @if (isset($warning)) {!! $warning !!} @else Delete this session? This <b>CANNOT</b> be undone. @endif
                <div class="pT10">
                    <a href="javascript:;" id="hidivBtnRec{{ $cID }}Del" class="hidivBtn btn btn-secondary btn-sm"
                        >Cancel</a>
                    <a href="/delSess/{{ $tree }}/{{ $cID }}" class="btn btn-danger btn-sm mL20"
                        >Yes, Delete #{{ $cID }}</a>
                </div>
            </div>
        </div>
    </div>
</div>