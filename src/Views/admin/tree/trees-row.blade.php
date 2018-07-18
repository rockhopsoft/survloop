<!-- Stored in resources/views/vender/survloop/admin/tree/trees-row.blade.php -->
<tr><td>
    <div class="fPerc133">{{ str_replace('[[coreID]]', 1111, $tree->TreeName) }}</div>
    <a class="pull-right" href="/dashboard/surv-{{ $tree->TreeID }}/map?all=1&alt=1"
        ><i class="fa fa-pencil mL10" aria-hidden="true"></i></a>
    @if ($tree->TreeOpts%3 == 0) <i class="fa fa-eye pull-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%43 == 0) <i class="fa fa-key pull-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%41 == 0) <i class="fa fa-university pull-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%17 == 0) <i class="fa fa-hand-rock-o pull-right mT5 mR5" aria-hidden="true"></i>
    @endif
    @if ($tree->TreeOpts%3 == 0 || $tree->TreeOpts%17 == 0 || $tree->TreeOpts%41 == 0 || $tree->TreeOpts%43 == 0)
        <a href="/dashboard/start/{{ $tree->TreeSlug }}" target="_blank" class="mL5"
            >/dashboard/start/{{ $tree->TreeSlug }}</a>
    @else <a href="/start/{{ $tree->TreeSlug }}" target="_blank" class="mL5">/start/{{ $tree->TreeSlug }}</a>
    @endif
    @if ($tree->TreeDesc) <span class="mL20 slGrey">{{ $tree->TreeDesc }}</span> @endif
</td>
</tr>