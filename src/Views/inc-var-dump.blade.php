<!-- resources/views/vendor/survloop/inc-var-dump.blade.php -->
<a name="debug"></a><center>
<div class="fC taC opac50">
    <a id="hidivBtnDbgPop" class="hidivBtn" href="javascript:;"><i class="fa fa-gear"></i></a>
    <a id="hidivBtnDbgPop2" class="hidivBtn" href="javascript:;"><i class="fa fa-cogs"></i></a>
</div>
<div id="hidivDbgPop">
    <center>
    <div class="f22 bld">lastNode: {{ $lastNode }} -> currNode: {{ $currNode }}</div>
    <div class="f14 pL10 pB20"><i>coreID: {{ $coreID }}, @if ($user && isset($user->id)) userID: {{ $user->id }}, 
        @if (isset($user->name)) {{ $user->name }} @endif @endif , 
        @if (isset($dataSets["Complaints"]) && $dataSets["Complaints"][0]) 
            {{ $dataSets["Complaints"][0]->ComAwardMedallion }} @endif
        </i></div>
    </center>
    <div class="row">
        <div class="col-6">
            <table border=0 class="table table-striped" >
            <tr><th>SessData Totals</th><th><i>IDs</i></th></tr>
            <?php /*
            @forelse ($dataSets as $tbl => $rows)
                @if (($rows && is_array($rows) && sizeof($rows) > 0) || $rows->isNotEmpty())
                    <tr>
                    <td><nobr>{{ $tbl }}</nobr></td>
                    <td><i>
                    @foreach ($rows as $ind => $row)
                        @if ($row) @if ($ind > 0) , @endif {{ $row->getKey() }} @endif
                    @endforeach
                    </i></td></tr>
                @endif
            @empty
            @endforelse
            */ ?>
            </table>
            POST/GET Requests:<pre>{!! $requestDeets !!}</pre>
        </div>
        <div class="col-6">
            <table border=0 class="table table-striped" >
            @if (isset($sessInfo->SessID))
                <tr><th colspan=2 >sessInfo:</th></tr>
                <tr><td>sessID</td><td>{{ $sessInfo->SessID }}</td></tr>
                <tr><td>coreID</td><td>{{ $sessInfo->SessCoreID }}</td></tr>
                <tr><td>currNode</td><td>{{ $sessInfo->SessCurrNode }}</td></tr>
                <tr><td>loopRootJustLeft</td><td>{{ $sessInfo->SessLoopRootJustLeft }}</td></tr>
                <tr><td>afterJumpTo</td><td>{{ $sessInfo->SessAfterJumpTo }}</td></tr>
            @endif
            <tr><th colspan=2 >sessLoops:</th></tr>
            @forelse ($GLOBALS['SL']->sessLoops as $sessLoop)
                <tr><td>{{ $sessLoop->SessLoopName }}</td><td>{{ $sessLoop->SessLoopItemID }}</td></tr>
            @empty
            @endforelse
            </table>
            {!! view('vendor.survloop.inc-var-dump-branches', [ "dataBranches" => $currNodeDataBranch ])->render() !!}
        </div>
    </div>
</div>
</center> 
<div id="hidivDbgPop2">
    <br />kidMap:
    <div class="row">
        <div class="col-6">
            <pre>{!! print_r($sessData->kidMap) !!}</pre>
        </div>
        <div class="col-6">
            loopItemIDs: <pre>{!! print_r($sessData->loopItemIDs) !!}</pre>
        </div>
    </div>
    dataSets: <pre>{!! print_r($sessData->dataSets) !!}</pre>
</div>
