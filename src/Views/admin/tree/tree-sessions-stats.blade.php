<!-- resources/views/admin/tree/tree-sessions-stats.blade.php -->
<div class="container">
<div class="row">
    <div class="col-8">
    
        <div class="slCard nodeWrap">
            <h1><span class="slBlueDark"><i class="fa fa-snowflake-o"></i>
                {{ $GLOBALS['SL']->treeName }}:</span> Users Session Stats</h1>
            <div class="w100" style="height: 450px;">
                @if (isset($graph2print)) {!! $graph2print !!} @endif
            </div>
        </div>
        
        <div class="slCard nodeWrap">
            <div class="w100" style="height: 500px;">
                @if (isset($graph1print)) {!! $graph1print !!} @endif
            </div>
        </div>
        
    </div><div class="col-4">
    
        <div class="slCard nodeWrap">
            <h4 class="m0">Stats of {{ number_format($genTots["cmpl"][0]+$genTots["cmpl"][1]) }} Recent Attempts</h4>
            <div class="mTn5 mB10 slGrey">since {{ date("n/j/Y", $genTots["date"][2]) }}...</div>
            <ul>
                <li><h4 class="disIn slBlueDark">
                @if (($genTots["cmpl"][0]+$genTots["cmpl"][1]) > 0)
                    {{ round(100*($genTots["cmpl"][1]/($genTots["cmpl"][0]+$genTots["cmpl"][1]))) }}%
                @endif
                <i class="fa fa-check mL15 mR10"></i> Completed <sub class="mL5 slGrey">{{ $genTots["cmpl"][1] }}</sub>
                </h4></li>
            </ul>
            <ul>
            <li>@if (($genTots["mobl"][0]+$genTots["mobl"][1]) > 0)
                    {{ round(100*($genTots["mobl"][0]/($genTots["mobl"][0]+$genTots["mobl"][1]))) }}%
                @endif
                <i class="fa fa-laptop mL15 mR10"></i> Desktop
                <sub class="mL5 slGrey">{{ $genTots["mobl"][0] }}</sub>
            </li>
            <li>@if (($genTots["mobl"][0]+$genTots["mobl"][1]) > 0)
                    {{ round(100*($genTots["mobl"][1]/($genTots["mobl"][0]+$genTots["mobl"][1]))) }}%
                @endif
                <i class="fa fa-mobile mL20 mR20"></i> Mobile
                <sub class="mL5 slGrey">{{ $genTots["mobl"][1] }}</sub>
            </li>
            </ul>
            <table class="table table-striped w100">
            <tr><th>&nbsp;</th><th><b>Complete</b></th><th><b>Incomplete</b></th></tr>
            <tr><td class="slBlueDark">Average Time</td>
                <td class="slBlueDark"> @if ($genTots["cmpl"][1] > 0)
                    {{ $GLOBALS["SL"]->sec2minSec(round($genTots["date"][1]/$genTots["cmpl"][1])) }}
                @endif </td>
                <td> @if ($genTots["cmpl"][0] > 0)
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
        
        <div class="slCard nodeWrap">
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
</div>

<div class="slCard nodeWrap">
    <h2 class="slBlueDark">Recent Submission Attempt History</h2>
    Each record lists whether desktop or mobile, full estimated duration of the attempt, 
    unique ID#, and a history of the full path of this user's survey experience.
    <div class="row">
        <div class="col-6">
        @forelse ($coreTots as $i => $core)
            <div class="p15">
                {!! view(
                    'vendor.survloop.admin.tree.tree-session-attempt-history', 
                    [
                        "core"     => $core,
                        "nodeTots" => $nodeTots
                    ]
                )->render() !!}
            </div>
            @if ($i == round(sizeof($coreTots)/2)) </div><div class="col-6"> @endif
        @empty
        @endforelse
        </div>
    </div>
</div>
</div>