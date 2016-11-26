<!-- resources/views/vendor/survloop/admin/tree/tree-about.blade.php -->

<div id="adminAbout" class="jumbotron 
@if ($showAbout)
	disBlo
@else
	disNon
@endif
">
	<div class="fR"><a href="javascript:void(0)" id="adminAboutBtn" >Hide About</a></div>
	<h1>Branching Tree, Form Generator</h1>
	<p>This branching tree defines the detailed work flow which a user experiences while submitting their complaint. 
	Most <a href="https://en.wikipedia.org/wiki/Tree_%28data_structure%29" target="_blank"><b>nodes</b></a> in this tree represents either a single field, 
	or a <nobr>page (<i class="fa fa-file-text-o"></i>)</nobr> which wraps multiple child fields (marked <nobr>(<i class="fa fa-angle-double-up"></i>)</nobr> as appearing "on parent's page"). 
	Children not "appearing on parent's page" are otherwise next in chronological/traversal order after their parent. Some nodes have responsive children which 
	a page reveals to the user if certain responses (<i class="fa fa-code-fork fa-flip-vertical"></i>) are submitted. Most nodes are specified to automatically 
	store user responses to specific database fields.
	</p><p>
	A few nodes are "branches" (<span class="f12"><i class="fa fa-share-alt"></i></span>) which wrap multiple pages and/or 
	sub-branches, and are used more internally to define main sections and subsections of the entire process and help define the navigation of the user's 
	complaint progress. Finally, some nodes are marked as the root node of a "Data Set Navigation Loop" (<span class="f12"><i class="fa fa-refresh"></i></span>), 
	which loops the user through entering multiple records of one type of data.
	</p>
</div> <!-- end jumbotron -->
