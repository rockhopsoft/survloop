<!-- resources/views/vendor/survloop/admin/tree/settings.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<form name="treeSettingsForm" method="post" action="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/settings">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="row">
    <div class="col-7">
    
        <div class="slCard nodeWrap">
            <h2 class="slBlueDark"><i class="fa fa-cogs" aria-hidden="true"></i>
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page') Page 
                @else Survey @endif #{{ $GLOBALS['SL']->treeID }}: Settings</h2>
            <div class="mB20 mT10 w100"><label class="w100">
            <h4 class="m0">{{ $GLOBALS['SL']->treeRow->tree_type }} Name</h4>
            <input type="text" class="form-control w100 form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!} 
                name="TreeName" value="{{ $GLOBALS['SL']->treeRow->tree_name }}" autocomplete="off" >
            </label></div>
            
            <div class="mB20 mT10 w100"><label class="w100">
            <h4 class="m0 disIn">{{ $GLOBALS['SL']->treeRow->tree_type }} URL</h4>
            <div class="disIn mL20">
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page') 
                    @if ($GLOBALS['SL']->treeIsAdmin) {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/dash/
                    @else {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/ @endif
                @else
                    @if ($GLOBALS['SL']->treeIsAdmin) {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/dashboard/start/
                    @else {{ $GLOBALS["SL"]->sysOpts["app-url"] }}/start/ @endif
                @endif
            </div>
            <input class="form-control form-control-lg w100 ntrStp slTab" 
                type="text" {!! $GLOBALS["SL"]->tabInd() !!} autocomplete="off" 
                name="TreeSlug" value="{{ $GLOBALS['SL']->treeRow->tree_slug }}" >
            </label></div>
            
            <div class="mB20 mT10 w100"><label class="w100">
            <h4 class="m0 slGreenDark">
                {{ $GLOBALS['SL']->treeRow->tree_type }} Core Database Table
            </h4>
            <select name="TreeCoreTable" class="form-control w100 form-control-lg ntrStp slTab" 
                {!! $GLOBALS["SL"]->tabInd() !!} autocomplete="off">
                {!! $GLOBALS["SL"]->tablesDropdown($GLOBALS["SL"]->coreTbl) !!}
                </select>
            </label></div>
            
            <h4>{{ $GLOBALS['SL']->treeRow->tree_type }} Options</h4>
            <div class="row">
                <div class="col-6">
                    <label class="w100 p5"><input type="checkbox" name="opt3" value="3" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->tree_opts%3 == 0) CHECKED @endif >
                        <i class="fa fa-eye mL10 mR5" aria-hidden="true"></i> 
                        Admin-Only {{ $GLOBALS['SL']->treeRow->tree_type }}</label>
                    <label class="w100 p5"><input type="checkbox" name="opt43" value="43" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->tree_opts%43 == 0) CHECKED @endif >
                        <i class="fa fa-key mL10 mR5" aria-hidden="true"></i> 
                        Staff {{ $GLOBALS['SL']->treeRow->tree_type }}</label>
                    @if ($GLOBALS["SL"]->sysHas('partners'))
                        <label class="w100 p5"><input type="checkbox" name="opt41" value="41" autocomplete="off" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%41 == 0) CHECKED @endif >
                            <i class="fa fa-university mL10 mR5" aria-hidden="true"></i> 
                            Partner {{ $GLOBALS['SL']->treeRow->tree_type }}</label>
                    @endif
                    @if ($GLOBALS["SL"]->sysHas('volunteers'))
                        <label class="w100 p5"><input type="checkbox" name="opt17" value="17" autocomplete="off" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%17 == 0) CHECKED @endif >
                            <i class="fa fa-hand-rock-o mL10 mR5" aria-hidden="true"></i> 
                            Volunteer {{ $GLOBALS['SL']->treeRow->tree_type }}</label>
                    @endif
                </div><div class="col-6">
                    @if ($GLOBALS['SL']->treeRow->tree_type == 'Survey') 
                        <label class="w100 p5"><input type="checkbox" name="opt11" value="11" autocomplete="off" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%11 == 0) CHECKED @endif >
                            <span class="mL10">Users Can Edit Completed Records</span></label>
                        <label class="w100 p5"><input type="checkbox" name="opt47" value="47" autocomplete="off" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%47 == 0) CHECKED @endif >
                            <span class="mL10">Completed Records Have Public ID#</span></label>
                        <label class="w100 p5"><input type="checkbox" name="opt37" value="37" autocomplete="off" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%37 == 0) CHECKED @endif >
                            <span class="mL10">Survey Navigation Menu</span></label>
                    @endif
                    <label class="w100 p5"><input type="checkbox" name="opt2" value="2" autocomplete="off" 
                        @if ($GLOBALS['SL']->treeRow->tree_opts%2 == 0) CHECKED @endif >
                    <span class="mL10">Skinny Page Width</span></label>
                </div>
            </div>
            <input type="submit" value="Save Changes" class="btn btn-primary btn-lg mT20 w100">
        </div>
        
    </div><div class="col-5">
        
@if ($GLOBALS['SL']->treeRow->tree_type == 'Page') 
        <div class="slCard nodeWrap">
            <div class="mB5"><br /><u><b>Special Page Types</b></u></div>
            <label class="w100 p5"><input type="checkbox" name="opt7" value="7" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->tree_opts%7 == 0) CHECKED @endif >
                <i class="fa fa-home mL10 mR5" aria-hidden="true"></i> Homepage</label>
            <label class="w100 p5"><input type="checkbox" name="opt13" value="13" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->tree_opts%13 == 0) CHECKED @endif >
                <i class="fa fa-list-alt mL10 mR5"></i> Report for Survey</label>
            <label class="w100 p5"><input type="checkbox" name="opt31" value="31" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->tree_opts%31 == 0) CHECKED @endif 
                <i class="fa fa-search mL10 mR5"></i> Search Results</label>
            <label class="w100 p5"><input type="checkbox" name="opt19" value="19" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->tree_opts%19 == 0) CHECKED @endif 
                <i class="fa fa-envelope-o mL10 mR5" aria-hidden="true"></i> Contact Form</label>
            <div class="mB5"><br /><u><b>Page Options</b></u></div>
            <label class="w100 p5"><input type="checkbox" name="opt29" value="29" autocomplete="off" 
                @if ($GLOBALS['SL']->treeRow->tree_opts%29 == 0) CHECKED @endif > 
                <span class="mL10">Cannot Be Cached</span></label>
        </div>
@else
        <div class="slCard nodeWrap">
            <h4 class="slBlueDark">Survey Pro-Tips</h4>
            <p>Displayed in between of this survey's page loads.</p>
            @for ($i = 0; $i < 20; $i++)
                <div id="proTipWrap{{ $i }}" class="disNon mB5">
                    <div class="row">
                        <div class="col-10">
                            <input class="form-control w100 ntrStp slTab" autocomplete="off"
                                type="text" {!! $GLOBALS["SL"]->tabInd() !!}
                                name="proTip{{ $i }}" id="proTip{{ $i }}ID" onKeyUp="chkProTips();"
                                value="{{ ((isset($GLOBALS['SL']->proTips[$i])) 
                                    ? $GLOBALS['SL']->proTips[$i] : '') }}">
                        </div><div class="col-2">
                            <input type="text" class="form-control w100 ntrStp slTab"
                                name="proTipImg{{ $i }}" id="proTipImg{{ $i }}ID" 
                                {!! $GLOBALS["SL"]->tabInd() !!} autocomplete="off"
                                value="{{ ((isset($GLOBALS['SL']->proTipsImg[$i])) 
                                    ? $GLOBALS['SL']->proTipsImg[$i] : '') }}">
                        </div>
                    </div>
                </div>
            @endfor
            </div>
        @endif
        </div>
        
    </div>
</div>

</form>

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

</div>
@endsection