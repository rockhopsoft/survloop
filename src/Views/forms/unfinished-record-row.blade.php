<!-- resources/views/vendor/survloop/forms/unfinished-record-row.blade.php -->
<div class="wrapLoopItem"><a name="item{{ $cID }}"></a>
    <div id="wrapItem{{ $cID }}On" class="brdTopFnt pT15 pB15 mT5">
        <a href="/switch/{{ $tree }}/{{ $cID }}"><h5 class="m0">{!! $title !!}</h5></a>
        <div class="pL15 pR15">
            <div class="mB5">{!! $desc !!}</div>
            <a href="/switch/{{ $tree }}/{{ $cID }}" class="mR10"
                ><i class="fa fa-pencil fa-flip-horizontal"></i> Continue</a>
            <a id="hidivBtnRec{{ $cID }}Del" class="hidivBtn txtDanger mR10" href="javascript:;"
                ><i class="fa fa-trash-o"></i> Delete</a>
            <div id="hidivRec{{ $cID }}Del" class="disNon mT10 w100">
                <div class="alert alert-danger w100">
                    <i class="fa fa-trash-o mR5" aria-hidden="true"></i> 
                    @if (isset($warning)) {!! $warning !!}
                    @else Delete this session? This <b>CANNOT</b> be undone. @endif
                    <div class="pT10">
                        <a href="/delSess/{{ $tree }}/{{ $cID }}" class="btn btn-danger btn-sm"
                            >Yes, Delete #{{ $cID }}</a>
                        <a id="hidivBtnRec{{ $cID }}Del" class="hidivBtn btn btn-secondary btn-sm mL20"
                            href="javascript:;" >Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>