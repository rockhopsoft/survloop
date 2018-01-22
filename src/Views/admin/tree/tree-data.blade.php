<!-- resources/views/vendor/survloop/admin/tree/tree-data.blade.php -->


<ul class="nav nav-tabs mT10" role="tablist">
    <li role="presentation">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/map?all=1">Full Map of Tree</a></li>
    <li role="presentation" class="active">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/data">Tree Data Structures</a></li>
    <li role="presentation">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/xmlmap">Data Export XML Map</a></li>
</ul>

<h2 class="slBlueDark"><i class="fa fa-snowflake-o"></i> 
    {{ $GLOBALS['SL']->treeName }}: Tree's Use of Database Design</nobr></h2>
<div class="nodeHalfGap"></div>
<div class="row">
    <div class="col-md-6">
        This area manages which parts of the entire database are actually accessed by this tree.
        This tracks the linkages which are needed for this form-tree to properly save it's data.
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <h4 class="m0">Tree's Core Data Table:</h4><h3 class="m0 slBlueDark">
            @if (isset($GLOBALS['SL']->coreTbl)) {{ $GLOBALS['SL']->coreTbl }} @endif </h3>
    </div>
</div>

<h2>SurvLoops</h2>
<p>
<i>Loop</i>: The group of records which the end user may provide between <i>Min</i> and <i>Max</i> records 
in the <i>Table</i>. This data loop plays out for the end user within its family's <i>Root Node</i>. 
New loops can be added by editing any node in the <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/map?all=1">Tree Map</a>.
</p>
<table class="table table-striped">
    <tr>
        <th colspan=2 >Root Node ID, Loop Name <small class="f12 slGrey">(Singular)</small></th>
        <th>Table [Conditions]</th>
        <th>Min</th>
        <th>Max <span class="f12 slGrey">(Warn At)</span></th>
    </tr>
@forelse ($GLOBALS['SL']->dataLoops as $loopPlural => $loopRow)
    <tr>
        <td class="taC">
        @if (intVal($loopRow->DataLoopRoot) > 0)
            <a href="#n{{ $loopRow->DataLoopRoot }}">{{ $loopRow->DataLoopRoot }}</a>
        @endif
        </td>
        <td><a href="#n{{ $loopRow->DataLoopRoot }}">{{ $loopRow->DataLoopPlural }}</a> 
            <small class="f12 slGrey">({{ $loopRow->DataLoopSingular }})</small></td>
        <td><a href="/dashboard/db/table/{{ $loopRow->DataLoopTable }}" target="_blank">
        @if (sizeof($loopRow->conds) > 0) 
            {!! view('vendor.survloop.admin.tree.node-list-conditions', [
                "conds" => $loopRow->conds
            ])->render() !!}
        @else
            {{ $loopRow->DataLoopTable }}
        @endif
        </a></td>
        <td class="slGrey">
            @if ($loopRow->DataLoopMinLimit > 0) {{ $loopRow->DataLoopMinLimit }} @else - @endif
        </td>
        <td class="slGrey">
            @if ($loopRow->DataLoopMaxLimit > 0)
                {{ $loopRow->DataLoopMaxLimit }} 
                @if ($loopRow->DataLoopWarnLimit > 0) ({{ $loopRow->DataLoopWarnLimit }}) @endif
            @else
                -
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="6"><i>none</i></td></tr>
@endforelse
</table>

<div class="nodeHalfGap"></div>

<h2>Data Subsets</h2>
<p>
<h4 class="disIn">(one-to-one)</h4>
<i>Auto-Gen</i>: a stork leaves the tree to magically deliver you a child record linked to its parent. 
<br />
<i>Loop-Gen</i>: leave it to the loops to start new records. 
<br />
<i>Manual</i>: you'll have to code that creation the old faashion way.
</p>
<table class="table table-striped">
    <tr>
        <th>Parent Table</th>
        <th class="taC">Foreign Key</th>
        <th>Child Table</th>
        <th colspan=2 class="slGrey f12">Child Creation</th>
    </tr>
@forelse ($GLOBALS['SL']->dataSubsets as $link)
    <tr>
        <td><a href="/dashboard/db/table/{{ $link->DataSubTbl }}" target="_blank"
            >{{ $link->DataSubTbl }}</a></td>
        @if (isset($link->DataSubTblLnk) && trim($link->DataSubTblLnk) != '')
            <td class="taR pR20"><nobr>
            {{ $link->DataSubTblLnk }} <i class="fa fa-long-arrow-right mR5 slBlueDark"></i>
            </nobr></td>
        @else
            <td class="taL"><nobr>
            <i class="fa fa-long-arrow-left mR5 slBlueDark"></i> {{ $link->DataSubSubLnk }}
            </nobr></td>
        @endif
        <td><a href="/dashboard/db/table/{{ $link->DataSubSubTbl }}" target="_blank"
            >{{ $link->DataSubSubTbl }}</a></td>
        <td class="slGrey">
            @if (isset($link->DataSubAutoGen) && intVal($link->DataSubAutoGen) == 1) Auto-Gen @else Manual @endif
        </td>
        <td class="taC"><a href="?refresh=1&all=1&dataStruct=1&delSub={{ $link->DataSubID }}" 
            class="f10 slRedDark"><i class="fa fa-times"></i></a></td>
    </tr>
@empty
    <tr><td colspan="4" ><i>none</i></td></tr>
@endforelse
    <form name="addNewSubset" method="post" action="?all=1&refresh=1&dataStruct=1&newSub=1">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <tr>
        <td colspan=3 >
            <select name="newSubset" class="form-control">
            {!! $GLOBALS['SL']->getForeignOpts() !!}
            </select>
        </td>
        <td>
            <select name="newSubAuto" class="form-control">
            <option value="1">Auto-Gen</option>
            <option value="2">Loop-Gen</option>
            <option value="0" CHECKED >Manual</option>
            </select>
        </td>
        <td><a href="javascript:;" class="btn btn-primary" 
            onClick="document.addNewSubset.submit();"><i class="fa fa-plus"></i></a></td>
    </tr>
    </form>
</table>

<div class="nodeHalfGap"></div>

<h2>Data Helpers</h2>
<p>
<h4 class="disIn">(one-to-many)</h4>
Some <i>Tables</i> use <i>Helper Tables</i> to store multiple checkbox responses in their <i>Value Field</i>, 
linked to momma by their <i>Foreign Key</i>. These can also be created by editing any Node in the Tree Map with a 
<i>checkbox response</i>. The SurvLoops above also have a one-to-many relation, so there's no need to repeat any 
basic core-table-to-loop-table relations.
</p>
<table class="table table-striped">
    <tr>
        <th>Table</th>
        <th>Helper Foreign Key</th>
        <th>Helper Table</th>
        <th colspan=2 >Helper Field Storing Checkbox Responses</th>
    </tr>
@forelse ($GLOBALS['SL']->dataHelpers as $link)
    <tr>
        <td><a href="/dashboard/db/table/{{ $link->DataHelpParentTable }}" target="_blank"
            >{{ $link->DataHelpParentTable }}</a></td>
        <td class="slGrey"><nobr><i class="fa fa-long-arrow-left mR5 slBlueDark"></i> 
            {{ $link->DataHelpKeyField }}</nobr></td>
        <td><a href="/dashboard/db/table/{{ $link->DataHelpTable }}" target="_blank"
            >{{ $link->DataHelpTable }}</a></td>
        <td>{{ $link->DataHelpValueField }}</td>
        <td class="taC"><a href="?refresh=1&all=1&dataStruct=1&delHelper={{ $link->DataHelpID }}" class="f10 slRedDark"
            ><i class="fa fa-times"></i></a></td>
    </tr>
@empty
    <tr><td colspan="4" ><i>none</i></td></tr>
@endforelse
    <form name="addNewHelper" method="post" action="?all=1&refresh=1&dataStruct=1&newHelper=1">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <tr>
        <td colspan=3 >
            <select name="newHelper" class="form-control">
            {!! $GLOBALS['SL']->getForeignOpts('', 'Helper') !!}
            </select>
        </td>
        <td>
            <select name="newHelperValue" class="form-control">
            {!! $GLOBALS['SL']->fieldsDropdown('', 0) !!}
            </select>
        </td>
        <td><a href="javascript:;" class="btn btn-primary" onClick="document.addNewHelper.submit();"
            ><i class="fa fa-plus"></i></a></td>
    </tr>
    </form>
</table>

<div class="nodeHalfGap"></div>

<h2>Data Linkages</h2>
<p>
<h4 class="disIn">(many-to-many)</h4>
Which other linkage tables should automatically pull related records while this tree is being used? 
</p><p>
Whereas the above data structures define the working data hierarchy, these linkages are between tables which don't 
require a hierarchical relationship. 
</p>
<table class="table table-striped">
    <tr>
        <th>Table 1</th>
        <th class="taL">Key</th>
        <th class="taC">Linkage Table</th>
        <th class="taR">Key</th>
        <th colspan=2 >Table 2</th>
    </tr>
@forelse ($GLOBALS['SL']->dataLinksOn as $tblID => $linkMap)
    <tr>
        <td><a href="/dashboard/db/table/{{ $linkMap[0] }}" target="_blank">{{ $linkMap[0] }}</a></td>
        <td class="taL slGrey"><i class="fa fa-long-arrow-left mR5 slBlueDark"></i> {{ $linkMap[1] }}</td>
        <td class="taC"><a href="/dashboard/db/table/{{ $linkMap[2] }}" target="_blank">{{ $linkMap[2] }}</a></td>
        <td class="taR slGrey">{{ $linkMap[3] }} <i class="fa fa-long-arrow-right mL5 slBlueDark"></i></td>
        <td><a href="/dashboard/db/table/{{ $linkMap[4] }}" target="_blank">{{ $linkMap[4] }}</a></td>
        <td class="taC"><a href="?refresh=1&all=1&dataStruct=1&delLinkage={{ $tblID }}" class="f10 slRedDark"
            ><i class="fa fa-times"></i></a></td>
    </tr>
@empty
    <tr><td colspan="6" ><i>none</i></td></tr>
@endforelse
    <form name="addNewLinkage" method="post" action="?all=1&refresh=1&dataStruct=1">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <tr>
        <td colspan=5 >
            <select name="newLinkage" class="form-control">
            <option value="" SELECTED ></option>
            @forelse ($GLOBALS['SL']->getLinkingTables() as $tbl)
                <option value="{{ $tbl->TblID }}">{{ $tbl->TblName }}</option>
            @empty
            @endforelse
            </select>
        </td>
        <td><a href="javascript:;" class="btn btn-primary" onClick="document.addNewLinkage.submit();"
            ><i class="fa fa-plus"></i></a></td>
    </tr>
    </form>
</table>

<div class="adminFootBuff"></div>
