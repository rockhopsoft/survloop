/* generated from resources/views/vendor/survloop/js/scripts-ajax-colors.blade.php */

function showColorList(fldName) {
    if (document.getElementById(""+fldName+"ID") && document.getElementById("colorPick"+fldName+"")) {
        if (document.getElementById("colorPick"+fldName+"").innerHTML == "") {
            var src = "/ajax/color-pick?fldName="+fldName+"&preSel="+document.getElementById(""+fldName+"ID").value.replace("#", "")+"";
            $("#colorPick"+fldName+"").load(src);
        } else {
            $("#colorPick"+fldName+"").slideDown("fast");
        }
    }
    return true;
}
$(document).on("click", ".colorPickFld", function() {
    return showColorList($(this).attr("name"));
});
$(document).on("click", ".colorPickFldSwatch", function() {
    return showColorList($(this).attr("id").replace("ColorSwatch", ""));
});
function setColorFld(fldName, val) {
    if (document.getElementById(""+fldName+"ID") && document.getElementById("colorPick"+fldName+"")) {
        document.getElementById(""+fldName+"ID").value = val;
        document.getElementById(""+fldName+"ColorSwatch").style.backgroundColor = val;
        $("#colorPick"+fldName+"").slideUp("fast");
    }
    return true;
}
$(document).on("click", ".colorPickRadio", function() {
    setColorFld($(this).attr("name").replace("Radio", ""), $(this).val());
    return true;
});
$(document).on("click", ".colorPickFldSwatchBtn", function() {
    var colorArr = $(this).attr("id").split("ColorSwatch");
    if (document.getElementById(""+colorArr[0]+"CustomID")) {
        return setColorFld(colorArr[0], "#"+colorArr[1]+"");
    }
    return true;
});
function setColorToCustom(fldName) {
    if (document.getElementById(""+fldName+"CustomID")) {
        return setColorFld(fldName, document.getElementById(""+fldName+"CustomID").value);
    }
    return true;
}
$(document).on("click", ".colorPickCustomBtn", function() {
    var fldName = $(this).attr("id").replace("SetCustomColor", "");
    return setColorToCustom(fldName);
});
$(document).on("keyup", ".colorPickCustomFld", function(e) {
    var fldName = $(this).attr("name").replace("Custom", "");
    if (document.getElementById(""+fldName+"CustomColor")) {
        document.getElementById(""+fldName+"CustomColor").style.backgroundColor = $(this).val();
    }
    if (e.keyCode == 13) {
        var fldName = $(this).attr("name").replace("Custom", "");
        setColorToCustom(fldName);
        e.preventDefault();
        return false;
    }
    return true;
});
