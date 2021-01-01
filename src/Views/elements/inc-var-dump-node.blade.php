<!-- resources/views/vendor/survloop/elements/inc-var-dump-node.blade.php -->
<div class="w100 relDiv">
    <div class="hidivBtnDbgW">
        <a id="hidivBtnDbgN{{ $nIDtxt }}" 
            class="hidivBtn hidivBtnDbgN fPerc66" href="javascript:;"
            ><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>
    </div>
    <div id="hidivDbgN{{ $nIDtxt }}" class="hidivDbgN">
        <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID 
            }}/map/node/{{ $nID }}" class="blk"
            ><i class="fa fa-pencil" aria-hidden="true"></i> 
            Edit Node #{{ $nID }}</a><br />
        {!! view(
            'vendor.survloop.elements.inc-var-dump-branches', 
            [ "dataBranches" => $dataBranches ]
        )->render() !!}
    </div>
</div>