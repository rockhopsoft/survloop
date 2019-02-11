<!-- resources/views/vendor/survloop/admin/tree/settings.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<h2><i class="fa fa-cogs" aria-hidden="true"></i>
@if ($GLOBALS['SL']->treeRow->TreeType == 'Page') Page @else Survey @endif #{{ $GLOBALS['SL']->treeID }}: Settings</h2>

<form name="treeSettingsForm" method="post" action="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/settings">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="row">
    <div class="col-7">
    
        <div class="mB20 mT10 w100"><label class="w100">
        <h3 class="m0">{{ $GLOBALS['SL']->treeRow->TreeType }} Name</h3>
        <input type="text" class="form-control w100 form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!} 
            name="TreeName" value="{{ $GLOBALS['SL']->treeRow->TreeName }}" autocomplete="off" >
        </label></div>
        
        <div class="mB20 mT10 w100"><label class="w100">
        <h3 class="m0 disIn">{{ $GLOBALS['SL']->treeRow->TreeType }} URL</h3>
        <div class="disIn mL20">
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') 
                @if ($GLOBALS['SL']->treeIsAdmin) {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/dash/
                @else {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/
                @endif
            @else
                @if ($GLOBALS['SL']->treeIsAdmin) {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/dashboard/start/
                @else {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/start/
                @endif
            @endif
        </div>
        <input type="text" class="form-control form-control-lg w100 ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!} 
            name="TreeSlug" value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}" autocomplete="off" >
        </label></div>
        
        <div class="mB20 mT10 w100"><label class="w100">
        <h3 class="m0 slGreenDark">{{ $GLOBALS['SL']->treeRow->TreeType }} Core Database Table</h3>
        <select class="form-control w100 form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}
            name="TreeCoreTable" autocomplete="off">
            {!! $GLOBALS["SL"]->tablesDropdown($GLOBALS["SL"]->coreTbl) !!}
            </select>
        </label></div>
        
        <h3>{{ $GLOBALS['SL']->treeRow->TreeType }} Options</h3>
        <div class="row">
            <div class="col-6">
                <label class="w100 p5"><input type="checkbox" name="opt3" value="3" autocomplete="off" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%3 == 0) CHECKED @endif >
                    <i class="fa fa-eye mL10 mR5" aria-hidden="true"></i> 
                    Admin-Only {{ $GLOBALS['SL']->treeRow->TreeType }}</label>
                <label class="w100 p5"><input type="checkbox" name="opt43" value="43" autocomplete="off" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%43 == 0) CHECKED @endif >
                    <i class="fa fa-key mL10 mR5" aria-hidden="true"></i> 
                    Staff {{ $GLOBALS['SL']->treeRow->TreeType }}</label>
                @if ($GLOBALS["SL"]->sysHas('partners'))
                    <label class="w100 p5"><input type="checkbox" name="opt41" value="41" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%41 == 0) CHECKED @endif >
                        <i class="fa fa-university mL10 mR5" aria-hidden="true"></i> 
                        Partner {{ $GLOBALS['SL']->treeRow->TreeType }}</label>
                @endif
                @if ($GLOBALS["SL"]->sysHas('volunteers'))
                    <label class="w100 p5"><input type="checkbox" name="opt17" value="17" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%17 == 0) CHECKED @endif >
                        <i class="fa fa-hand-rock-o mL10 mR5" aria-hidden="true"></i> 
                        Volunteer {{ $GLOBALS['SL']->treeRow->TreeType }}</label>
                @endif
            </div><div class="col-6">
                @if ($GLOBALS['SL']->treeRow->TreeType == 'Survey') 
                    <label class="w100 p5"><input type="checkbox" name="opt11" value="11" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%11 == 0) CHECKED @endif >
                        <span class="mL10">Users Can Edit Completed Records</span></label>
                    <label class="w100 p5"><input type="checkbox" name="opt47" value="47" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%47 == 0) CHECKED @endif >
                        <span class="mL10">Completed Records Have Public ID#</span></label>
                    <label class="w100 p5"><input type="checkbox" name="opt37" value="37" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%37 == 0) CHECKED @endif >
                        <span class="mL10">Survey Navigation Menu</span></label>
                @endif
                <label class="w100 p5"><input type="checkbox" name="opt2" value="2" autocomplete="off" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%2 == 0) CHECKED @endif >
                <span class="mL10">Skinny Page Width</span></label>
            </div>
        </div>
        
    </div><div class="col-1">
    </div><div class="col-4">
        
        @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') 
            <div class="mB5"><br /><u><b>Special Page Types</b></u></div>
            <label class="w100 p5"><input type="checkbox" name="opt7" value="7" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0) CHECKED @endif >
                <i class="fa fa-home mL10 mR5" aria-hidden="true"></i> Home Page</label>
            <label class="w100 p5"><input type="checkbox" name="opt13" value="13" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->TreeOpts%13 == 0) CHECKED @endif >
                <i class="fa fa-list-alt mL10 mR5"></i> Report for Survey</label>
            <label class="w100 p5"><input type="checkbox" name="opt31" value="31" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->TreeOpts%31 == 0) CHECKED @endif 
                <i class="fa fa-search mL10 mR5"></i> Search Results</label>
            <label class="w100 p5"><input type="checkbox" name="opt19" value="19" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->TreeOpts%19 == 0) CHECKED @endif 
                <i class="fa fa-envelope-o mL10 mR5" aria-hidden="true"></i> Contact Form</label>
            <div class="mB5"><br /><u><b>Page Options</b></u></div>
            <label class="w100 p5"><input type="checkbox" name="opt29" value="29" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->TreeOpts%29 == 0) CHECKED @endif > 
                <span class="mL10">Cannot Be Cached</span></label>
        @else
            <div class="mB20 mT10 w100">
            <h3 class="m0">Pro-Tips</h3>
            <p>Displayed in between of this survey's page loads.</p>
            @for ($i = 0; $i < 20; $i++)
                <div id="proTipWrap{{ $i }}" class="disNon p5"><div class="row">
                    <div class="col-1 pT10">
                        #{{ (1+$i) }}
                    </div><div class="col-11">
                        <input type="text" class="form-control w100 ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}
                            name="proTip{{ $i }}" id="proTip{{ $i }}ID" autocomplete="off" onKeyUp="chkProTips();"
                            value="{{ ((isset($GLOBALS['SL']->proTips[$i])) ? $GLOBALS['SL']->proTips[$i] : '') }}">
                    </div>
                </div></div>
            @endfor
            </div>
        @endif
        
    </div>
</div>

<input type="submit" value="Save Changes" class="btn btn-primary btn-lg mT20">
</form>

<div class="adminFootBuff"></div>

<script type="text/javascript">
function chkProTips() {
    var maxInd = 0;
    for (var i = 0; i < 20; i++) {
        if (document.getElementById('proTip'+i+'ID') && document.getElementById('proTip'+i+'ID').value.trim() != '') {
            maxInd = i;
        }
    }
    for (var i = 0; i < 20; i++) {
        if (i <= (1+maxInd)) {
            document.getElementById('proTipWrap'+i+'').style.display='block';
        } else {
            document.getElementById('proTipWrap'+i+'').style.display='none';
        }
    }
    return true;
}
function chkProTipsTimer() {
    chkProTips();
    setTimeout("chkProTipsTimer()", 1000);
    return true;
}
setTimeout("chkProTipsTimer()", 10);
</script>
@endsection