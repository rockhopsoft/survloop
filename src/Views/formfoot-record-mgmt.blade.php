@if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' && $coreID > 0)
    <!-- Stored in resources/views/survloop/formfoot-record-mgmt.blade.php -->
    <div id="sessMgmtWrap" class="disBlo">
        <div class="pT20 mT20 mB5">
            Editing {{ $GLOBALS["SL"]->treeRow->TreeName }} #{{ $coreID }}
            @if (trim($recDesc) != '' && $recDesc != $GLOBALS["SL"]->treeRow->TreeName
                && strpos($GLOBALS["SL"]->treeRow->TreeName, $recDesc) !== false)
                <span class="mL10">{!! $recDesc !!}</span>
            @endif
        </div>
        @if (!isset($isUser) || !$isUser)
            <a class="mR10 saveAndRedir" data-redir-url="/register" href="javascript:;">Save and Continue Later</a>
        @endif
        <a id="hidivBtnRecMgmtDel" class="hidivBtn slRedDark" href="javascript:;">Delete</a>
        <div id="hidivRecMgmtDel" class="disNon mT5 w100">
            <div class="alert alert-danger w100">
                <i class="fa fa-trash-o mR5" aria-hidden="true"></i> Delete this session? This CANNOT be undone.
                <a href="/delSess/{{ $treeID }}/{{ $coreID }}" class="float-right btn btn-danger btn-sm mTn5 mBn10 mL20"
                    >Yes, Delete #{{ $coreID }}</a>
                <a href="javascript:;" id="recMgmtDelX" class="float-right btn btn-secondary btn-sm mTn5 mBn10"
                    >Cancel</a>
            </div>
        </div>
        <?php /*
        <div id="survFootRecMgmt" class="w100"></div>
        @if (isset($multipleRecords)) {!! $multipleRecords !!} @endif
        */ ?>
    </div>
@endif