@if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' && $coreID > 0) 
    <!-- Stored in resources/views/survloop/formfoot-record-mgmt.blade.php -->
    <div id="sessMgmtWrap" class="disBlo">
        <div class="p20"></div>
        <a id="hidivBtnSessMgmt" class="hidivBtnSelf slGrey" href="javascript:;"
            ><i class="fa fa-cogs" aria-hidden="true"></i>
            You are editing {{ $GLOBALS["SL"]->treeRow->TreeName }} #{{ $coreID }}</a>
        <div id="hidivSessMgmt" class="disNon round10 brdDshGry slGrey p15">
            <h4 class="disIn m0"><i class="fa fa-cogs" aria-hidden="true"></i> 
                Editing {{ $GLOBALS["SL"]->treeRow->TreeName }} #{{ $coreID }}
                @if (trim($recDesc) != '' && $recDesc != $GLOBALS["SL"]->treeRow->TreeName)
                    {!! $recDesc !!}
                @endif </h4>
            @if (!isset($isUser) || !$isUser)
                <a class="float-right mL20 saveAndRedir" data-redir-url="/register"
                    href="javascript:;" ><i class="fa fa-key mL5 mR5" aria-hidden="true"></i> 
                    <span class="mR5">Finish Later</span></a>
            @endif
            <?php /* <a class="float-right mL20 saveAndRedir" href="javascript:;" 
                data-redir-url="/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}?new=1"
                ><i class="fa fa-star-o mL5 mR5" aria-hidden="true"></i> <span class="mR5">Start New</span></a> */ ?>
            <a id="hidivBtnRecMgmtDel" class="float-right mL20 hidivBtn" href="javascript:;" 
                ><i class="fa fa-trash-o mL5 mR5" aria-hidden="true"></i> 
                <span class="mR5">Delete #{{ $coreID }}</span></a>
            <div id="hidivRecMgmtDel" class="disNon mT20 mL0 w100 round15 brdRed slRedDark">
                <div class="row">
                    <div class="col-md-6 col-sm-11"><div class="pT5 pB5 pL15">
                        Are you sure you want to delete this session?<br />Deleting CANNOT be undone.
                    </div></div>
                    <div class="col-md-2 col-sm-11">
                        <a href="javascript:;" id="recMgmtDelX" class="btn btn-secondary w100 m10">Cancel</a>
                    </div>
                    <div class="col-md-3 col-sm-11">
                        <a href="/delSess/{{ $treeID }}/{{ $coreID }}" class="btn btn-danger w100 m10"
                            >Yes, Delete #{{ $coreID }}</a>
                    </div>
                </div>
            </div>
            <div id="survFootRecMgmt" class="w100"></div>
            @if (isset($multipleRecords)) {!! $multipleRecords !!} @endif
        </div>
    </div>
@endif