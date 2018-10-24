<!-- resources/views/admin/treeSessions.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-snowflake-o"></i> {{ $GLOBALS['SL']->treeName }}:</span>
    Users Session Stats
</h1>
<div class="p10"></div>

<div class="row">
    <div class="col-8">
        <div class="w100" style="height: 450px;">
        @if (isset($sessDailyAttempts)) {!! $sessDailyAttempts !!} @endif
        </div>
    </div>
    <div class="col-4">
        <h3 class="mT0">Stats of {{ number_format($genTots["cmpl"][0]+$genTots["cmpl"][1]) }} Recent Attempts</h3>
        <div class="mTn5 mB10 slGrey">since {{ date("n/j/Y", $genTots["date"][2]) }}...</div>
        <ul>
        <li><h3 class="disIn slBlueDark">
            @if (($genTots["cmpl"][0]+$genTots["cmpl"][1]) > 0)
                {{ round(100*($genTots["cmpl"][1]/($genTots["cmpl"][0]+$genTots["cmpl"][1]))) }}%
            @endif
            <i class="fa fa-check mL15 mR10"></i> Completed
            <sub class="mL5 slGrey">{{ $genTots["cmpl"][1] }}</sub>
            </h3>
        </li>
        </ul>
        
        <ul>
        <li><span class="fPerc125">
            @if (($genTots["mobl"][0]+$genTots["mobl"][1]) > 0)
                {{ round(100*($genTots["mobl"][0]/($genTots["mobl"][0]+$genTots["mobl"][1]))) }}%
            @endif
            <i class="fa fa-laptop mL15 mR10"></i> Desktop</span>
            <sub class="mL5 slGrey">{{ $genTots["mobl"][0] }}</sub>
        </li>
        <li><span class="fPerc125">
            @if (($genTots["mobl"][0]+$genTots["mobl"][1]) > 0)
                {{ round(100*($genTots["mobl"][1]/($genTots["mobl"][0]+$genTots["mobl"][1]))) }}%
            @endif
            <i class="fa fa-mobile mL20 mR20"></i> Mobile</span>
            <sub class="mL5 slGrey">{{ $genTots["mobl"][1] }}</sub>
        </li>
        </ul>
        
        <div class="p10"></div>
        
        <table class="table table-striped w100">
        <tr><th>&nbsp;</th><th><b>Complete</b></th><th><b>Incomplete</b></th></tr>
        <tr><td class="fPerc133 slBlueDark">Average Time</td>
            <td class="fPerc133 slBlueDark"> @if ($genTots["cmpl"][1] > 0)
                {{ $GLOBALS["SL"]->sec2minSec(round($genTots["date"][1]/$genTots["cmpl"][1])) }}
            @endif </td>
            <td class="fPerc125"> @if ($genTots["cmpl"][0] > 0)
                {{ $GLOBALS["SL"]->sec2minSec(round($genTots["date"][0]/$genTots["cmpl"][0])) }}
            @endif </td>
        </tr>
        <tr><td><i class="fa fa-laptop mR5"></i> Desktop</td>
            <td> @if (($genTots["mobl"][2][0]+$genTots["mobl"][2][1]) > 0)
                {{ round(100*($genTots["mobl"][2][1]/($genTots["mobl"][2][0]+$genTots["mobl"][2][1]))) }}%
                <sub class="mL5 slGrey">{{ $genTots["mobl"][2][1] }}</sub>
                @endif </td>
            <td> @if (($genTots["mobl"][2][0]+$genTots["mobl"][2][1]) > 0)
                {{ round(100*($genTots["mobl"][2][0]/($genTots["mobl"][2][0]+$genTots["mobl"][2][1]))) }}%
                <sub class="mL5 slGrey">{{ $genTots["mobl"][2][0] }}
                @endif </td>
        </tr>
        <tr><td><i class="fa fa-mobile mR5 mL5"></i> Mobile</td>
            <td> @if (($genTots["mobl"][3][0]+$genTots["mobl"][3][1]) > 0)
                {{ round(100*($genTots["mobl"][3][1]/($genTots["mobl"][3][0]+$genTots["mobl"][3][1]))) }}%
                <sub class="mL5 slGrey">{{ $genTots["mobl"][3][1] }}</sub>
                @endif </td>
            <td> @if (($genTots["mobl"][3][0]+$genTots["mobl"][3][1]) > 0)
                {{ round(100*($genTots["mobl"][3][0]/($genTots["mobl"][3][0]+$genTots["mobl"][3][1]))) }}%
                <sub class="mL5 slGrey">{{ $genTots["mobl"][3][0] }}
                @endif </td>
        </tr>
        </table>
    </div>
</div>

<div class="p20"></div>

<div class="row">
    <div class="col-8">
        <div class="w100" style="height: 450px;">
        @if (isset($graph1print)) {!! $graph1print !!} @endif
        </div>
    </div>
    <div class="col-4">
        <div class="p10"></div>
        <table class="table table-striped w100">
        <tr><th colspan=2 ><b>Incompletes:<br />Final Page Saved</b></th>
            <th><b>#<sub class="slGrey">%</sub> of<br />Attempts</b></div></th></tr>
        @forelse ($nodeSort as $perc => $nID)
            @if (isset($nodeTots[$nID]))
                <tr><td class="taR slGreenDark"><sub class="mRn10">{{ $nodeTots[$nID]["perc"] }}%</sub></td>
                    <td class="slGreenDark">{{ $nodeTots[$nID]["name"] }}</td>
                    <td>{{ number_format($nodeTots[$nID]["cmpl"][0]) }} @if ($genTots["cmpl"][0] > 0)
                        <sub class="slGrey">{{ round(100*($nodeTots[$nID]["cmpl"][0]/$genTots["cmpl"][0])) }}%</sub>
                    @endif </td></tr>
            @endif
        @empty
            <tr><td colspan=3 class="slGrey pT20 pB20" ><i>no recent attempts found</i></td></tr>
        @endforelse
        </table>
        
    </div>
</div>

<div class="p20"></div>

<h2 class="slBlueDark">Recent Submission Attempt History</h2>
<div class="row">
    <div class="col-6">
    @forelse ($coreTots as $i => $core)
        <div class="p15">
            <h3 class="mT20 mB0">@if ($core["cmpl"]) <i class="fa fa-check mL10"></i> @endif
                @if ($core["mobl"]) <i class="fa fa-mobile mR10"></i> 
                @else <i class="fa fa-laptop mR10"></i> @endif
                <span class="mR10">{{ $GLOBALS["SL"]->sec2minSec($core["dur"]) }}</span>
                @if ($core["cmpl"]) Complete @else Incomplete @endif
                #{{ $core["core"] }}
            </h3>
            <div class="pL10 mB5 slGrey">{{ date("m/d/y, g:ia", $core["date"]) }}</div>
            @forelse ($core["log"] as $k => $log)
                <div class="pL10 mT5">
                    <span class="mR5">{{ $GLOBALS["SL"]->sec2minSec($log[0]) }}</span>
                    @if (isset($nodeTots[$log[1]]))
                        <span class="mR5 slGrey">{{ $nodeTots[$log[1]]["perc"] }}%</span>
                        {{ $nodeTots[$log[1]]["name"] }}
                    @endif
                </div>
            @empty
            @endforelse
        </div>
        @if ($i == round(sizeof($coreTots)/2))
            </div><div class="col-6">
        @endif
    @empty
    @endforelse
    </div>
</div>

<div class="adminFootBuff"></div>
