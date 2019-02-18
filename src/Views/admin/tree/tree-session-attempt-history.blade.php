<!-- resources/views/admin/tree-session-attempt-history.blade.php -->
<h3 class="mT20 mB5">@if ($core["cmpl"]) <i class="fa fa-check mL10"></i> @endif
    @if ($core["mobl"]) <i class="fa fa-mobile mR10"></i> @else <i class="fa fa-laptop mR10"></i> @endif
    <span class="mR10">{{ $GLOBALS["SL"]->sec2minSec($core["dur"]) }}</span>
    @if ($core["cmpl"]) Complete @else Incomplete @endif #{{ $core["core"] }}
</h3>
<div class="pL10 mB5 slGrey">{{ date("m/d/y, g:ia", $core["date"]) }}</div>
@if (isset($core["log"]) && is_array($core["log"]) && sizeof($core["log"]) > 0)
    @foreach ($core["log"] as $k => $log)
        <div class="pL10 mT5">
            <span class="mR5">{{ $GLOBALS["SL"]->sec2minSec($log[0]) }}</span>
            @if (isset($nodeTots[$log[1]]))
                <span class="mR5 slGrey">{{ $nodeTots[$log[1]]["perc"] }}%</span> {{ $nodeTots[$log[1]]["name"] }}
            @endif
        </div>
    @endforeach
@endif