<!-- resources/views/survloop/forms/formtree-widget.blade.php -->

@if (trim($curr->nodeRow->node_prompt_text) != '')
    <div>{!! 
        $GLOBALS["SL"]->extractJava($curr->nodeRow->node_prompt_text, $curr->nID) 
    !!}</div>
@endif
<div id="n{{ $curr->nIDtxt }}ajaxLoad" class="w100">{!! $spin !!}</div>
@if (trim($curr->nodeRow->node_prompt_after) != '')
    <div>{!! 
        $GLOBALS["SL"]->extractJava($curr->nodeRow->node_prompt_after, $curr->nID) 
    !!}</div>
@endif
