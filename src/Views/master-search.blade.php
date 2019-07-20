<!-- Stored in resources/views/vendor/survloop/master-search.blade.php -->
<a class="fL slNavLnk" id="topNavSearchBtn" href="javascript:;"
    ><i class="fa fa-search" aria-hidden="true"></i></a>
<div id="topNavSearch" class="fL topNavSearch">
    <form id="dashSearchFrmID" name="dashSearchForm" method="get" action="/search-run">
    <div id="dashSearchFrmWrap">
        <div id="dashSearchBg"></div>
        <a id="dashSearchBtn" onClick="document.dashSearchForm.submit();" href="javascript:;"
            ><i class="fa fa-search" aria-hidden="true"></i></a>
        <input type="text" name="s" id="admSrchFld" class="form-control form-control-sm"
            placeholder="Search the database" 
            @if ($GLOBALS['SL']->REQ->has('s') && trim($GLOBALS['SL']->REQ->get('s')) != '')
                    value="{{ trim($GLOBALS['SL']->REQ->get('s')) }}" @endif >
        <a id="hidivBtnSearchOpts" class="hidivBtn" href="javascript:;"
            ><i id="hidivBtnArrSearchOpts" class="fa fa-caret-down" aria-hidden="true"></i></a>
        <div id="hidivSearchOpts">
            <div class="row">
                <div class="col-6">
                @if (sizeof($GLOBALS["SL"]->allCoreTbls) > 0)
                    <div class="p15">
                        <b>Data Sets</b><br />
                    @foreach ($GLOBALS["SL"]->allCoreTbls as $tbl)
                        <label class="w100 srchBarParts">
                            <input type="checkbox" name="searchData[]" value="{{ $tbl['id']}}"
                                @if (in_array($tbl['id'], $GLOBALS['SL']->currSearchTbls)
                                    || sizeof($GLOBALS["SL"]->currSearchTbls) == 0) CHECKED @endif
                                class="mR5 srchBarParts" autocomplete="off"> {{ $tbl["name"] }}
                        </label>
                    @endforeach
                    </div>
                @endif
                </div>
                <div class="col-6">
                    <div class="p15">
                        <a class="btn btn-primary btn-sm fR srchBarParts" href="javascript:;"
                            onClick="document.dashSearchForm.submit();">Search</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="sFiltID" name="sFilt" value="">
    <input type="hidden" id="sSortID" name="sSort" value="">
    <input type="hidden" id="sSortDirID" name="sSortDir" value="">
    <input type="hidden" id="sViewID" name="sView" @if ($GLOBALS['SL']->REQ->has('sView')) 
        value="{{ $GLOBALS['SL']->REQ->get('sView') }}" @else value="" @endif >
    </form>
</div>