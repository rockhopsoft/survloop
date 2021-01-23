<!-- Stored in resources/views/vendor/survloop/master-search.blade.php -->
<?php $tbls = $GLOBALS["SL"]->getSearchCoreTbls(); ?>
<?php /* @if ($isDashLayout) <div class="pL15 pR15"> @endif */ ?>
<div id="topNavSearch">
    <div id="topNavSearchAbs">
        <div id="topNavSearchWrap">
            <div id="topNavSearchContain"class="container-fluid"
                @if (isset($GLOBALS['SL']->v['showSearch']) 
                    && $GLOBALS['SL']->v['showSearch'])
                    style="display: block;"
                @endif >
                <div class="row">
                @if (sizeof($tbls) > 0)
                    <div class="col-4 col-lg-3 formIn" style="padding: 0px;">
                        <select name="sDataSet" id="sDataSetID" 
                            class="form-control form-control-lg" autocomplete="off"
                            style="border-right: 0px none;">
                            <option value=""
                                @if (sizeof($GLOBALS["SL"]->currSearchTbls) == 0) 
                                    SELECTED
                                @endif
                                >All</option>
                        @foreach ($tbls as $cnt => $tbl)
                            <option value="{{ $tbl['id'] }}"
                                @if (sizeof($GLOBALS['SL']->currSearchTbls) == 1
                                    && in_array($tbl['id'], $GLOBALS['SL']->currSearchTbls))
                                    SELECTED
                                @endif
                                >{{ $tbl["name"] }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-7 formIn brdLftGrey"
                        style="padding: 0px;">
                @else
                    <div class="col-10 formIn" style="padding: 0px;">
                @endif
                        <input type="text" name="s" id="admSrchFld" autocomplete="off" 
                            class="form-control form-control-lg w100" placeholder="Search" 
                            @if ($GLOBALS['SL']->REQ->has('s') 
                                && trim($GLOBALS['SL']->REQ->get('s')) != '')
                                value="{{ trim($GLOBALS['SL']->REQ->get('s')) }}"
                            @endif >
                    </div>
                    <div class="col-2" style="padding: 0px;">
                        <a class="btn btn-primary btn-lg btn-block srchBarParts updateSearchFilts" 
                            id="admSrchSubmitBtn" href="javascript:;"
                            ><div class="d-none d-lg-block">
                                <div class="mR15"><nobr>
                                    <i class="fa fa-search mR3" 
                                        aria-hidden="true"></i> Search
                                </nobr></div>
                            </div><div class="d-block d-lg-none pT5">
                                <i class="fa fa-search mR15" aria-hidden="true"></i>
                            </div></a>
                    </div>
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
                <input type="hidden" id="sResultsDivID" name="sResultsDiv" 
                    value="dashResultsWrap">
                <input type="hidden" id="sResultsUrlID" name="sResultsUrl" 
                    value="?ajax=1&dashResults=1">
            </div>
        </div>
    </div>
</div>
<?php /* @if ($isDashLayout) </div> @endif */ ?>