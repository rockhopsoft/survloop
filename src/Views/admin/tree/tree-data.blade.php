<!-- resources/views/vendor/survloop/admin/tree/tree-data.blade.php -->

<div class="row mB20">
    <div class="col-md-7">
        <h1><i class="fa fa-snowflake-o"></i> Tree's Use of Database Design</nobr></h1>
        <h2 class="slBlueDark"><i>Core Table: {{ $GLOBALS["DB"]->coreTbl }}</i></h2>
    </div>
    <div class="col-md-5">
        <div class="gry9 p10">
            <i>The Tree's Datadesign</i>: This area manages which parts of the entire database are actually accessed by this tree.
            This tracks the linkages which are needed for this form-tree to properly save it's data.
            More tools will also come to craft the cleanly organized API for raw completed data...
        </div>
    </div>
</div>

<h1>SurvLoops</h1>
<div class="row mB20">
    <div class="col-md-8">
        <table class="table table-striped">
            <tr>
                <th colspan=2 >Root Node ID, Loop Name <small class="f12 gry9">(Singular)</small></th>
                <th>Table [Conditions]</th>
                <th>Min</th>
                <th>Max <span class="f12 gry9">(Warn At)</span></th>
            </tr>
        @forelse ($GLOBALS["DB"]->dataLoops as $loop)
            <tr>
                <td class="taC">
                @if ($loop->DataLoopRoot > 0)
                    <a href="#n{{ $loop->DataLoopRoot }}">{{ $loop->DataLoopRoot }}</a>
                @endif
                </td>
                <td><a href="#n{{ $loop->DataLoopRoot }}">{{ $loop->DataLoopPlural }}</a> <small class="f12 gry9">({{ $loop->DataLoopSingular }})</small></td>
                <td><a href="/dashboard/db/table/{{ $loop->DataLoopTable }}" target="_blank">
                @if (sizeof($loop->conds) > 0) 
                    {!! view( 'vendor.survloop.admin.tree.node-list-conditions', [ "conds" => $loop->conds ])->render() !!}
                @else
                    {{ $loop->DataLoopTable }}
                @endif
                </a></td>
                <td>
                    @if ($loop->DataLoopMinLimit > 0)
                        <span class="gry9">{{ $loop->DataLoopMinLimit }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if ($loop->DataLoopMaxLimit > 0)
                        {{ $loop->DataLoopMaxLimit }} 
                        @if ($loop->DataLoopWarnLimit > 0)
                            <span class="gry9">({{ $loop->DataLoopWarnLimit }})</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><i>none</i></td></tr>
        @endforelse
        </table>
    </div>
    <div class="col-md-4">
        <div class="gry9">
            <i>Loop</i>: The group of records which the end user may provide between <i>Min</i> and <i>Max</i> records in the <i>Table</i>. 
            This data loop plays out for the end user within its family's <i>Root Node</i>. 
            <br /><br />
            New loops can be added by editing any node in the <a href="/dashboard/tree/map?all=1">Tree Map</a>.
        </div>
    </div>
</div>

<h1>Data Subsets</h1>
<div class="row mB20">
    <div class="col-md-8">
        <table class="table table-striped">
            <tr>
                <th>Parent Table</th>
                <th class="taC">Foreign Key</th>
                <th>Child Table</th>
                <th colspan=2 class="gry6 f12">Child Creation</th>
            </tr>
        @forelse ($GLOBALS["DB"]->dataSubsets as $link)
            <tr>
                <td><a href="/dashboard/db/table/{{ $link->DataSubTbl }}" target="_blank">{{ $link->DataSubTbl }}</a></td>
                @if (isset($link->DataSubTblLnk) && trim($link->DataSubTblLnk) != '')
                    <td class="taR pR20"><nobr>
                    {{ $link->DataSubTblLnk }} <i class="fa fa-long-arrow-right mR5 slBlueDark"></i>
                    </nobr></td>
                @else
                    <td class="taL"><nobr>
                    <i class="fa fa-long-arrow-left mR5 slBlueDark"></i> {{ $link->DataSubSubLnk }}
                    </nobr></td>
                @endif
                <td><a href="/dashboard/db/table/{{ $link->DataSubSubTbl }}" target="_blank">{{ $link->DataSubSubTbl }}</a></td>
                <td><small class="gry9">
                @if (isset($link->DataSubAutoGen) && intVal($link->DataSubAutoGen) == 1)
                    Auto-Gen
                @else
                    Manual
                @endif
                </small></td>
                <td><a href="?refresh=1&all=1&dataStruct=1&delSub={{ $link->DataSubID }}" class="f10 redDrk opac50"><i class="fa fa-times"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="4" ><i>none</i></td></tr>
        @endforelse
            <form name="addNewSubset" method="post" action="?all=1&refresh=1&dataStruct=1&newSub=1">
            {!! csrf_field() !!}
            <tr>
                <td colspan=3 >
                    <select name="newSubset" class="form-control">
                    {!! $GLOBALS["DB"]->getForeignOpts() !!}
                    </select>
                </td>
                <td>
                    <select name="newSubAuto" class="form-control">
                    <option value="1">Auto-Gen</option>
                    <option value="2">Loop-Gen</option>
                    <option value="0" CHECKED >Manual</option>
                    </select>
                </td>
                <td><a href="javascript:void(0)" class="btn btn-xs btn-primary" onClick="document.addNewSubset.submit();"><i class="fa fa-plus"></i></a></td>
            </tr>
            </form>
        </table>
    </div>
    <div class="col-md-4">
        <h3 class="gry6">(one-to-one)</h3>
        <div class="gry9">
            <i>Auto-Gen</i>: a stork leaves the tree to magically deliver you a child record linked to its parent. 
            <br /><br />
            <i>Loop-Gen</i>: leave it to the loops to start new records. 
            <br /><br />
            <i>Manual</i>: you'll have to code that creation the old faashion way.
        </div>
    </div>
</div>

<h1>Data Helpers</h1>
<div class="row">
    <div class="col-md-8">
        <table class="table table-striped">
            <tr>
                <th>Table</th>
                <th>Helper Foreign Key</th>
                <th>Helper Table</th>
                <th colspan=2 >Helper Field Storing Checkbox Responses</th>
            </tr>
        @forelse ($GLOBALS["DB"]->dataHelpers as $link)
            <tr>
                <td><a href="/dashboard/db/table/{{ $link->DataHelpParentTable }}" target="_blank">{{ $link->DataHelpParentTable }}</a></td>
                <td class="gry9"><nobr><i class="fa fa-long-arrow-left mR5 slBlueDark"></i> {{ $link->DataHelpKeyField }}</nobr></td>
                <td><a href="/dashboard/db/table/{{ $link->DataHelpTable }}" target="_blank">{{ $link->DataHelpTable }}</a></td>
                <td>{{ $link->DataHelpValueField }}</td>
                <td><a href="?refresh=1&all=1&dataStruct=1&delHelper={{ $link->id }}" class="f10 redDrk opac50"><i class="fa fa-times"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="4" ><i>none</i></td></tr>
        @endforelse
            <form name="addNewHelper" method="post" action="?all=1&refresh=1&dataStruct=1&newHelper=1">
            {!! csrf_field() !!}
            <tr>
                <td colspan=3 >
                    <select name="newHelper" class="form-control">
                    {!! $GLOBALS["DB"]->getForeignOpts('', 'Helper') !!}
                    </select>
                </td>
                <td>
                    <select name="newHelperValue" class="form-control">
                    {!! $GLOBALS["DB"]->fieldsDropdown('', 0) !!}
                    </select>
                </td>
                <td><a href="javascript:void(0)" class="btn btn-xs btn-primary" onClick="document.addNewHelper.submit();"><i class="fa fa-plus"></i></a></td>
            </tr>
            </form>
        </table>
    </div>
    <div class="col-md-4">
        <h3 class="gry6">(one-to-many)</h3>
        <div class="gry9">
            Some <i>Tables</i> use <i>Helper Tables</i> to store multiple checkbox responses in their <i>Value Field</i>, 
            linked to momma by their <i>Foreign Key</i>. 
            <br /><br />
            These can also be created by editing any Node in the Tree Map with a <i>checkbox response</i>.
            <br /><br />
            The SurvLoops above also have a one-to-many relation, so there's no need to repeat any basic core-table-to-loop-table relations.
        </div>
    </div>
</div>

<h1>Data Linkages</h1>
<div class="row">
    <div class="col-md-8">
        <table class="table table-striped">
            <tr>
                <th>Table 1</th>
                <th class="taL">Key</th>
                <th class="taC">Linkage Table</th>
                <th class="taR">Key</th>
                <th colspan=2 >Table 2</th>
            </tr>
        @forelse ($GLOBALS["DB"]->dataLinksOn as $tblID => $linkMap)
            <tr>
                <td><a href="/dashboard/db/table/{{ $linkMap[0] }}" target="_blank">{{ $linkMap[0] }}</a></td>
                <td class="taL gry9"><i class="fa fa-long-arrow-left mR5 slBlueDark"></i> {{ $linkMap[1] }}</td>
                <td class="taC"><a href="/dashboard/db/table/{{ $linkMap[2] }}" target="_blank">{{ $linkMap[2] }}</a></td>
                <td class="taR gry9">{{ $linkMap[3] }} <i class="fa fa-long-arrow-right mL5 slBlueDark"></i></td>
                <td><a href="/dashboard/db/table/{{ $linkMap[4] }}" target="_blank">{{ $linkMap[4] }}</a></td>
                <td><a href="?refresh=1&all=1&dataStruct=1&delLinkage={{ $tblID }}" class="f10 redDrk opac50"><i class="fa fa-times"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="6" ><i>none</i></td></tr>
        @endforelse
            <form name="addNewLinkage" method="post" action="?all=1&refresh=1&dataStruct=1">
            {!! csrf_field() !!}
            <tr>
                <td colspan=5 >
                    <select name="newLinkage" class="form-control">
                    <option value="" SELECTED ></option>
                    @forelse ($GLOBALS["DB"]->getLinkingTables() as $tbl)
                        <option value="{{ $tbl->TblID }}">{{ $tbl->TblName }}</option>
                    @empty
                    @endforelse
                    </select>
                </td>
                <td><a href="javascript:void(0)" class="btn btn-xs btn-primary" onClick="document.addNewLinkage.submit();"><i class="fa fa-plus"></i></a></td>
            </tr>
            </form>
        </table>
    </div>
    <div class="col-md-4">
        <h3 class="gry6">(many-to-many)</h3>
        <div class="gry9">
            Which other linkage tables should automatically pull related records while this tree is being used? 
            <br /><br />
            Whereas the above data structures define the working data hierarchy, these linkages are between tables which don't require a hierarchical relationship. 
        </div>
    </div>
</div>

<div class="adminFootBuff"></div>
