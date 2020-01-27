<!-- resources/views/vendor/survloop/admin/db/inc-basicTblFlds.blade.php -->

@if (isset($tblID) && $tblID > 0 && isset($GLOBALS['SL']->tbl[$tblID]))
    @if (!$isExcel)
        <table border=0 cellpadding=5 cellspacing=0 class="table table-striped" >
    @endif
    <tr class="disNon"></tr>
    <tr><td>
        <div class="row slGrey">
            <div class="col-6 pL20">
                <i><span class="mL20">Field Name (in English)</span>, 
                Description, Notes, Value Options</i>
            </div>
            <div class="col-6 taR pR20">
                <i>Field Name (in Database), Data Type, Key Info</i>
            </div>
        </div>
    </td></tr>
    @if ($GLOBALS['SL']->tbl[$tblID] == 'users')
        <tr><td><div class="row slGrey">
            <div class="col-9 pL20"><h4 class="disIn mL20">Users Unique ID</h4></div>
            <div class="col-3 taR pR20">
                <div class="slGrey">id<br />Number, Indexed, Primary Key</div>
            </div>
        </div></td></tr>
        <tr><td><div class="row slGrey">
            <div class="col-9 pL20"><h4 class="disIn mL20">Username</h4></div>
            <div class="col-3 taR pR20">
                <div class="slGrey">name<br />Text</div>
            </div>
        </div></td></tr>
        <tr><td><div class="row slGrey">
            <div class="col-9 pL20"><h4 class="disIn mL20">Email Address</h4></div>
            <div class="col-3 taR pR20">
                <div class="slGrey">email<br />Text</div>
            </div>
        </div></td></tr>
    @elseif (intVal($GLOBALS['SL']->tblOpts[$tblID]) == 0 || $GLOBALS['SL']->tblOpts[$tblID]%3 > 0)
        <tr><td>
            <div class="row">
                <div class="col-9 pL20"><h4 class="disIn mL20">{{ $GLOBALS['SL']->tbl[$tblID] }} Unique ID</h4></div>
                <div class="col-3 taR slGrey pR20">
                    <div>{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$tblID]] }}id</div>
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
