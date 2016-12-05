<!-- resources/views/vendor/survloop/inc-var-dump.blade.php -->

<a name="debug"></a><center>
<div class="fC pT10 taC">
    <a id="debugPopBtn" class="slBlueFaint mR10" href="#debug"><i class="fa fa-gear"></i></a>
    <a id="debugPopBtn2" class="slBlueFaint" href="#debug"><i class="fa fa-cogs"></i></a>
</div>
<div id="debugPop" class="brdDrk p20 mB10 round20 w50 disNon taL">
    <center>
    <div class="f22 bld">lastNode: {{ $lastNode }} -> currNode: {{ $currNode }}</div>
    <div class="f14 pL10 pB20"><i>complaintID: {{ $coreID }}, @if ($user) {{ $user->name }} @endif , {{ $dataSets["Complaints"][0]->ComAwardMedallion }} </i></div>
    </center>
    <div class="row">
        <div class="col-md-6">
            <table border=0 class="table table-striped" >
            <tr><th>SessData Totals</th><th><i>IDs</i></th></tr>
            @forelse ($dataSets as $tbl => $rows)
                @if (sizeof($rows) > 0)
                    <tr>
                    <td><nobr>{{ $tbl }}</nobr></td>
                    <td><i>
                    @foreach ($rows as $ind => $row)
                        @if ($ind > 0) , @endif {{ $row->getKey() }} 
                    @endforeach
                    </i></td></tr>
                @endif
            @empty
            @endforelse
            </table>
            POST/GET Requests:<pre>{!! print_r($REQ->all()) !!}</pre>
        </div>
        <div class="col-md-6">
            <table border=0 class="table table-striped" >
            <tr><th colspan=2 >sessInfo:</th></tr>
            <tr><td>sessID</td><td>{{ $sessInfo->SessID }}</td></tr>
            <tr><td>coreID</td><td>{{ $sessInfo->SessCoreID }}</td></tr>
            <tr><td>currNode</td><td>{{ $sessInfo->SessCurrNode }}</td></tr>
            <tr><td>loopRootJustLeft</td><td>{{ $sessInfo->SessLoopRootJustLeft }}</td></tr>
            <tr><td>afterJumpTo</td><td>{{ $sessInfo->SessAfterJumpTo }}</td></tr>
            <tr><th colspan=2 >sessLoops:</th></tr>
            @forelse ($GLOBALS["DB"]->sessLoops as $sessLoop)
                <tr><td>{{ $sessLoop->SessLoopName }}</td><td>{{ $sessLoop->SessLoopItemID }}</td></tr>
            @empty
            @endforelse
            <tr><th colspan=2 >dataBranches:</th></tr>
            @forelse ($currNodeDataBranch as $i => $branch)
                <tr><td>
                    @for ($j = 0; $j <= $i; $j++)
                        - 
                    @endfor
                    {{ $branch["branch"] }}
                    <span class="mL5 f10">{{ $branch["loop"] }}</span>
                </td><td>
                    {{ $branch["itemID"] }}
                </td></tr>
            @empty
            @endforelse
            </table>
        </div>
    </div>
</div>
</center> 
<div id="debugPop2" class="brdDrk p20 mB10 round20 disNon">
    <br />kidMap:
    <div class="row">
        <div class="col-md-6">
            <pre>{!! print_r($sessData->kidMap) !!}</pre>
        </div>
        <div class="col-md-6">
            loopItemIDs: <pre>{!! print_r($sessData->loopItemIDs) !!}</pre>
        </div>
    </div>
    dataSets: <pre>{!! print_r($sessData->dataSets) !!}</pre>
</div>
<script type="text/javascript"> $(function() {
    $("#debugPopBtn").click(function() { $("#debugPop").slideToggle("fast"); });
    $("#debugPopBtn2").click(function() { $("#debugPop2").slideToggle("slow"); });
}); </script> <style> ul li span { color: #AAA; } </style>
