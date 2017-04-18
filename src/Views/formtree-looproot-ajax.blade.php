/* resources/views/vendor/survloop/formtree-looproot-ajax.blade.php */

var currItemCnt = {{ $loopSize }};
var maxItemCnt = {{ $GLOBALS['SL']->closestLoop["obj"]->DataLoopMaxLimit }};
$("#nFormAdd").click(function() {
    document.getElementById("loopItemID").value="-37";
    return runFormSub();
});
$(".delLoopItem").click(function() {
    var id = $(this).attr("id").replace("delLoopItem", "");
    document.getElementById("delItem"+id+"").checked=true;
    document.getElementById("wrapItem"+id+"On").style.display="none";
    document.getElementById("wrapItem"+id+"Off").style.display="block";
    updateCnt(-1);
    return true;
});
$(".unDelLoopItem").click(function() {
    var id = $(this).attr("id").replace("unDelLoopItem", "");
    document.getElementById("delItem"+id+"").checked=false;
    document.getElementById("wrapItem"+id+"On").style.display="block";
    document.getElementById("wrapItem"+id+"Off").style.display="none";
    updateCnt(1);
    return true;
});
function updateCnt(addCnt) {
    currItemCnt += addCnt;
    if (maxItemCnt <= 0 || currItemCnt < maxItemCnt) document.getElementById("nFormAdd").style.display="block";
    else document.getElementById("nFormAdd").style.display="none";
    return true;
}
