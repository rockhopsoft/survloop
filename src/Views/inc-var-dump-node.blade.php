<!-- resources/views/vendor/survloop/inc-var-dump-node.blade.php -->
<div class="w100 relDiv">
    <div class="hidivBtnDbgW">
        <a id="hidivBtnDbgN{{ $nIDtxt }}" class="hidivBtn hidivBtnDbgN" href="javascript:;"
            ><i class="fa fa-circle-thin" aria-hidden="true"></i></a>
    </div>
    <div id="hidivDbgN{{ $nIDtxt }}" class="hidivDbgN">
        <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map/node/{{ $nID 
            }}" class="blk"><i class="fa fa-pencil" aria-hidden="true"></i> Edit Node</a><br />
        {!! view('vendor.survloop.inc-var-dump-branches', [ "dataBranches" => $dataBranches ])->render() !!}
    </div>
</div>