<!-- resources/views/vendor/survloop/elements/inc-var-dump.blade.php -->
<div class="nodeAnchor"><a name="debug"></a></div>
<div class="fC"></div>
<center>
<div id="dbgPops{{ $GLOBALS['SL']->treeID }}" class="w100 pT15 pB15 taL">
    <a id="hidivBtnDbgPop" class="hidivBtn btn btn-sm btn-secondary mR15" 
        href="javascript:;"><i class="fa fa-list" aria-hidden="true"></i>
        Core Load</a>
    <a id="hidivBtnDbgPop2" class="hidivBtn btn btn-sm btn-secondary mR15" 
        href="javascript:;"><i class="fa fa-database" aria-hidden="true"></i>
        Full Data Sets</a>
    <a id="hidivBtnDbgPop3" class="hidivBtn btn btn-sm btn-secondary" 
        href="javascript:;"><i class="fa fa-clock-o" aria-hidden="true"></i> 
        Micro Load Times</a>
</div>
<div id="hidivDbgPop">
    <center>
    <h3>lastNode: {{ $lastNode }} -> currNode: {{ $currNode }}</h3>
    <div class="pL10 pB20">
        <i>coreID: {{ $coreID }}, 
        @if (isset($user) && isset($user->id))
            userID: {{ $user->id }}, 
            @if (isset($user->name)) {{ $user->name }} @endif ,
        @endif
        </i>
    </div>
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
            <br />Session:<pre>{!! $sessionDeets !!}</pre>
        </div>
        <div class="col-6">
            <table border=0 class="table table-striped" >
            @if (isset($sessInfo->sess_id))
                <tr><th colspan=2 >sessInfo:</th></tr>
                <tr><td>sessID</td><td>{{ $sessInfo->sess_id }}</td></tr>
                <tr><td>coreID</td><td>{{ $sessInfo->sess_core_id }}</td></tr>
                <tr><td>currNode</td><td>{{ $sessInfo->sess_curr_node }}</td></tr>
                <tr><td>loopRootJustLeft</td><td>{{ $sessInfo->sess_loop_root_just_left }}</td></tr>
                <tr><td>afterJumpTo</td><td>{{ $sessInfo->sess_after_jump_to }}</td></tr>
            @endif
            <tr><th colspan=2 >sessLoops:</th></tr>
            @forelse ($GLOBALS['SL']->sessLoops as $sessLoop)
                <tr>
                    <td>{{ $sessLoop->sess_loop_name }}</td>
                    <td>{{ $sessLoop->sess_loop_item_id }}</td>
                </tr>
            @empty
            @endforelse
            </table>
            {!! view(
                'vendor.survloop.elements.inc-var-dump-branches', 
                [ "dataBranches" => $currNodeDataBranch ]
            )->render() !!}
        </div>
    </div>
</div>
</center> 
<div id="hidivDbgPop2">
    <div class="row">
        <div class="col-8">
            kidMap: <pre>{!! str_replace("\n", '  ', 
                print_r($sessData->kidMap)) !!}</pre>
        </div>
        <div class="col-4">
            loopItemIDs: <pre>{!! str_replace("\n", '  ', 
                print_r($sessData->loopItemIDs)) !!}</pre>
        </div>
    </div>
    dataSets: <pre>{!! print_r($sessData->dataSets) !!}</pre>
</div>
<div id="hidivDbgPop3">
    {!! $GLOBALS["SL"]->printMicroLog() !!}
</div>
