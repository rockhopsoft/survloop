<!-- Stored in resources/views/vender/survloop/admin/tree/trees-row.blade.php -->
<tr>
<td class="w75">
    <div class=" @if ($tree->TreeOpts%7 > 0) pL20 mL10 @endif ">
        <div class="fPerc133">{{ str_replace('[[coreID]]', 1111, $tree->TreeName) }}</div>
        <div class="pL5 slBlueDark">
            @if ($tree->TreeOpts%3 == 0 || $tree->TreeOpts%17 == 0)
                <a href="/dashboard/start/{{ $tree->TreeSlug }}" target="_blank">/dashboard/start/{{ $tree->TreeSlug 
                    }}</a>
            @else
                <a href="/start/{{ $tree->TreeSlug }}" target="_blank">/start/{{ $tree->TreeSlug }}</a>
            @endif
            @if ($tree->TreeDesc) <span class="mL20 slGrey">{{ $tree->TreeDesc }}</span> @endif
        </div>
    </div>
</td>
<td class="w25 taR slGrey fPerc133">
    <nobr>
    @if ($tree->TreeOpts%3 == 0) <i class="fa fa-key mR10" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%17 == 0) <i class="fa fa-hand-rock-o mR10" aria-hidden="true"></i>
    @endif
    <a href="/dashboard/surv-{{ $tree->TreeID }}/map?all=1&alt=1"><i class="fa fa-pencil" aria-hidden="true"></i></a>
    </nobr>
</td>
</tr>