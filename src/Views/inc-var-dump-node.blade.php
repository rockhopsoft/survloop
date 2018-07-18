<!-- resources/views/vendor/survloop/inc-var-dump-node.blade.php -->
<div class="w100 relDiv">
    <div class="hidivBtnDbgW">
        <a id="hidivBtnDbgN{{ $nIDtxt }}" class="hidivBtn hidivBtnDbgN" href="javascript:;"
            ><i class="fa fa-gear"></i></a>
    </div>
    <div id="hidivDbgN{{ $nIDtxt }}" class="hidivDbgN">
        {!! view('vendor.survloop.inc-var-dump-branches', [ "dataBranches" => $dataBranches ])->render() !!}
    </div>
</div>