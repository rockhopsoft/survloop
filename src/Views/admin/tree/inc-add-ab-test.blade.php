<!-- resources/views/vendor/survloop/admin/tree/inc-add-ab-test.blade.php -->
<div class="mT10">
    <a id="hidivBtnAddTestAB" class="hidivBtn" href="javascript:;">Add AB Test</a>
    <div id="hidivAddTestAB" class="disNon">
        <label for="addTestABID">Show node if we (A) are running this AB Test, or (B) not.</label>
        <select name="addTestAB" id="addTestABID" class="form-control" autocomplete="off">
            <option value="" SELECTED >Select AB Test...</option>
        @forelse ($GLOBALS["SL"]->condABs as $i => $ab)
            <option value="{{ $ab[0] }}.A">{{ $ab[0] }}.A - {{ $ab[1] }}</option>
            <option value="{{ $ab[0] }}.B">{{ $ab[0] }}.B - NOT</option>
        @empty
        @endforelse
            <option value="NewAB">Create new test for {{ $GLOBALS["SL"]->treeRow->tree_name }}</option>
        </select>
        <div id="addNewTestAB" class="disNon pT10">
            <label for="addTestABdescID">AB Test Description</label>
            <input type="text" name="addTestABdesc" id="addTestABdescID" 
                class="form-control" autocomplete="off">
            <div class="mT5"><label>
                <input type="radio" name="addTestABwhich" value="A" 
                    class="mR5" CHECKED autocomplete="off">Shows node for test
            </label></div>
            <div class="mT5"><label>
                <input type="radio" name="addTestABwhich" value="B" 
                    class="mR5" autocomplete="off">Show node for non-test
            </label></div>
        </div>
    </div>
</div>
<script type="text/javascript"> $(document).ready(function(){
$(document).on("change", "#addTestABID", function() {
    if (document.getElementById('addTestABID').value == 'NewAB') {
        $("#addNewTestAB").slideDown('fast');
    } else {
        $("#addNewTestAB").slideUp('fast');
    }
    return true;
});
}); </script>