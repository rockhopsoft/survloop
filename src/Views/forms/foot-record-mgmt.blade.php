@if ($GLOBALS["SL"]->treeRow->tree_type == 'Survey' && $coreID > 0)
    <!-- resources/views/survloop/forms/foot-record-mgmt.blade.php -->
    <div id="sessMgmtWrap" class="disBlo pT20 mT20 mB30">
        <a id="hidivBtnSessMgmtID" href="javascript:;"
            class="hidivBtn btn btn-secondary btn-sm">
            <div class="pT5 pB5">
                <i class="fa fa-cogs" aria-hidden="true"></i>
                @if (trim($recDesc) != '')
                    {!! $recDesc !!}
                @else 
                    {{ $GLOBALS["SL"]->treeRow->tree_name }}
                @endif
                #{{ $coreID }}
            </div>
        </a>
        <div id="hidivSessMgmtID" class="disNon pT10">
            @if (!isset($isUser) || !$isUser)
                <a class="mR10 saveAndRedir" data-redir-url="/register" 
                    href="javascript:;">Save and Continue Later</a>
            @endif
            <a id="hidivBtnRecMgmtDel" class="hidivBtn txtDanger" 
                href="javascript:;">Delete</a>
            <div id="hidivRecMgmtDel" class="disNon mT5 w100">
                <div class="alert alert-danger w100">
                    <i class="fa fa-trash-o mR5" aria-hidden="true"></i> 
                    Delete this session? This CANNOT be undone.
                    <a href="/delSess/{{ $treeID }}/{{ $coreID }}" 
                        class="float-right btn btn-danger btn-sm mTn5 mBn10 mL20"
                        >Yes, Delete #{{ $coreID }}</a>
                    <a href="javascript:;" id="recMgmtDelX" 
                        class="float-right btn btn-secondary btn-sm mTn5 mBn10"
                        >Cancel</a>
                </div>
            </div>
            <?php /*
            <div id="survFootRecMgmt" class="w100"></div>
            @if (isset($multipleRecords)) {!! $multipleRecords !!} @endif
            */ ?>
        </div>
    </div>
@endif