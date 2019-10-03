/* generated from resources/views/vendor/survloop/js/scripts-ajax-forms.blade.php */

function runSaveReload() {
    if (document.getElementById("stepID")) {
        document.getElementById("stepID").value="save";
        return runFormSub();
    }
    if (document.mainPageForm) {
        document.mainPageForm.submit();
        return true;
    }
    location.reload();
    return false;
}
function chkRunSaveReload() {
    if (cntDownOver) {
        $("#nondialog").fadeIn(300);
        $("#dialog").fadeOut(300);
        cntDownOver = false;
        return runSaveReload();
    }
    setTimeout(function() { chkRunSaveReload(); }, 2000);
}
setTimeout(function() { chkRunSaveReload(); }, 2000);
$(document).on("click", ".nFormSaveReload", function() { runSaveReload(); });

function runFormSubAjax() {
    blurAllFlds();
    var formData = new FormData(document.getElementById("postNodeForm"));
    replaceAjaxWithSpinner();
    addProTipToAjax();
    window.scrollTo(0, 0);
    if (document.getElementById("postActionID")) actionUrl = document.getElementById("postActionID").value;
    $.ajax({
        url: formActionUrl,
        type: "POST", 
        data: formData, 
        contentType: false,
        processData: false,
        success: function(data) {
            $("#ajaxWrap").empty();
            $("#ajaxWrap").append(data);
        }, 
        error: function(xhr, status, error) {
            $("#ajaxWrap").append("<div>(error - "+xhr.responseText+")</div>");
        }
    });
    resetCheckForm();
    return false;
}

function runFormSub() {
    if (document.getElementById("isPage")) {
        document.postNode.submit();
    } else {
        runFormSubAjax();
    }
    return false;
}

function getUpID(thisAttr) {
    return thisAttr.replace("delLoopItem", "").replace("confirmN", "").replace("confirmY", "").replace("editLoopItem", "");
}

$(document).on("click", "#nFormUpload", function() {
    if (checkNodeForm()) {
        document.getElementById("stepID").value="upload";
        return runFormSub();
    } else {
        return false;
    }
});
$(document).on("click", ".nFormUploadSave", function() {
    document.getElementById("stepID").value="uploadSave";
    document.getElementById("altID").value=getUpID( $(this).attr("id") );
    return runFormSub();
});

function exitLoop(whichWay) {
    if (document.getElementById("stepID") && document.getElementById("jumpToID") && checkNodeForm()) {
        document.getElementById("stepID").value="exitLoop"+whichWay;
        document.getElementById("jumpToID").value=document.getElementById("isLoopNav").value;
        runFormSub();
    }
    return false;
}

function checkNodeForm() {
    if (document.getElementById("stepID") && document.getElementById("stepID").value == "back") return true;
    hasAttemptedSubmit = true;
    totFormErrors = 0;
    formErrorsEng = "";
    firstNodeError = "";
    for (var i = 0; i < reqNodes.length; i++) {
        if (document.getElementById('n'+reqNodes[i][0]+'VisibleID') && document.getElementById('n'+reqNodes[i][0]+'VisibleID').value == 1) {
            if (reqNodes[i][1] == 'reqFormFld') {
                reqFormFld(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldOther') {
                reqFormFldOther(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldEmail') {
                reqFormFldEmail(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldRadio') {
                reqFormFldRadio(reqNodes[i][0], reqNodes[i][2]);
            } else if (reqNodes[i][1] == 'reqFormFldRadioCustom') {
                reqFormFldRadioCustom(reqNodes[i][0], reqNodes[i][2]);
            } else if (reqNodes[i][1] == 'reqFormFeetInches') {
                reqFormFeetInches(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormGender') {
                reqFormGender(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldTbl') {
                reqFormFldTbl(reqNodes[i][2], reqNodes[i][0], reqNodes[i][3], reqNodes[i][4], reqNodes[i][5]);
            } else if (reqNodes[i][1] == 'reqFormFldGreater') {
                reqFormFldGreater(reqNodes[i][0], reqNodes[i][2]);
            } else if (reqNodes[i][1] == 'reqFormFldLesser') {
                reqFormFldLesser(reqNodes[i][0], reqNodes[i][2]);
            } else if (reqNodes[i][1] == 'reqFormFldDate') {
                reqFormFldDate(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldDate') {
                reqFormFldDate(reqNodes[i][0]);
            } else if (reqNodes[i][1] == 'reqFormFldDateLimit') {
                reqFormFldDateLimit(reqNodes[i][0], reqNodes[i][2], reqNodes[i][3], reqNodes[i][4]);
            } else if (reqNodes[i][1] == 'reqFormFldDateAndLimit') {
                reqFormFldDateAndLimit(reqNodes[i][0], reqNodes[i][2], reqNodes[i][3], reqNodes[i][4]);
            }
        }
    }
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    clearFormErrs();
    return true; 
}

function checkForm() {
    if (!foundForm) {
        if (document.getElementById("postNodeForm")) {
            foundForm = true;
        } else {
            setTimeout(function() {
                if (typeof checkForm === "function") checkForm();
            }, 10000);
        }
    }
    return true;
}

function resetCheckForm() {
    foundForm = false;
    setTimeout(function() {
        if (typeof checkForm === "function") checkForm();
    }, 10000);
    return true;
}

function chkFormCheck() {
    if (hasAttemptedSubmit) return checkNodeForm();
    return false;
}

function setFormLabelBlack(nIDtxt) {
    if (document.getElementById("node"+nIDtxt+"")) {
        document.getElementById("node"+nIDtxt+"").className=document.getElementById("node"+nIDtxt+"").className.replace("nodeWrapError", "nodeWrap");
    }
    return true;
}

function setFormLabelRed(nIDtxt) {
    if (pressedSubmit && firstNodeError == "") {
        firstNodeError = nIDtxt;
        slideToHshooPos("n"+nIDtxt+"");
    }
    if (document.getElementById("node"+nIDtxt+"")) {
        document.getElementById("node"+nIDtxt+"").className=document.getElementById("node"+nIDtxt+"").className.replace("nodeWrap", "nodeWrapError").replace("nodeWrapErrorError", "nodeWrapError");
    }
    return true;
}

$(document).on("click", ".nFormNext", function() {
    if (document.getElementById("stepID")) {
        pressedSubmit = true;
        if (document.getElementById("isLoopNav")) return exitLoop("");
        if (checkNodeForm()) {
            document.getElementById("stepID").value="next";
            return runFormSub();
        }
        pressedSubmit = false;
    }
    return false;
});

$(document).on("click", ".nFormBack", function() {
    if (document.getElementById("stepID")) {
        if (document.getElementById("isLoopNav")) return exitLoop("Back");
        document.getElementById("stepID").value="back";
        if (document.getElementById("loopRootJustLeftID") && document.getElementById("loopRootJustLeftID").value > 0) {
            document.getElementById("jumpToID").value=document.getElementById("loopRootJustLeftID").value;
        }
        return runFormSub();
    }
    return false;
});

$(document).on("click", "a.navJump", function() {
    if (document.getElementById("stepID") && document.getElementById("jumpToID")) {
        document.getElementById("jumpToID").value = $(this).attr("id").replace("jump", "");
        if (document.getElementById("dataLoopRootID")) document.getElementById("stepID").value="exitLoopJump";
        return runFormSub();
    }
    return false;
});

$(document).on("click", "a.saveAndRedir", function() {
    if (document.getElementById("stepID") && document.getElementById("jumpToID")) {
        document.getElementById("stepID").value="save";
        document.getElementById("jumpToID").value = $(this).attr("data-redir-url");
        if (document.getElementById("afterJumpToID") && document.getElementById("treeSlugID") && document.getElementById("nodeSlugID")) {
            document.getElementById("afterJumpToID").value = "/u/"+document.getElementById("treeSlugID").value+"/"+document.getElementById("nodeSlugID").value;
        }
        return runFormSub();
    }
    return false;
});

function postNodeAutoSave() {
    if (document.getElementById('postNodeForm') && document.getElementById('stepID') && document.getElementById('stepID')) {
        if (!document.getElementById('emailBlockID') && !document.getElementById('noAutoSaveID')) {
            var origStep = document.getElementById('stepID').value;
            var origTarget = document.postNode.target;
            document.getElementById('stepID').value = "autoSave";
            document.postNode.target = "hidFrame";
            document.postNode.submit();
            document.getElementById('stepID').value = origStep;
            document.postNode.target = origTarget;
            setTimeout(function() { postNodeAutoSave() }, 60000);
            return true;
        }
    }
    return false;
}
setTimeout(function() { if (!document.getElementById("isPage")) postNodeAutoSave(); }, 90000);

window.onpopstate = function(event) {
    if (document.getElementById("stepID") && !document.getElementById("isPage")) {
        var newPage = document.location.href;
        newPage = newPage.replace("{{ $GLOBALS['SL']->sysOpts['app-url'] }}", "");
        document.getElementById("stepID").value = "save";
        if (document.getElementById("popStateUrlID")) document.getElementById("popStateUrlID").value = newPage;
        return runFormSub();
    }
    return false;
};

function timeoutChecks() {
    if (otherFormSub) return runFormSub();
    if (document.getElementById("admMenu")) {
        var leftPos = $(document).scrollLeft();
        if (leftPos > 0) document.getElementById("leftSideWrap").style.position="static";
        else document.getElementById("leftSideWrap").style.position="fixed";
    }
    setTimeout(function() { timeoutChecks(); }, 2000);
    return true;
}
setTimeout(function() { timeoutChecks(); }, 500);

$(document).on("click", ".editLoopItem", function() {
    var id = $(this).attr("id").replace("editLoopItem", "").replace("arrowLoopItem", "");
    document.getElementById("loopItemID").value=id;
    return runFormSub();
});
var limitTog = false;
function toggleLineEdit(upID) {
    if (!limitTog) {
        limitTog = true;
        setTimeout(function() { limitTog = false; }, 700);
        if (document.getElementById("up"+upID+"InfoEdit").style.display != 'block') {
            $("#up"+upID+"Info").slideUp("fast");
            setTimeout(function() { $("#up"+upID+"InfoEdit").slideDown("fast"); }, 301);
            document.getElementById("up"+upID+"EditVisibID").value="0";
        } else {
            $("#up"+upID+"InfoEdit").slideUp("fast");
            setTimeout(function() { $("#up"+upID+"Info").slideDown("fast"); }, 301);
            document.getElementById("up"+upID+"EditVisibID").value="1";
        }
    }
    return true;
}
$(document).on("click", ".nFormLnkEdit", function() {
    return toggleLineEdit(getUpID( $(this).attr("id") ));
});
$(document).on("click", ".nFormLnkDel", function() {
    var upID = getUpID( $(this).attr("id") );
    $("#editLoopItem"+upID+"block").slideUp("fast");
    $("#delLoopItem"+upID+"confirm").slideDown("fast");
    return true;
});
$(document).on("click", ".nFormLnkDelConfirmNo", function() {
    var upID = getUpID( $(this).attr("id") );
    $("#delLoopItem"+upID+"confirm").slideUp("fast");
    $("#editLoopItem"+upID+"block").slideDown("fast");
    return true;
});
$(document).on("click", ".nFormLnkDelConfirmYes", function() {
    document.getElementById("stepID").value="uploadDel";
    document.getElementById("altID").value=getUpID( $(this).attr("id") );
    return runFormSub();
});
$(document).on("click", "#recMgmtDelX", function() {
    $("#hidivRecMgmtDel").slideUp("fast");
});

$(document).on("click", "#nFormNextStepItem", function() {
    document.getElementById("loopItemID").value=loopItemsNextID;
    document.getElementById("jumpToID").value="-3";
    document.getElementById("stepID").value="next";
    return runFormSub();
});
$(document).on("click", "#nFormAdd", function() {
    if (document.getElementById("loopItemID")) document.getElementById("loopItemID").value="-37";
    return runFormSub();
});
$(document).on("click", ".delLoopItem", function() {
    var id = $(this).attr("id").replace("delLoopItem", "");
    document.getElementById("delItem"+id+"").checked=true;
    document.getElementById("wrapItem"+id+"On").style.display="none";
    document.getElementById("wrapItem"+id+"Off").style.display="block";
    updateCnt(-1);
    return true;
});
$(document).on("click", ".unDelLoopItem", function() {
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

$(document).on("click", ".upTypeBtn", function() {
    var nIDtxt = $(this).attr("name").replace("n", "").replace("fld", "");
    if (document.getElementById("n"+nIDtxt+"fld"+uploadTypeVid+"") && document.getElementById("n"+nIDtxt+"fld"+uploadTypeVid+"").checked) { // (Video)
        $("#up"+nIDtxt+"FormFile").slideUp("fast");
        $("#up"+nIDtxt+"FormVideo").slideDown("fast");
    }
    else { // not video, but file upload
        $("#up"+nIDtxt+"FormVideo").slideUp("fast");
        $("#up"+nIDtxt+"FormFile").slideDown("fast");
    }
    $("#up"+nIDtxt+"Info").slideDown("fast");
    return true;
});
/* $("[data-toggle=\"tooltip\"]").tooltip(); */

function logCngCnt() {
    if (document.getElementById('chgCntID')) document.getElementById('chgCntID').value++;
    return true;
}
$(document).on("keyup", ".ntrStp", function(e) {
    if (e.keyCode == 13) {
        if (e.preventDefault) e.preventDefault(); 
        else e.returnValue = false; 
        nextTabFld($(this).attr("id"));
        return false; 
    }
});
$(document).on("keydown", ".ntrStp", function(e) {
    if (e.keyCode == 13) { 
        if (e.preventDefault) e.preventDefault(); 
        else e.returnValue = false;
        return false;
    } else { logCngCnt(); }
});
$(document).on("click", ".ntrStp", function(e) { logCngCnt(); });
var lastSlTabIndex = 0;
function nextTabFld(fldID) {
    if (!document.getElementById(fldID)) return false;
    var nIDtxt = "";
    var checkbox = -1;
    if (fldID.indexOf("FldID") > 0 && fldID.indexOf("n") == 0) {
        nIDtxt = fldID.replace("FldID", "").replace("n", "");
    } else if (fldID.indexOf("fld") > 0 && fldID.indexOf("n") == 0) {
        nIDtxt = fldID.replace("n", "").substr(0, (fldID.indexOf("fld")-1));
        checkbox = 0;
    }
    var currIndex = document.getElementById(fldID).tabIndex;
    if (lastSlTabIndex > 0 && currIndex == lastSlTabIndex) currIndex = 0;
    var allTabbables = document.querySelectorAll(".slTab");
    for (var i = 0; i < allTabbables.length; i++) {
        if (allTabbables[i].tabIndex >= (currIndex+1)) {
            if (nIDtxt == "" || chkNodeVisib(nIDtxt)) {
                allTabbables[i].focus();
                break;
            }
        }
    }
    return true;
}

$(document).on("click", ".clkBox", function(e) {
    if ($(this).attr("data-url")) {
        if (e.shiftKey || e.ctrlKey || e.metaKey) window.open($(this).attr("data-url"), "_blank");
        else window.location=$(this).attr("data-url");
    }
    return true;
});

$(document).on("click", ".addSprdTblRow", function() {
    var nID = $(this).attr("data-nid");
    var nIDtxt = $(this).attr("data-nidtxt");
    var dataRowMax = $(this).attr("data-row-max");
    var currMax = 0;
    for (var j = 0; j < dataRowMax; j++) {
        if (document.getElementById('n'+nIDtxt+'tbl'+j+'row') && document.getElementById('n'+nIDtxt+'tbl'+j+'row').style.display == 'table-row' && currMax < j) {
            currMax = j;
        }
    }
    currMax++;
    if (document.getElementById('n'+nIDtxt+'tbl'+currMax+'row')) {
        document.getElementById('n'+nIDtxt+'tbl'+currMax+'row').style.display = 'table-row';
    }
    if (dataRowMax < currMax && document.getElementById('addSprdTbl'+nIDtxt+'Btn')) {
        document.getElementById('addSprdTbl'+nIDtxt+'Btn').style.display = 'none';
    }
});
$(document).on("click", ".delSprdTblRow", function() {
    var nID = $(this).attr("data-nid");
    var nIDtxt = $(this).attr("data-nidtxt");
    var rowind = $(this).attr("data-row-ind");
    if (nodeTblList[nID] && nodeTblList[nID].length > 0) {
        for (var i = 0; i < nodeTblList[nID].length; i++) {
            if (document.getElementById("n"+nodeTblList[nID][i]+"tbl"+rowind+"FldID")) {
                document.getElementById("n"+nodeTblList[nID][i]+"tbl"+rowind+"FldID").value="";
            }
        }
    }
    if (document.getElementById("n"+nIDtxt+"tbl"+rowind+"row")) {
        document.getElementById("n"+nIDtxt+"tbl"+rowind+"row").style.display="none";
    }
    return true;
});

function reqFormTxt(fldID, nIDtxt) {
    if (document.getElementById(fldID) && document.getElementById(fldID).value.trim() == "") {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFld(nIDtxt) {
    var fail = (document.getElementById("n"+nIDtxt+"FldID") && document.getElementById("n"+nIDtxt+"FldID").value.trim() == "");
    if (document.getElementById("n"+nIDtxt+"tagIDsID")) {
        var tags = document.getElementById("n"+nIDtxt+"tagIDsID").value.trim();
        fail = (tags == "" || tags == ",");
    }
    if (fail) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldOther(nIDtxt) {
    if (document.getElementById("n"+nIDtxt+"fldOtherID") && document.getElementById("n"+nIDtxt+"fldOtherID").value.trim() == "") {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldEmail(nIDtxt) {
    if (!reqFormEmail("n"+nIDtxt+"FldID")) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldGreater(nIDtxt, min) {
    if (document.getElementById("n"+nIDtxt+"FldID") && (document.getElementById("n"+nIDtxt+"FldID").value.trim() == "" || Number.parseFloat(document.getElementById("n"+nIDtxt+"FldID").value) < Number.parseFloat(min))) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldLesser(nIDtxt, max) {
    if (document.getElementById("n"+nIDtxt+"FldID") && (document.getElementById("n"+nIDtxt+"FldID").value.trim() == "" || Number.parseFloat(document.getElementById("n"+nIDtxt+"FldID").value) > Number.parseFloat(max))) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldRadio(nIDtxt, maxOpts) {
    var foundCheck = false;
    for (var j=0; j < maxOpts; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"") && document.getElementById("n"+nIDtxt+"fld"+j+"").checked) foundCheck = true;
    }
    if (!foundCheck) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldRadioCustom(nIDtxt, idList) {
    var foundCheck = false;
    for (var j=0; j < idList.length; j++) {
        if (document.getElementById(idList[j]) && document.getElementById(idList[j]).checked) foundCheck = true;
    }
    if (!foundCheck) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldDate(nIDtxt) {
    if (!checkFldDate(nIDtxt)) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldDateAndLimit(nIDtxt, future, today, optional) {
    if (!checkFldDate(nIDtxt) || !chkFormFldDateLimit(nIDtxt, future, today, optional)) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldDateLimit(nIDtxt, future, today, optional) {
    if (!chkFormFldDateLimit(nIDtxt, future, today, optional)) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormFldTbl(nID, nIDtxt, maxRow, cols, colsReq) {
    var foundTblFail = false;
    var nSffx = nIDtxt.replace(nID.toString(), "");
    if (!colsReq) {
        for (var j = 0; j < maxRow; j++) {
            var nFldRow = "n"+nIDtxt+"tbl"+j;
            if (document.getElementById(nFldRow+"row") && document.getElementById(nFldRow+"row").style.display != "none") {
                var foundRowData = false;
                for (var k = 0; k < cols.length; k++) {
                    var currFld = "n"+cols[k][0]+nSffx+"tbl"+j;
                    if (document.getElementById(currFld+"FldID") && document.getElementById(currFld+"FldID").value.trim() != "") {
                        foundRowData = true;
                    } else if (document.getElementById(currFld+"fld0") && document.getElementById(currFld+"fld0").checked) {
                        foundRowData = true;
                    }
                }
                if (!foundRowData) {
                    if (pressedSubmit && firstNodeError == "") {
                        if (document.getElementById("n"+cols[0][0]+nSffx+"tbl"+j+"FldID")) {
                            document.getElementById("n"+cols[0][0]+nSffx+"tbl"+j+"FldID").focus();
                            firstNodeError = nIDtxt;
                        } else if (document.getElementById("n"+cols[0][0]+nSffx+"tbl"+j+"fld0")) {
                            document.getElementById("n"+cols[0][0]+nSffx+"tbl"+j+"fld0").focus();
                            firstNodeError = nIDtxt;
                        }
                    }
                    foundTblFail = true;
                    if (document.getElementById(nFldRow+"row").className.indexOf("sprdRowErr") < 0) {
                        document.getElementById(nFldRow+"row").className += " sprdRowErr";
                    }
                } else {
                    document.getElementById(nFldRow+"row").className = document.getElementById(nFldRow+"row").className.replace("sprdRowErr", "");
                }
            }
        }
    } else {
        
    }
    if (foundTblFail) {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function formDateChange(nIDtxt) {
    document.getElementById("n"+nIDtxt+"FldID").value = document.getElementById("n"+nIDtxt+"fldYearID").value+"-"+document.getElementById("n"+nIDtxt+"fldMonthID").value+"-"+document.getElementById("n"+nIDtxt+"fldDayID").value;
    chkFormCheck();
console.log(document.getElementById("n"+nIDtxt+"FldID").value);
    return true;
}
$(document).on("change", "select.slDateChange", function() {
    return formDateChange( $(this).attr("data-nid-txt") );
});

function dateKeyUp(nIDtxt, which) {
    document.getElementById("n"+nIDtxt+"FldID").value = document.getElementById("n"+nIDtxt+"fldMonthID").value+"/"+document.getElementById("n"+nIDtxt+"fldDayID").value+"/"+document.getElementById("n"+nIDtxt+"fldYearID").value;
    chkFormCheck();
    return true;
}

function formChangeFeetInches(nIDtxt) {
    if (document.getElementById("n"+nIDtxt+"FldID")) document.getElementById("n"+nIDtxt+"FldID").value = (12*parseInt(document.getElementById("n"+nIDtxt+"fldFeetID").value))+parseInt(document.getElementById("n"+nIDtxt+"fldInchID").value);
    chkFormCheck();
    return true;
}

function reqFormFeetInches(nIDtxt) {
    if (document.getElementById("n"+nIDtxt+"fldFeetID").value.trim() == "" || document.getElementById("n"+nIDtxt+"fldInchID").value.trim() == "") {
        setFormLabelRed(nIDtxt);
        totFormErrors++;
    } else {
        setFormLabelBlack(nIDtxt);
    }
    return true;
}

function reqFormGender(nIDtxt) {
    if (document.getElementById("n"+nIDtxt+"fld2") && document.getElementById("n"+nIDtxt+"fld2").value == "?") {
        return reqFormFldRadio(nIDtxt, 4);  // we also have "Not Sure"
    }
    return reqFormFldRadio(nIDtxt, 3);
}

function checkNodeUp(nIDtxt, response) {
    if (nIDtxt != '') {
        nID = txt2nID(nIDtxt);
        checkMutEx(nIDtxt, response);
        if (nID > 0 && nodeMobile[nID]) checkFingerClass(nIDtxt);
        if (chkIsRadioNode(nIDtxt)) runRadioClick(nIDtxt, response);
        chkFormCheck();
    }
    return true;
}

function tryCheckNodeUp(nFldID) {
    if (!checkingForm) {
        checkingForm = true;
        var nodeAndRes = getNodeAndResFromFldID(nFldID);
        checkNodeUp(nodeAndRes[0], nodeAndRes[1]);
        setTimeout(function() { checkingForm = false; }, 400);
    }
    return true;
}

$(".slNodeChange").keyup(function() { return tryCheckNodeUp($(this).attr("id")); });
$(".slNodeChange").click(function() { return tryCheckNodeUp($(this).attr("id")); });
$("input.slNodeChange").click(function() { return tryCheckNodeUp($(this).attr("id")); });

$(document).on("keyup", "input.slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });
$(document).on("keyup", "textarea.slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });
$(document).on("change", "select.slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });
$(document).on("click", "input.slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });

$(document).on("keyup", ".slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });
$(document).on("change", ".slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });
$(document).on("click", ".slNodeChange", function() { return tryCheckNodeUp($(this).attr("id")); });


function formKeyUpOther(nIDtxt, j) {
    if (document.getElementById("n"+nIDtxt+"fldOtherID"+j+"") && document.getElementById("n"+nIDtxt+"fldOtherID"+j+"").value.trim() != "") {
        document.getElementById("n"+nIDtxt+"fld"+j+"").checked=true;
        checkFingerClass(nIDtxt);
    }
    chkFormCheck();
    return true;
}
$(document).on("keyup", "input.slNodeKeyUpOther", function() {
    if ($(this).attr("data-nid") && $(this).attr("data-j")) {
        var nIDtxt = $(this).attr("data-nid");
        var j = $(this).attr("data-j");
        return formKeyUpOther(nIDtxt, j);
    }
    return false;
});

function formClickGender(nIDtxt) {
    if (document.getElementById("n"+nIDtxt+"fldOtherID")) {
        if ((document.getElementById("n"+nIDtxt+"fld0") && document.getElementById("n"+nIDtxt+"fld0").checked)
            || (document.getElementById("n"+nIDtxt+"fld1") && document.getElementById("n"+nIDtxt+"fld1").checked)
            || (document.getElementById("n"+nIDtxt+"fld3") && document.getElementById("n"+nIDtxt+"fld3").checked)) {
            document.getElementById("n"+nIDtxt+"fldOtherID").value = "";
        }
    }
    chkFormCheck();
    return true;
}
$(document).on("click", "input.slNodeClkGender", function() {
    if ($(this).attr("data-nid")) {
        var nID = $(this).attr("data-nid");
        return formClickGender(nID);
    }
    return false;
});


function checkNodeFormSignup() {
@if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
    var emailRequired = false;
@else var emailRequired = true; @endif
    hasAttemptedSubmit = true;
    totFormErrors = 0;
    formErrorsEng = "";
@if (isset($GLOBALS["SL"]->sysOpts["user-name-req"]) && intVal($GLOBALS["SL"]->sysOpts["user-name-req"]) == 1)
    if (document.getElementById('nameID').value.trim() == '') {
        setFormLabelRed('004');
        totFormErrors++;
    } else {
        setFormLabelBlack('004');
    }
@endif
    if (emailRequired && (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')) {
        setFormLabelRed('001'); 
        totFormErrors++;
    } else if (reqFormEmail('emailID') && document.getElementById('emailID').value.trim() != '') {
        document.getElementById('emailWarning').style.display='none';
        $.ajax({
            url: "/chkEmail?"+$("#emailID").serialize(),
            type: 'GET',
            async: false,
            cache: false,
            timeout: 30000,
            error: function(){
                return true;
            },
            success: function(chkData){ 
                if (chkData == 'found') {
                    document.getElementById('emailBlockID').value = 1;
                    setFormLabelRed('001'); 
                    totFormErrors++;
                    //document.getElementById('emailWarning').style.display='block';
                    $("#emailWarning").slideDown("fast");
                } else {
                    document.getElementById('emailBlockID').value = 0;
                    setFormLabelBlack('001');
                }
            }
        });
    }
    if (document.getElementById('password') && document.getElementById('password_confirmation')) {
        var pass1 = document.getElementById('password').value;
        if (pass1 == '' || pass1.length < 8 || pass1 != document.getElementById('password_confirmation').value) {
            setFormLabelRed('002');
            setFormLabelRed('003');
            totFormErrors++;
        } else {
            setFormLabelBlack('002');
            setFormLabelBlack('003');
        }
    }
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    if (!emailRequired && (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')) {
        document.getElementById('emailID').value = 'no.email.'+document.getElementById('nameID').value+'@noemail.org';
    }
    clearFormErrs();
    firstNodeError = "";
    if (document.getElementById('nameID').value.trim() == '') {
        document.getElementById('nameID').value=document.getElementById('emailID').value;
    }
    return true;
}

$(document).on("click", ".nFormSignupSubBtn", function() {
    pressedSubmit = true;
    return checkNodeFormSignup();
});

