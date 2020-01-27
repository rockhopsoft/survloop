<!-- resources/views/vender/survloop/admin/tree/pages-row.blade.php -->
<tr><td>
    @if ($tree->tree_opts%7 > 0 && $tree->tree_opts%13 > 0)
        <div class="relDiv pL20 mL10"><div class="absDiv" style="left: -5px;">
            <i class="fa fa-share fa-flip-vertical opac20" aria-hidden="true"></i></div>
    @endif
    <div>{{ str_replace('[[coreID]]', 1111, $tree->tree_name) }}</div>
    <a class="float-right" href="/dashboard/page/{{ $tree->tree_id }}?all=1&alt=1"
        ><i class="fa fa-pencil mL10" aria-hidden="true"></i></a>
    @if ($tree->tree_opts%3 == 0) <i class="fa fa-eye float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%43 == 0) <i class="fa fa-key float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%41 == 0) <i class="fa fa-university float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->tree_opts%17 == 0) <i class="fa fa-hand-rock-o float-right mT5 mR5" aria-hidden="true"></i>
    @endif
    @if ($tree->tree_opts%7 == 0) <i class="fa fa-home float-right mT5 mR5"></i> @endif
    @if ($tree->tree_opts%31 == 0) <i class="fa fa-search float-right mT5 mR5"></i> @endif
    <a href="{{ $GLOBALS['SL']->x['pageUrls'][$tree->tree_id] }}" target="_blank" class="mL5"
        >{{ $GLOBALS["SL"]->x["pageUrls"][$tree->tree_id] }}</a>
    @if (isset($GLOBALS["SL"]->x["myRedirs"][$tree->tree_slug]))
        {!! $GLOBALS["SL"]->x["myRedirs"][$tree->tree_slug] !!}
    @endif
    @if ($tree->tree_desc) <span class="mL20 slGrey">{{ $tree->tree_desc }}</span> @endif
    @if ($tree->tree_opts%7 > 0) </div> @endif
</td></tr>