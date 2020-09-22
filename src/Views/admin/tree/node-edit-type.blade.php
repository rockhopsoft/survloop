<!-- resources/views/vendor/survloop/admin/tree/node-edit-type.blade.php -->
<div class="slBlueDark mBn20"><b>
    <i class="fa fa-cube mR5" aria-hidden="true"></i> Node Type
</b></div>
<div id="nodeTypeFld1" class="nFld w100 pT5">
    <select name="nodeType" id="nodeTypeID" class="form-control" 
        autocomplete="off" onChange="return changeNodeType(this.value);" 
        {{ $nodeTypeSel }} >
    
    @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
    
        <option value="instruct" @if ($node->isInstruct()) SELECTED @endif >
            Content Chunk: Using WYSIWYG Editor</option>
        <option value="instructRaw" @if ($node->isInstructRaw()) SELECTED @endif >
            Content Chunk: Hard-code HTML, JS, CSS</option>
        <option value="question" @if (!$node->isSpecial()) SELECTED @endif > 
            Question Prompting User Response</option>
        <option value="bigButt" @if ($node->isBigButt()) SELECTED @endif >
            Big Button</option>
        <option value="branch" @if ($node->isBranch()) SELECTED @endif >
            Just A Branch Title</option>
        <option value="cycle" @if ($node->isLoopCycle()) SELECTED @endif >
            Loop: Root Node of one or more Nodes</option>
        <?php /* <option value="sort" @if ($node->isLoopSort()) SELECTED @endif >
            Sort Survloop Responses</option> */ ?>
    
    @else 
    
        <option value="question" @if (!$node->isSpecial()) SELECTED @endif 
            >Question Prompting User Response</option>
        <option value="instruct" @if ($node->isInstruct()) SELECTED @endif 
            >Instruction (no response): Using WYSIWYG Editor</option>
        <option value="instructRaw" @if ($node->isInstructRaw()) SELECTED @endif 
            >Instruction (no response): Hard-code HTML, JS, CSS</option>
        <option value="bigButt" @if ($node->isBigButt()) SELECTED @endif 
            >Big Button</option>
        <option value="loop" @if ($node->isLoopRoot()) SELECTED @endif 
            >Loop Multiple Pages</option>
        <option value="cycle" @if ($node->isLoopCycle()) SELECTED @endif 
            >Loop Within One Page</option>
        <option value="sort" @if ($node->isLoopSort()) SELECTED @endif 
            >Sort Loop Responses</option>
            
    @endif
    
    <option value="data" @if ($node->isDataManip()) SELECTED @endif 
        >Data Manipulation</option>
    <option value="dataPrint" @if ($node->isDataPrint()) SELECTED @endif 
        >Data Printout</option>
    <option value="survWidget" @if ($node->isWidget()) SELECTED @endif 
        >Survloop Widget</option>
    <option value="sendEmail" @if ($node->nodeType == 'Send Email') SELECTED @endif 
        >Send Email</option>
    <option value="layout" @if ($node->isLayout()) SELECTED @endif 
        >Layout</option>
    
    <option value="page" @if ($node->isPage()) SELECTED @endif 
        >Page Wrapper</option>
    @if ($GLOBALS['SL']->treeRow->tree_type != 'Page')
        <option value="branch" @if ($node->isBranch()) SELECTED @endif 
            >Navigation Branch Title</option>
    @endif
    
    </select>
</div>
<div id="responseType" 
    class="@if ($node->isSpecial()) disNon @else disBlo @endif ">
    <div class="nFld m0">
        <select name="nodeTypeQ" id="nodeTypeQID" class="form-control slBlueDark w100"
            onChange="return changeResponseType(this.value);" autocomplete="off" >
    @foreach ($nodeTypes as $type)
        <option value="{{ $type }}"
            @if (isset($node->nodeRow->node_type) && $node->nodeRow->node_type == $type) 
                SELECTED
            @endif >{{ $type }}</option>
    @endforeach
        </select>
    </div>
    @if (isset($parentNode->node_type) 
        && in_array($parentNode->node_type, ['Checkbox']))
        <div class="mT5 mB10 slGrey fPerc80">
            <i>Select <span class="blk">Layout Sub-Response</span> to make this 
            node's children appear within each of the parent's responses to a 
            <span class="blk">Checkbox</span>.</i>
        </div>
    @else <div class="m5"></div>
    @endif
</div>
<div id="dataPrintType" 
    class="@if ($node->isDataPrint()) disBlo @else disNon @endif ">
    <div class="nFld m0"><select name="nodeTypeD" id="nodeTypeDID" 
        class="form-control slBlueDark w100" autocomplete="off"
        onChange="return changeDataPrintType(this.value);" >
        <option value="Data Print Row" @if (!isset($node->nodeRow->node_type) 
            || in_array(trim($node->nodeRow->node_type), ['', 'Data Print Row', 'Instructions'])) 
            SELECTED @endif >Data Block Row</option>
        <option value="Data Print Block" @if (isset($node->nodeRow->node_type) 
            && $node->nodeRow->node_type == 'Data Print Block') SELECTED @endif 
            >One Block of Data Rows</option>
        <option value="Data Print Columns" @if (isset($node->nodeRow->node_type) 
            && $node->nodeRow->node_type == 'Data Print Columns') SELECTED @endif 
            >Columns of Data Rows</option>
        <option value="Print Vert Progress" @if (isset($node->nodeRow->node_type) 
            && $node->nodeRow->node_type == 'Print Vert Progress') SELECTED @endif 
            >Column of Progress</option>
        <option value="Data Print" @if (isset($node->nodeRow->node_type) 
            && $node->nodeRow->node_type == 'Data Print') SELECTED @endif 
            >Plain Data Printout</option>
    </select></div>
</div>
<div id="widgetType" class="@if ($node->isWidget()) disBlo @else disNon @endif nFld mT0 ">
    <select name="nodeSurvWidgetType" id="nodeSurvWidgetTypeID" autocomplete="off" 
        class="form-control w100" onChange="return changeWidgetType();" >
        
        <option value="Record Full" 
            @if ($node->nodeType == 'Record Full') SELECTED @endif 
            >Record Full (Default User Privileges)</option>
        <option value="Record Full Public" 
            @if ($node->nodeType == 'Record Full Public') SELECTED @endif 
            >Record Full Public</option>
        @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
        
            <option value="Search" @if ($node->nodeType == 'Search') SELECTED @endif 
                >Search Bar</option>
            <option value="Search Results" 
                @if ($node->nodeType == 'Search Results') SELECTED @endif 
                >Search Results</option>
            <option value="Search Featured" 
                @if ($node->nodeType == 'Search Featured') SELECTED @endif 
                >Search Featured</option>
            <option value="Record Previews" 
                @if ($node->nodeType == 'Record Previews') SELECTED @endif 
                >Record Previews</option>
            <option value="Plot Graph" @if ($node->nodeType == 'Plot Graph') SELECTED @endif 
                >Plot Graph</option>
            <option value="Line Graph" @if ($node->nodeType == 'Line Graph') SELECTED @endif 
                >Line Graph</option>
            <option value="Bar Graph" @if ($node->nodeType == 'Bar Graph') SELECTED @endif 
                >Bar Graph</option>
            <option value="Pie Chart" @if ($node->nodeType == 'Pie Chart') SELECTED @endif 
                >Pie Chart</option>
            <option value="Map" @if ($node->nodeType == 'Map') SELECTED @endif 
                >Map</option>
            <option value="Incomplete Sess Check" 
                @if ($node->nodeType == 'Incomplete Sess Check') SELECTED @endif 
                >Incomplete Sessions Check</option>
            <option value="Member Profile Basics" 
                @if ($node->nodeType == 'Member Profile Basics') SELECTED @endif 
                >Member Profile Basics</option>
            <option value="MFA Dialogue" 
                @if ($node->nodeType == 'MFA Dialogue') SELECTED @endif 
                >Token Access Dialogue</option>
                
        @endif
        
            <option value="Back Next Buttons" 
                @if ($node->nodeType == 'Back Next Buttons') SELECTED @endif 
                >Extra Next [Back] Buttons</option>
            <option value="Widget Custom" 
                @if ($node->nodeType == 'Widget Custom') SELECTED @endif 
                >Custom-Written Widget</option>
        
    </select>
</div>