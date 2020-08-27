<!-- resources/views/vendor/survloop/admin/tree/node-edit-xmlmap.blade.php -->

<h2><i class="fa fa-snowflake-o mR3"></i> Data Table XML Map </h2>

<div class="p10 fC"></div>

<form name="mainPageForm" method="post" 
    @if (isset($node->nodeRow) && isset($node->nodeRow->node_id))
        action="/dashboard/surv-{{ $GLOBALS['SL']->treeID 
        }}/xmlmap/node/{{ $node->nodeRow->node_id }}"
    @else
        action="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/xmlmap/node/-3"
    @endif >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">
<input type="hidden" name="treeID" value="{{ $GLOBALS['SL']->treeID }}">
<input type="hidden" name="nodeParentID" 
    @if ($REQ->has('parent') && intVal($REQ->input('parent')) > 0) value="{{ $REQ->input('parent') }}"
    @else value="{{ $node->parentID }}" @endif >
<input type="hidden" name="childPlace" 
    @if ($REQ->has('start') && intVal($REQ->input('start')) > 0) value="start"
    @elseif ($REQ->has('end') && intVal($REQ->input('end')) > 0) value="end" 
    @else value="" @endif >
<input type="hidden" name="orderBefore" 
    @if ($REQ->has('ordBefore') && intVal($REQ->ordBefore) > 0)
        value="{{ $REQ->ordBefore }}"
    @else value="-3" @endif >
<input type="hidden" name="orderAfter" 
    @if ($REQ->has('ordAfter') && intVal($REQ->ordAfter) > 0) 
        value="{{ $REQ->ordAfter }}"
    @else value="-3" @endif >

<div class="card">
    <div class="card-header">
        @if (isset($node->nodeRow->node_id) && $node->nodeRow->node_id > 0) 
            <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID 
                }}/xmlmap?all=1#n{{ $node->nodeRow->node_id 
                }}" class="float-right">Back to XML Map</a>
            <h4 class="disIn"><span class="mR20">
                #{{ $node->nodeRow->node_id }}</span> Editing Node</h4>
        @else 
            <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/xmlmap?all=1" 
                class="float-right">Back to XML Map</a>
            <h4 class="disIn">Adding Node</h4>
        @endif
    </div>
    <div class="card-body">
        <div class="row mT20">
            <div class="col-2">
                <label class="mB20"><nobr>
                    <input type="radio" name="xmlNodeType" id="xmlNodeTypeTbl" 
                        class="xmlDataChng" autocomplete="off" 
                        value="dataTbl" 
                        @if (intVal($node->nodeRow->node_prompt_notes) > 0) 
                            CHECKED 
                        @endif >
                    <b class="slBlueDark">Data Table:</b>
                </nobr></label>
                <label><nobr>
                    <input type="radio" name="xmlNodeType" id="xmlNodeTypeWrap" 
                        class="xmlDataChng" autocomplete="off" 
                        value="dataWrap" 
                        @if (intVal($node->nodeRow->node_prompt_notes) <= 0) 
                            CHECKED
                        @endif >
                    <b>Extra Wrap:</b>
                </nobr></label>
            </div>
            <div class="col-6">
                <div id="xmlDataTbl" class=" 
                    @if (intVal($node->nodeRow->node_prompt_notes) <= 0) disNon @else disBlo 
                    @endif ">
                    <select name="nodePromptText" id="nodePromptTextID" 
                        class="form-control">
                        {!! $GLOBALS['SL']->tablesDropdown(
                            $node->nodeRow->node_prompt_text
                        ) !!}
                    </select>
                    <div class="p10"></div>
                    <label class="mR20 mL20">
                        <input type="checkbox" name="opts7" value="7" 
                            class="mR20" autocomplete="off"
                            @if ($node->nodeOpts%7 == 0) CHECKED @endif 
                            > Min 1 Record
                    </label>
                    <label class="mR20 mL20">
                        <input type="checkbox" name="opts11" value="11" 
                            class="mR20" autocomplete="off"
                            @if ($node->nodeOpts%11 == 0) CHECKED @endif 
                            > Max 1 Record
                    </label>
                    <label class="mR20 mL20">
                        <input type="checkbox" name="opts5" value="5" 
                            class="mR20" autocomplete="off"
                            @if ($node->nodeOpts%5 == 0) CHECKED @endif 
                            > Include members with parent, without table wrap
                    </label>
                </div>
                <div id="xmlDataWrap" class="mT20 pT20 
                    @if (intVal($node->nodeRow->node_prompt_notes) <= 0) disBlo @else disNon 
                    @endif ">
                    <input name="wrapPromptText" id="wrapPromptTextID" 
                        type="text" class="form-control" 
                        value="{{ $node->nodeRow->node_prompt_text }}">
                </div>
            </div>
            <div class="col-4 taR">
                <input type="submit" value="Save Node Changes" 
                    class="btn btn-lg btn-primary" 
                    @if (!$canEditTree) DISABLED @endif >
            </div>
        </div>
    </div>
</div>
@if ($node->nodeRow->node_id > 0 && $canEditTree)
    <div class="p30 mB30">
        <label>
            <input name="deleteNode" id="deleteNodeID" 
                type="checkbox" value="1" class="mR3" > 
            Delete This Node
        </label>
    </div>
@endif
</form>