<!-- resources/views/vendor/survloop/admin/tree/tree-about.blade.php -->

<div id="adminAbout" class=" @if ($showAbout) disBlo @else disNon @endif ">
    <div class="container">
        <div class="slCard nodeWrap">
            <img src="/survloop/uploads/branching-tree.png" width="20%" 
                align="right" class="mL20 round10" alt="Branching Tree">
            <h1>Branching Tree, <nobr>Form Generator</nobr></h1>
            <p>
            <b>Each user experience</b> is defined by a branching tree, similar to 
            a "choose your own adventure" book. The spots where branches (or paths) 
            separate from each other are often called 
            <a href="https://en.wikipedia.org/wiki/Tree_%28data_structure%29" 
                target="_blank"><b>nodes</b></a>.
            Most nodes in this tree represents either a single question to ask the user, 
            instructions, or a <nobr>page (<i class="fa fa-file-text-o"></i>)</nobr> which 
            groups multiple child questions on one screen.
            </p>
            <p>
            Some nodes have children which respond to certain user choices 
            (<i class="fa fa-code-fork fa-flip-vertical"></i>) by revealing more questions 
            on the page. Many options are provided when editing a node, and most questions can be 
            easily setup to automatically store user responses to a specific field in the database.
            </p>
            <p>
            A few nodes are "branches" (<span class="f12"><i class="fa fa-share-alt"></i></span>) 
            which wrap multiple pages and/or sub-branches, and are used more internally to define 
            main sections and subsections of the entire process. These often help define the 
            navigation menu provided for the user.
            </p>
            <p>
            Some nodes are marked as the root node of a Survloop 
            (<span class="f12"><i class="fa fa-refresh"></i></span>). 
            The children of a Survloop node will loop through multiple records of one collection 
            of data. They serve one or more questions, pages, or whole branches, for each record. 
            They can be setup to allow the user to add new records as part of that process, 
            or cycle through previously entered data sets.
            </p>
            <p>
            In the map of your user experience's tree below, the left side previews both what 
            the user will see, and the right side details how it all gets into the database.
            </p>
            <div class="float-right mBn20">
                <a href="javascript:;" class="adminAboutTog"
                    >Hide About <i class="fa fa-times" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</div>