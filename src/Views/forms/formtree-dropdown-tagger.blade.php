<!-- resources/views/survloop/forms/formtree-dropdown-tagger.blade.php -->

<input name="n{{ $curr->nIDtxt }}tagIDs" id="n{{ $curr->nIDtxt }}tagIDsID" 
    data-nid="{{ $curr->nID }}" type="hidden" value="," 
    class="{{ $curr->xtraClass }}">
<div id="n{{ $curr->nIDtxt }}tags" class="slTagList"></div>
