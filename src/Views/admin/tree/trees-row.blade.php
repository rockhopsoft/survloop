<!-- resources/views/vender/survloop/admin/tree/trees-row.blade.php -->
<tr><td>
    <div class="fPerc133">{{ str_replace('[[coreID]]', 1111, $tree->tree_name) }}</div>
    <a class="float-right" href="/dashboard/surv-{{ $tree->tree_id }}/map?all=1&alt=1"
        ><i class="fa fa-pencil mL10" aria-hidden="true"></i></a>
    @if ($tree->tree_opts%3 == 0) <i class="fa fa-eye float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%43 == 0) <i class="fa fa-key float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%41 == 0) <i class="fa fa-university float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%17 == 0) <i class="fa fa-hand-rock-o float-right mT5 mR5" aria-hidden="true"></i>
    @endif
    @if ($tree->tree_opts%3 == 0 || $tree->tree_opts%17 == 0 || $tree->tree_opts%41 == 0 || $tree->tree_opts%43 == 0)
        <a href="/dashboard/start/{{ $tree->tree_slug }}" target="_blank" class="mL5"
            >/dashboard/start/{{ $tree->tree_slug }}</a>
    @else <a href="/start/{{ $tree->tree_slug }}" target="_blank" class="mL5">/start/{{ $tree->tree_slug }}</a>
    @endif
    @if ($tree->tree_desc) <span class="mL20 slGrey">{{ $tree->tree_desc }}</span> @endif
</td>
</tr>