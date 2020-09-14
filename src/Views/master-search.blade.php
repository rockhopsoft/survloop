<!-- Stored in resources/views/vendor/survloop/master-search.blade.php -->
<?php /*
<a class="fL slNavLnk" id="topNavSearchBtn" href="javascript:;"
    ><i class="fa fa-search" aria-hidden="true"></i></a>
*/ ?>
<div id="topNavSearch" class="fL topNavSearch">
    <div id="dashSearchFrmWrap">
        <div id="dashSearchBg"></div>
        <a id="dashSearchBtn" class="updateSearchFilts" href="javascript:;"
            ><i class="fa fa-search" aria-hidden="true"></i></a>
        <input type="text" name="s" id="admSrchFld" class="form-control form-control-lg"
            placeholder="Search" 
            @if ($GLOBALS['SL']->REQ->has('s') && trim($GLOBALS['SL']->REQ->get('s')) != '')
                value="{{ trim($GLOBALS['SL']->REQ->get('s')) }}" @endif >
    @if (sizeof($GLOBALS["SL"]->allCoreTbls) > 0)
        <a id="hidivBtnSearchOpts" class="hidivBtn" href="javascript:;"
            ><i id="hidivBtnArrSearchOpts" class="fa fa-caret-down" aria-hidden="true"></i></a>
        <div id="hidivSearchOpts" class="p15">
            <a class="btn btn-primary btn-sm fR srchBarParts updateSearchFilts" 
                href="javascript:;">Search</a>
            <b><u>Data Sets</u></b><br />
        @foreach ($GLOBALS["SL"]->allCoreTbls as $cnt => $tbl)
            <div id="srchBarTbl{{ $tbl['id'] }}" class="w100 disBlo">
                <label class="w100 srchBarParts">
                    <input type="checkbox" value="{{ $tbl['id'] }}"
                        name="sDataSet[]" id="sDataSet{{ $cnt }}" 
                        @if (in_array($tbl['id'], $GLOBALS['SL']->currSearchTbls)
                            || sizeof($GLOBALS["SL"]->currSearchTbls) == 0) CHECKED @endif
                        class="mR5 srchBarParts" autocomplete="off"> {{ $tbl["name"] }}
                </label>
            </div>
        @endforeach
        <input type="hidden" id="sDataSetCntID" name="srchBarDataSetCnt" 
            value="{{ sizeof($GLOBALS['SL']->allCoreTbls) }}">
        </div>
    @endif
    </div>
    <input type="hidden" id="sFiltID" name="sFilt" value="">
    <input type="hidden" id="sSortID" name="sSort" value="">
    <input type="hidden" id="sSortDirID" name="sSortDir" value="">
    <input type="hidden" id="sViewID" name="sView" 
        @if ($GLOBALS['SL']->REQ->has('sView')) 
            value="{{ $GLOBALS['SL']->REQ->get('sView') }}" 
        @else value="" 
        @endif >
    <input type="hidden" id="sPrevSearchTblsID" name="sPrevSearchTbls" 
        @if (sizeof($GLOBALS['SL']->currSearchTbls) > 0)
            value="{{ implode(',', $GLOBALS['SL']->currSearchTbls) }}"
        @else value=""
        @endif >
    <input type="hidden" id="sResultsDivID" name="sResultsDiv" value="dashResultsWrap">
    <input type="hidden" id="sResultsUrlID" name="sResultsUrl" value="?ajax=1&dashResults=1">
</div>