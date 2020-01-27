<!-- resources/views/vender/survloop/elements/inc-search-bar.blade.php -->
<div class="nodeAnchor"><a name="search"></a></div>
@if (isset($pre)) {!! $pre !!} @endif
<div class="search-bar">
    <input type="text" id="searchBar{{ $nID }}t{{ $treeID }}" name="s{{ $nID }}" 
        class="form-control form-control-lg slTab searchBar" {!! $GLOBALS["SL"]->tabInd() !!}
        @if (isset($search)) value="{{ $search }}" @else value="" @endif >
    <div class="search-btn-wrap"><a id="searchTxt{{ $nID }}t{{ $treeID }}" 
        class="btn btn-info searchBarBtn" href="javascript:;"
        @if (!isset($ajax) || intVal($ajax) == 0) target="_parent" @endif 
        ><i class="fa fa-search" aria-hidden="true"></i></a></div>
</div>
<input type="hidden" name="advUrl" id="advUrlID" value="{{ $advUrl }}">
@if (isset($extra) && trim($extra) != '') {!! $extra !!} @endif
@if (isset($advanced) && trim($advanced) != '')
    <div class="fR pT15"><a id="searchAdvBtn{{ $nID }}t{{ $treeID }}" 
        class="searchAdvBtn" href="javascript:;"
        >Advanced filters <i class="fa fa-cogs" aria-hidden="true"></i></a></div>
@endif
<div class="fC"></div>
@if (isset($advanced) && trim($advanced) != '')
    <div id="searchAdv{{ $nID }}t{{ $treeID }}" class=" @if ($advUrl != '') disBlo @else disNon @endif ">
        {!! $advanced !!}
    </div>
@endif

@if (isset($advanced) && trim($advanced) != "")
<script type="text/javascript">
var advUrlArr = new Array();
function printAdvSrch() {
    var retUrl="";
    for (var i=0; i < advUrlArr.length; i++) {
        retUrl+="&"+advUrlArr[i][0]+"="+advUrlArr[i][1];
    }
    if (document.getElementById("advUrlID")) document.getElementById("advUrlID").value=retUrl;
    return true;
}
function addAdvSrch(key, val) {
    var found=false;
    for (var i=0; i < advUrlArr.length; i++) {
        if (advUrlArr[i][0] == key) {
            advUrlArr[i][1]=val;
            found=true;
        }
    }
    if (!found) advUrlArr[advUrlArr.length] = new Array(key, val);
    return printAdvSrch();
}
function delAdvSrch(key) {
    var retArr = new Array();
    for (var i = 0; i < advUrlArr.length; i++) {
        if (advUrlArr[i][0] != key) {
            retArr[retArr.length] = new Array(advUrlArr[i][0], advUrlArr[i][1]);
        }
    }
    advUrlArr = retArr;
    return printAdvSrch();
}
function checkboxAdvSrch(key, val) {
    if (document.getElementById(""+key+"ID") && document.getElementById(""+key+"ID").checked) {
        addAdvSrch(key, val);
    } else {
        delAdvSrch(key);
    }
    return true;
}
{!! $advBarJS !!}
</script>
@endif

@if (isset($post) && trim($post) != '') {!! $post !!} @endif
