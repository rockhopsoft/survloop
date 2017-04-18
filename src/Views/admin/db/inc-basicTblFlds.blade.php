<!-- resources/views/vendor/survloop/admin/db/inc-basicTblFlds.blade.php -->

@if (isset($tblID) && $tblID > 0 && isset($GLOBALS['SL']->tbl[$tblID]))
    @if (!$isExcel)
        <table border=0 cellpadding=5 cellspacing=0 class="table table-striped" >
    @endif
    <tr class="disNon"></tr>
    <tr><td>
        <div class="row gryA pL10 pR10">
            <div class="col-md-6 pL20">
                <i><span class="fPerc133">Field Name (in English)</span>, 
                Description, Notes, Value Options</i>
            </div>
            <div class="col-md-6 taR pR20">
                <i>Field Name (in Database), Data Type, Key Info</i>
            </div>
        </div>
    </td></tr>
    @if ($GLOBALS['SL']->tbl[$tblID] == 'users')
        <tr><td><div class="row gry6 pL10 pR10">
            <div class="col-md-9 pL20"><b class="fPerc133">Users Unique ID</b></div>
            <div class="col-md-3 taR pR20">
                <div class="slGrey">id<br />Number, Indexed, Primary Key</div>
            </div>
        </div></td></tr>
        <tr><td><div class="row gry6 pL10 pR10">
            <div class="col-md-9 pL20"><b class="fPerc133">Username</b></div>
            <div class="col-md-3 taR pR20">
                <div class="slGrey">name<br />Text</div>
            </div>
        </div></td></tr>
        <tr><td><div class="row gry6 pL10 pR10">
            <div class="col-md-9 pL20"><b class="fPerc133">Email Address</b></div>
            <div class="col-md-3 taR pR20">
                <div class="slGrey">email<br />Text</div>
            </div>
        </div></td></tr>
    @elseif (intVal($GLOBALS['SL']->tblOpts[$tblID]) == 0 || $GLOBALS['SL']->tblOpts[$tblID]%3 > 0)
        <tr><td>
            <div class="row pL10 pR10">
                <div class="col-md-9 fPerc133 gry6 pL20">
                    <b>{{ $GLOBALS['SL']->tbl[$tblID] }} Unique ID</b>
                </div>
                <div class="col-md-3 taR slGrey pR20">
                    <div>{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]] }}ID</div>
                    Number, Indexed, Primary Key
                </div>
            </div>
        </td></tr>
    @endif
            
    {!! $printTblFldRows !!}
    
    @if (!$isExcel)
        </table>
    @endif
@endif
