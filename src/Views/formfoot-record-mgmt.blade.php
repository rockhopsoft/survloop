@if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' && $coreID > 0) 
    <!-- Stored in resources/views/survloop/formfoot-record-mgmt.blade.php -->
    <div class="p20"></div>
    <div id="sessMgmt" class="round10 brdDshGry slGrey p15">
        <h4 class="disIn m0">&uarr; Editing {{ $GLOBALS["SL"]->treeRow->TreeName }} #{{ $coreID }}</h4>
        @if (!isset($isUser) || !$isUser)
            <a class="btn btn-xs btn-default pull-right mL20 saveAndRedir" href="javascript:;" data-redir-url="/register"
                ><i class="fa fa-key mL5 mR5" aria-hidden="true"></i> 
                <span class="mR5">Finish Later</span></a>
        @endif
        <?php /* <a class="btn btn-xs btn-default pull-right mL20 saveAndRedir" href="javascript:;" 
            data-redir-url="/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}?new=1"
            ><i class="fa fa-star-o mL5 mR5" aria-hidden="true"></i> <span class="mR5">Start New</span></a> */ ?>
        <a id="hidivBtnRecMgmtDel" class="btn btn-xs btn-default pull-right mL20 hidivBtn" href="javascript:;" 
            ><i class="fa fa-trash-o mL5 mR5" aria-hidden="true"></i> <span class="mR5">Delete #{{ $coreID }}</span></a>
        <div id="hidivRecMgmtDel" class="disNon mT20 mL0 w100 round15 brdRed slRedDark row">
            <div class="col-md-6 p5">
                Are you sure you want to delete this session?<br />Deleting CANNOT be undone.
            </div>
            <div class="col-md-3 taR p15">
                <a href="javascript:;" id="recMgmtDelX" class="btn btn-default">Cancel</a>
            </div>
            <div class="col-md-3 taR p15">
                <a href="/delSess/{{ $treeID }}/{{ $coreID }}" class="btn btn-danger">Yes, Delete #{{ $coreID }}</a>
            </div>
        </div>
        <div id="survFootRecMgmt" class="w100"></div>
        @if (isset($multipleRecords)) {!! $multipleRecords !!} @endif
    </div>
@endif