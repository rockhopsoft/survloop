<!-- resources/views/vendor/survloop/print-tree-map-desc.blade.php -->
<div class="pL10">
<h2>{{ $GLOBALS["SL"]->treeRow->TreeName }}: Survey Form Specs</h2>
<p>
This document shows all questions asked in this user survey. 
Some questions are conditional depending on previous responses ( e.g. 
    <i class="fa fa-code-fork fa-flip-vertical"></i> , 
    <i class="fa fa-filter" aria-hidden="true"></i> ).
The left side of this <a href="https://en.wikipedia.org/wiki/Tree_%28data_structure%29" target="_blank"
    >branching tree</a> shows the plain text of the questions and responses presented to the user.
The right side of this tree shows where the user's responses will be stored in the database design.
</p>
</div>