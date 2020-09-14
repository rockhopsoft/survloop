<!-- resources/views/vendor/survloop/admin/debug-node-saves.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
    <div class="slCard">
        <h2>
            <i class="fa fa-bug mR3" aria-hidden="true"></i>
            Record Response History
        </h2>
        <p><b>Debug using individual survey node saves.</b></p>
        <?php /*
        <b>Core Record Raw ID:</b>
        <input type="text" name="cidi" id="cidiID"
        @if ($GLOBALS['SL']->REQ->has('cidi'))
            value="{{ $GLOBALS['SL']->REQ->cidi }}"
        @endif
            class="form-control form-control-lg">

        <div class="pT30 pB30"><p><hr></p></div>
        */ ?>

        <h3 class="slBlueDark">Response Saves Directly Tied To Core Record</h3>
    @if (isset($nodeSaveReport->mainSaves)
        && $nodeSaveReport->mainSaves
        && $nodeSaveReport->mainSaves->saves
        && $nodeSaveReport->mainSaves->saves->isNotEmpty())
        <div class="row">
            <div class="col-md-6">
                <h3>Response Changes, By Field</h3>
            @foreach ($nodeSaveReport->mainSaves->saveFlds as $fld => $fldSaves)
                <h4>{{ $fld }}</h4>
                @forelse ($fldSaves as $loopID => $loopSaves)
                    <b>{{ $loopID }}</b><ul>
                    <?php $prev = ''; ?>
                    @forelse ($loopSaves as $save)
                        @if (isset($save->node_save_new_val))
                            <?php
                            $def = $GLOBALS["SL"]->def->getValById($save->node_save_new_val);
                            $print = $save->node_save_new_val 
                                . (($def != '') ? ' (' . $def . ')' : '');
                            ?>
                            @if ($prev != $print)
                                <?php $prev = $print; ?>
                                <li>
                                    {{ $print }}
                                    <div class="mB5 fPerc80 slGrey">
                                        {{ date("n/j/y g:i:a", strtotime($save->created_at)) }}
                                    </div>
                                </li>
                            @endif
                        @endif
                    @empty
                    @endforelse
                    </ul>
                @empty
                @endforelse

                <div class="p10"></div>
            @endforeach
            </div>
            <div class="col-md-1"></div>
            <div class="col-md-5">
                <h3>All Responses Stored</h3>
            @foreach ($nodeSaveReport->mainSaves->saves as $save)
                @if (isset($save->node_save_new_val) 
                    && trim($save->node_save_new_val) != '')
                    <li>
                        #{{ $save->node_save_loop_item_id }}
                        <div class="mTn5">
                            {{ str_replace(
                                $GLOBALS["SL"]->coreTbl . ':', 
                                '', 
                                $save->node_save_tbl_fld
                            ) }} = {{ $save->node_save_new_val }}
                            <?php $def = $GLOBALS["SL"]->def->getValById($save->node_save_new_val); ?>
                            @if ($def != '') ({{ $def }}) @endif
                        </div>
                        <div class="mB5 fPerc80 slGrey">
                            {{ date("n/j/y g:i:a", strtotime($save->created_at)) }}
                        </div>
                    </li>
                @endif
            @endforeach
            </div>
        </div>
    @else
        No direct saves found.
    @endif

<?php /*
        <div class="pT30 pB30"><p><hr></p></div>

        <h3 class="slBlueDark">Response Saves Tied To Users Who Edited Record</h3>
    @if (sizeof($nodeSaveReport->userSaves) > 0)
        @foreach ($nodeSaveReport->userSaves as $uID => $usrSaves)
            @if ($usrSaves->saves && $usrSaves->saves->isNotEmpty())
                <h4 class="slBlueDark mT30"><u>User #{{ $uID }}</u></h4>
                <div class="row">
                    <div class="col-md-6">
                        <h3>Response Changes, By Field</h3>
                    @foreach ($usrSaves->saveFlds as $fld => $fldSaves)
                        <h4>{{ $fld }}</h4>
                        @forelse ($fldSaves as $loopID => $loopSaves)
                            <b>{{ $loopID }}</b><ul>
                            <?php $prev = ''; ?>
                            @forelse ($loopSaves as $save)
                                @if (isset($save->node_save_new_val))
                                    <?php
                                    $def = $GLOBALS["SL"]->def->getValById($save->node_save_new_val);
                                    $print = $save->node_save_new_val 
                                        . (($def != '') ? ' (' . $def . ')' : '');
                                    ?>
                                    @if ($prev != $print)
                                        <?php $prev = $print; ?>
                                        <li>
                                            {{ $print }}
                                            <div class="mB5 fPerc80 slGrey">
                                                {{ date("n/j/y g:i:a", strtotime($save->created_at)) }}
                                            </div>
                                        </li>
                                    @endif
                                @endif
                            @empty
                            @endforelse
                            </ul>
                        @empty
                        @endforelse
 
                        <div class="p10"></div>
                    @endforeach
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                        <h3>All Responses Stored</h3>
                        <ul>
                    @foreach ($usrSaves->saves as $save)
                        @if (isset($save->node_save_new_val) 
                            && trim($save->node_save_new_val) != '')
                            <li>
                                #{{ $save->node_save_loop_item_id }}
                                <div class="mTn5">
                                    {{ str_replace(
                                        $GLOBALS["SL"]->coreTbl . ':', 
                                        '', 
                                        $save->node_save_tbl_fld
                                    ) }} = {{ $save->node_save_new_val }}
                                    <?php $def = $GLOBALS["SL"]->def->getValById($save->node_save_new_val); ?>
                                    @if ($def != '') ({{ $def }}) @endif
                                </div>
                                <div class="mB5 fPerc80 slGrey">
                                    {{ date("n/j/y g:i:a", strtotime($save->created_at)) }}
                                </div>
                            </li>
                        @endif
                    @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        No other user saves found.
    @endif
*/ ?>
        
        <div class="pT30 pB30"><p><hr></p></div>

        <h4>Session Event Log</h4>
        {!! $nodeLog !!}

    </div>
</div>
<div class="adminFootBuff"></div>
@endsection