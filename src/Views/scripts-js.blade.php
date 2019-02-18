/* generated from resources/views/vendor/survloop/scripts-js.blade.php */

function debugTxt(txt) {
    if (document.getElementById("absDebug")) {
        document.getElementById("absDebug").innerHTML = txt+"<br />"+document.getElementById("absDebug").innerHTML;
    }
    return true;
}

var appUrl = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}";
var defMetaImg = "{{ ((isset($GLOBALS['SL']->sysOpts['meta-img'])) ? $GLOBALS['SL']->sysOpts['meta-img'] : '') }}";

var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);

function findGetParam(paramName) {
    var result = null,
    tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === paramName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}

var treeList = new Array();
@forelse ($GLOBALS["SL"]->getTreeList() as $i => $t)
    treeList[{{ $i }}] = [ {{ $t[0] }}, "{{ str_replace('"', '\\"', $t[1]) }}", "{{ $t[2] }}", "{{ $t[3] }}" ];
@empty
@endforelse
function getTreeCoreTbl(treeID) {
    if (treeID > 0) {
        for (var i = 0; i < treeList.length; i++) {
            if (treeList[i][0] == treeID) return treeList[i][3];
        }
    }
    return "";
}

function slugOnBlur(obj, dest) {
    if (obj.value.trim() != '' && document.getElementById(dest) && document.getElementById(dest).value.trim() == '') {
        document.getElementById(dest).value = slugify(obj.value);
    }
    return true;
}
function slugify(string) {
  return string.toString().trim().toLowerCase().replace(/\s+/g, "-").replace(/[^\w\-]+/g, "").replace(/\-\-+/g, "-")
    .replace(/^-+/, "").replace(/-+$/, "");
}

var currPage = new Array();
function setCurrPage(title, url) {
    currPage[0] = title;
    currPage[1] = url;
    return true;
}

var allFldList = new Array();
function addFld(fld) {
	allFldList[allFldList.length] = fld;
	return true;
}
function blurAllFlds() {
	for (var i=0; i < allFldList.length; i++) {
		if (document.getElementById(allFldList[i])) document.getElementById(allFldList[i]).blur();
	}
	return true;
}

function copyClip(divID) {
    if (document.getElementById(divID)) {
        document.getElementById(divID).select();
        document.execCommand("Copy");
    }
    return true;
}

var foundForm = true;
function checkForm() {
	if (!foundForm) {
		if (document.getElementById("postNodeForm")) foundForm = true;
		else setTimeout("checkForm()", 10000);
	}
	return true;
}
function resetCheckForm() {
	foundForm = false;
	setTimeout("checkForm()", 10000);
	return true;
}

var loopItemsNextID = 0;
var currItemCnt = 0;
var maxItemCnt = 0;

var pressedSubmit = false;
var hasAttemptedSubmit = false;
var totFormErrors = 0;
var formErrorsEng = "";
var otherFormSub = false;

function chkFormCheck() {
    if (hasAttemptedSubmit) return checkNodeForm();
    return false;
}

function setFormErrs() {
	if (document.getElementById("formErrorMsg")) {
	    document.getElementById("formErrorMsg").innerHTML = "<h2>Please complete all required fields. "+formErrorsEng+"</h2>";
	    document.getElementById("formErrorMsg").style.display = "block";
	}
	return true;
}
function clearFormErrs() {
	if (document.getElementById("formErrorMsg")) {
	    document.getElementById("formErrorMsg").innerHTML = "";
	    document.getElementById("formErrorMsg").style.display = "none";
	}
	return true;
}

function txt2nID(nIDtxt) {
    if (nIDtxt.indexOf('cyc') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('cyc'))));
    if (nIDtxt.indexOf('res') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('res'))));
    if (nIDtxt.indexOf('tbl') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('tbl'))));
    return parseInt(nIDtxt);
}

function setFormLabelBlack(nIDtxt) {
	if (document.getElementById("node"+nIDtxt+"")) {
	    document.getElementById("node"+nIDtxt+"").className=document.getElementById("node"+nIDtxt+"").className.replace("nodeWrapError", "nodeWrap");
	}
	return true;
}

var errorFocus = new Array();
var firstNodeError = "";
function setFormLabelRed(nIDtxt) {
    if (pressedSubmit && firstNodeError == "") {
        var foundFocus = "";
        for (var j = 0; j < errorFocus.length; j++) {
            if (foundFocus == "" && errorFocus[j][0] == nIDtxt && errorFocus[j][1].trim() != "" && document.getElementById(errorFocus[j][1])) {
                foundFocus = errorFocus[j][1];
            }
        }
        /* if (foundFocus != "") {
            document.getElementById(foundFocus).focus();
        } else if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").focus();
        } else if (document.getElementById("n"+nIDtxt+"fld0")) {
            document.getElementById("n"+nIDtxt+"fld0").focus();
        } */
        scrollTo(document.getElementById("#n"+nIDtxt+""));
        /* window.location="#n"+nIDtxt+""; */
    }
	if (document.getElementById("node"+nIDtxt+"")) {
	    document.getElementById("node"+nIDtxt+"").className=document.getElementById("node"+nIDtxt+"").className.replace("nodeWrap", "nodeWrapError").replace("nodeWrapErrorError", "nodeWrapError");
    }
	return true;
}

function reqFormEmail(FldName) {
	if (document.getElementById(FldName)) {
		if (document.getElementById(FldName).value.trim() == "") return false;
		var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (!re.test(document.getElementById(FldName).value)) return false;
	}
	return true;
}

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

var radioNodes = new Array();
function addRadioNode(nID) {
    radioNodes[radioNodes.length] = nID;
    return true;
}
function chkIsRadioNode(nIDtxt) {
    var nID = txt2nID(nIDtxt);
    for (var i=0; i < radioNodes.length; i++) {
        if (radioNodes[i] == nID) return true;
    }
    return false;
}
function runRadioClick(nIDtxt, response) {
    if (document.getElementById("n"+nIDtxt+"fld"+response+"") && document.getElementById("n"+nIDtxt+"radioCurrID")) {
        if (document.getElementById("n"+nIDtxt+"fld"+response+"").value != document.getElementById("n"+nIDtxt+"radioCurrID").value) {
            document.getElementById("n"+nIDtxt+"radioCurrID").value = document.getElementById("n"+nIDtxt+"fld"+response+"").value;
        } else {
            document.getElementById("n"+nIDtxt+"fld"+response+"").checked = false;
            document.getElementById("n"+nIDtxt+"radioCurrID").value = "";
            checkFingerClass(nIDtxt);
            chkFormCheck();
        }
    }
    return true;
}


function checkFldDate(nIDtxt) {
    return (document.getElementById("n"+nIDtxt+"fldYearID").value != "0000" 
	    && document.getElementById("n"+nIDtxt+"fldMonthID").value != "00" 
	    && document.getElementById("n"+nIDtxt+"fldDayID").value != "00");
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

function chkFormFldDateLimit(nIDtxt, future, today, optional) {
    if (future == 0) return true;
    validDate = true;
    if (optional == 1 && !checkFldDate(nIDtxt)) {
    } else if (checkFldDate(nIDtxt)) {
        var todayYear = parseInt(today.substring(0, 4));
        var todayMonth = parseInt(today.substring(5, 7));
        var todayDay = parseInt(today.substring(8, 10));
        var userYear = parseInt(document.getElementById("n"+nIDtxt+"fldYearID").value);
        var userMonth = parseInt(document.getElementById("n"+nIDtxt+"fldMonthID").value);
        var userDay = parseInt(document.getElementById("n"+nIDtxt+"fldDayID").value);
        if (future < 0) { // the past is valid
            if (userYear > todayYear) {
                validDate = false;
            } else if (userYear == todayYear) {
                if (userMonth > todayMonth) {
                    validDate = false;
                } else if (userMonth == todayMonth) {
                    if (userDay > todayDay) validDate = false;
                }
            }
        } else { // the future is valid
            if (userYear < todayYear) {
                validDate = false;
            } else if (userYear == todayYear) {
                if (userMonth < todayMonth) {
                    validDate = false;
                } else if (userMonth == todayMonth) {
                    if (userDay < todayDay) validDate = false;
                }
            }
        }
    } else {
        validDate = false;
    }
    return validDate;
}

function charLimit(nIDtxt, limit) {
    if (document.getElementById("n"+nIDtxt+"FldID")) {
        if (document.getElementById("n"+nIDtxt+"FldID").value.length > limit) {
            document.getElementById("n"+nIDtxt+"FldID").value = document.getElementById("n"+nIDtxt+"FldID").value.substring(0, limit);
        }
        var charRemain = limit-document.getElementById("n"+nIDtxt+"FldID").value.length;
        document.getElementById("charLimit"+nIDtxt+"Msg").innerHTML = limit+" Character Limit: "+charRemain+" Remaining";
    }
	return true;
}

function formDateChange(nIDtxt) {
	document.getElementById("n"+nIDtxt+"FldID").value = document.getElementById("n"+nIDtxt+"fldYearID").value+"-"+document.getElementById("n"+nIDtxt+"fldMonthID").value+"-"+document.getElementById("n"+nIDtxt+"fldDayID").value;
	chkFormCheck();
	return true;
}

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
function formRequireFeetInches(nIDtxt) {
	if (document.getElementById("n"+nIDtxt+"fldFeetID").value.trim() == "" || document.getElementById("n"+nIDtxt+"fldInchID").value.trim() == "") {
		setFormLabelRed(nIDtxt);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nIDtxt);
	}
	return true;
}

function formRequireGender(nIDtxt) {
	if (document.getElementById("n"+nIDtxt+"fld2") && document.getElementById("n"+nIDtxt+"fld2").value == "?") {
		return reqFormFldRadio(nIDtxt, 4);  // we also have "Not Sure"
	}
	return reqFormFldRadio(nIDtxt, 3);
}

var graphs = new Array();
function addGraph(nIDtxt, baseurl) {
    graphs[graphs.length] = [ txt2nID(nIDtxt), nIDtxt, baseurl ];
    return true;
}
var graphFlds = new Array();
function addGraphFld(nIDtxt, fld, parent) {
    graphFlds[graphFlds.length] = [ txt2nID(nIDtxt), nIDtxt, parent, fld, "" ];
    return true;
}

function checkNodeUp(nIDtxt, response, isMobile) {
    checkMutEx(nIDtxt, response);
    if (isMobile == 1) checkFingerClass(nIDtxt);
    if (chkIsRadioNode(nIDtxt)) setTimeout("runRadioClick('"+nIDtxt+"', '"+response+"')", 10);
	chkFormCheck();
    return true;
}

function formKeyUpOther(nIDtxt, j) {
    if (document.getElementById("n"+nIDtxt+"fldOtherID"+j+"") && document.getElementById("n"+nIDtxt+"fldOtherID"+j+"").value.trim() != "") {
        document.getElementById("n"+nIDtxt+"fld"+j+"").checked=true;
        checkFingerClass(nIDtxt);
    }
	chkFormCheck();
    return true;
}

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

var gryFlds = new Array();
function addGryFld(fldID, defaultTxt) {
    
}
function fldGryTxtKeyUp(fldID, defaultTxt) {
    if (document.getElementById(fldID)) {
        if (document.getElementById(fldID).value.trim() == '') {
            document.getElementById(fldID).value = defaultTxt;
            document.getElementById(fldID).className = "form-control slGrey";
        } else if (document.getElementById(fldID).value == defaultTxt 
            || document.getElementById(fldID).value.replace("'", "").replace('"', '') == defaultTxt) {
            document.getElementById(fldID).className = "form-control slGrey";
        } else {
            document.getElementById(fldID).className = "form-control";
        }
    }
    return true;
}
function fldGryTxtFocus(fldID, defaultTxt) {
    if (document.getElementById(fldID)) {
        if (document.getElementById(fldID).value.trim() == '') {
            document.getElementById(fldID).value = defaultTxt;
            document.getElementById(fldID).className = "form-control slGrey";
        } else if (document.getElementById(fldID).value == defaultTxt 
            || document.getElementById(fldID).value.replace("'", "").replace('"', '') == defaultTxt) {
            document.getElementById(fldID).className = "form-control slGrey";
        } else {
            document.getElementById(fldID).className = "form-control";
        }
    }
    return true;
}

function focusNodeID(nIDtxt) {
    var fldID = "n"+nIDtxt+"FldID";
    if (!document.getElementById(fldID)) {
        fldID = "n"+nIDtxt+"fld0";
        if (!document.getElementById(fldID)) {
            fldID = "n"+nIDtxt+"fldMonthID";
            if (!document.getElementById(fldID)) {
                fldID = "n"+nIDtxt+"fldHrID";
                if (!document.getElementById(fldID)) {
                    fldID = "n"+nIDtxt+"fldFeetID";
                    if (!document.getElementById(fldID)) {
                        fldID = "";
                    }
                }
            }
        }
    }
    if (fldID != "" && document.getElementById(fldID)) document.getElementById(fldID).focus();
    return true;
}

function charCountKeyUp(nIDtxt) {
	if (document.getElementById("n"+nIDtxt+"FldID")) {
	    var count = 0;
	    if (document.getElementById("n"+nIDtxt+"FldID").value.trim() != "") {
	        count = document.getElementById("n"+nIDtxt+"FldID").value.trim().length;
        }
	    if (document.getElementById("wordCnt"+nIDtxt+"")) {
            document.getElementById("wordCnt"+nIDtxt+"").innerHTML=count;
        }
	}
	return true;
}
function keywordCountKeyUp(nIDtxt) {
	if (document.getElementById("n"+nIDtxt+"FldID")) {
	    var keywords = new Array();
	    if (document.getElementById("n"+nIDtxt+"FldID").value.trim() != "") {
	        keywords = document.getElementById("n"+nIDtxt+"FldID").value.trim().split(",");
        }
	    if (document.getElementById("keywordCnt"+nIDtxt+"")) {
            document.getElementById("keywordCnt"+nIDtxt+"").innerHTML=keywords.length;
        }
	}
	return true;
}

function wordCountKeyUp(nIDtxt, limit, warn) {
	if (document.getElementById("n"+nIDtxt+"FldID")) {
	    var words = new Array();
	    if (document.getElementById("n"+nIDtxt+"FldID").value.trim() != "") {
	        words = document.getElementById("n"+nIDtxt+"FldID").value.trim().split(" ");
        }
	    if (limit > 0 && limit < 10000000000 && words.length > limit) {
	        var newLimited = "";
	        for (var i = 0; i < limit; i++) {
	            newLimited += " " + words[i];
	        }
	        document.getElementById("n"+nIDtxt+"FldID").value = newLimited.trim();
	    }
	    if (document.getElementById("wordCnt"+nIDtxt+"")) {
            var cntWords = "<span class=\"slBlueDark\">"+words.length+"</span>";
            if (0 < warn && warn <= words.length) cntWords = "<span class=\"slRedLight\">"+words.length+"</span>";
            document.getElementById("wordCnt"+nIDtxt+"").innerHTML=cntWords;
        }
	}
	return true;
}

function checkMin(nIDtxt, minVal) {
	if (document.getElementById("n"+nIDtxt+"FldID")) {
	    var currVal = document.getElementById("n"+nIDtxt+"FldID");
	    if (currVal.value.trim() != '' && currVal.value < minVal) currVal.value = minVal;
	}
    return true;
}
function checkMax(nIDtxt, maxVal) {
	if (document.getElementById("n"+nIDtxt+"FldID")) {
	    var currVal = document.getElementById("n"+nIDtxt+"FldID");
	    if (currVal.value.trim() != '' && currVal.value > maxVal) currVal.value = maxVal;
	}
    return true;
}

var nodeResTot = new Array();
function addResTot(nID, tot) {
    nodeResTot[nID] = tot;
    return true;
}

var nodeMutEx = new Array();
function addMutEx(nID, response) {
    if (!nodeMutEx[nID]) nodeMutEx[nID] = new Array();
    nodeMutEx[nID][nodeMutEx[nID].length] = response;
    return true;
}

function checkFingerClass(nIDtxt) {
    for (var j = 0; j < nodeResTot[nIDtxt]; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"lab") && document.getElementById("n"+nIDtxt+"fld"+j+"")) {
            if (document.getElementById("n"+nIDtxt+"fld"+j+"").checked) {
                document.getElementById("n"+nIDtxt+"fld"+j+"lab").className = "fingerAct";
            } else {
                document.getElementById("n"+nIDtxt+"fld"+j+"lab").className = "finger";
            }
        }
    }
    return true;
}

function checkMutEx(nIDtxt, response) {
    var nID = txt2nID(nIDtxt);
    if (nID > 0 && response > 0 && nodeMutEx[nID] && nodeMutEx[nID].length > 0) {
        var hasMutEx = false;
        var clickedMutEx = false;
        for (var i = 0; i < nodeMutEx[nID].length; i++) {
            if (nodeMutEx[nID][i] == response) {
                if (document.getElementById("n"+nIDtxt+"fld"+response+"").checked) {
                    clickedMutEx = true;
                    for (var j=0; j < nodeResTot[nID]; j++) {
                        if (j != response) {
                            document.getElementById("n"+nIDtxt+"fld"+j+"").checked = false;
                        }
                    }
                }
            }
        }
        if (!clickedMutEx) {
            for (var i=0; i < nodeMutEx[nID].length; i++) {
                if (nodeMutEx[nID][i] != response && document.getElementById("n"+nIDtxt+"fld"+response+"").checked) {
                    document.getElementById("n"+nIDtxt+"fld"+nodeMutEx[nID][i]+"").checked = false;
                }
            }
        }
    }
    return true;
}

function chkRadioHide(nIDtxt) {
    setTimeout("chkRadioHideRun('"+nIDtxt+"')", 100);
    return true;
}
function chkRadioHideRun(nIDtxt) {
    var nID = txt2nID(nIDtxt);
    var foundCheck = false;
    for (var j = 0; j < nodeResTot[nID]; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"")) {
            if (document.getElementById("n"+nIDtxt+"fld"+j+"").checked) foundCheck = true;
        }
    }
    for (var j = 0; j < nodeResTot[nID]; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"lab") && document.getElementById("n"+nIDtxt+"fld"+j+"")) {
            if (!foundCheck || document.getElementById("n"+nIDtxt+"fld"+j+"").checked) {
                document.getElementById("n"+nIDtxt+"fld"+j+"lab").style.display = "block";
            } else {
                document.getElementById("n"+nIDtxt+"fld"+j+"lab").style.display = "none";
            }
        }
    }
    if (document.getElementById("radioUnHide"+nIDtxt+"")) {
        if (foundCheck) document.getElementById("radioUnHide"+nIDtxt+"").style.display="block";
        else document.getElementById("radioUnHide"+nIDtxt+"").style.display="none";
    }
    return true;
}
function radioUnHide(nIDtxt) {
    var nID = txt2nID(nIDtxt);
    if (document.getElementById("radioUnHide"+nIDtxt+"")) {
        document.getElementById("radioUnHide"+nIDtxt+"").style.display="none";
    }
    for (var j = 0; j < nodeResTot[nID]; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"lab")) {
            document.getElementById("n"+nIDtxt+"fld"+j+"lab").style.display = "block";
        }
    }
    return true;
}

function copyNodeResponse(fldID, dest) {
    if (document.getElementById(fldID) && document.getElementById(dest)) {
        document.getElementById(dest).innerHTML=document.getElementById(fldID).value;
        return true;
    }
    return false;
}

var nodeTags = new Array();
var nodeTagList = new Array();
function getTagNodeInd(nIDtxt) {
    for (var i = 0; i < nodeTags.length; i++) {
        if (nodeTags[i][0] == nIDtxt) return i;
    }
    return -1;
}
function addTagOpt(nIDtxt, tagID, tagText, preSel) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd < 0) {
        nodeInd = nodeTags.length;
        nodeTags[nodeInd] = new Array(nIDtxt, new Array());
        nodeTagList[nodeInd] = new Array();
    }
    if (!nodeTags[nodeInd][1][tagID]) nodeTags[nodeInd][1][tagID] = new Array(tagText, preSel);
    nodeTagList[nodeInd][nodeTagList[nodeInd].length] = tagID;
    return true;
}
function selectTag(nIDtxt, tagID) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd >= 0 && nodeTags[nodeInd] && nodeTags[nodeInd][1][tagID]) nodeTags[nodeInd][1][tagID][1] = 1;
    updateTagList(nIDtxt);
    return true;
}
function deselectTag(nIDtxt, tagID) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd >= 0 && nodeTags[nodeInd] && nodeTags[nodeInd][1][tagID]) nodeTags[nodeInd][1][tagID][1] = 0;
    updateTagList(nIDtxt);
    return false;
}
function printTag(nIDtxt, tagID, tagText) {
    return "<a onClick=\"return deselectTag('"+nIDtxt+"', "+tagID+");\" class=\"btn btn-primary\" href=\"javascript:;\">"+tagText+"<i class=\"fa fa-times\" aria-hidden=\"true\"></i></a> ";
}
function updateTagList(nIDtxt) {
    var tagIDs = ",";
    var tagHtml = "";
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeTags[nodeInd] && nodeTagList[nodeInd]) {
        for (var i = 0; i < nodeTagList[nodeInd].length; i++) {
            var tagID = nodeTagList[nodeInd][i];
            if (nodeTags[nodeInd][1][tagID] && nodeTags[nodeInd][1][tagID][1] == 1 && tagIDs.indexOf(","+tagID+",") < 0) {
                tagIDs += tagID+",";
                tagHtml += printTag(nIDtxt, tagID, nodeTags[nodeInd][1][tagID][0]);
            }
        }
    }
    if (document.getElementById("n"+nIDtxt+"tagIDsID")) document.getElementById("n"+nIDtxt+"tagIDsID").value=tagIDs;
    if (document.getElementById("n"+nIDtxt+"tags")) document.getElementById("n"+nIDtxt+"tags").innerHTML=tagHtml;
    return true;
}

// used by form generator child reveal responsiveness:
var nodeList = new Array();
var nodeParents = new Array();
var nodeKidList = new Array();
var nodeTblList = new Array();
var nodeSffxs = new Array("");
var conditionNodes = new Array();
function styBlock(id) {
    if (document.getElementById(id)) document.getElementById(id).style.display="block";
}
function styNone(id) {
    if (document.getElementById(id)) document.getElementById(id).style.display="none";
}
function kidsVisible(nID, nSffx, onOff) {
    setNodeVisib(nID, nSffx, onOff);
	if (nodeKidList[nID] && nodeKidList[nID].length > 0) {
		for (var k = 0; k < nodeKidList[nID].length; k++) {
			kidsVisible(nodeKidList[nID][k], nSffx, onOff);
		}
	}
	return true;
}
function setNodeVisib(nID, nSffx, onOff) {
	if (document.getElementById("n"+nID+nSffx+"VisibleID")) {
		if (onOff) document.getElementById("n"+nID+nSffx+"VisibleID").value=1;
		else document.getElementById("n"+nID+nSffx+"VisibleID").value=0;
	}
	return true;
}
function chkNodeVisib(nIDtxt) {
    return (document.getElementById("n"+nIDtxt+"VisibleID") && document.getElementById("n"+nIDtxt+"VisibleID").value == 1);
}
function chkNodeParentVisib(nID) {
    /* for (var s = 0; s < nodeSffxs.length; s++) {
        if (nodeParents[nID] && document.getElementById("n"+nID+nodeSffxs[s]+"VisibleID") && document.getElementById("n"+nodeParents[nID]+nodeSffxs[s]+"VisibleID")) {
            if (onOff) document.getElementById("n"+nID+nodeSffxs[s]+"VisibleID").value=1;
            else document.getElementById("n"+nID+nodeSffxs[s]+"VisibleID").value=0;
        }
    } */
	return true;
}
function setSubResponses(nID, nSffx, onOff, kids) {
    if (kids.length > 0) {
        for (var k = 0; k < kids.length; k++) {
            if (document.getElementById("node"+kids[k]+nSffx+"")) {
                if (onOff) {
//alert("node kids[k] = "+kids[k]+" , nSffx = "+nSffx+"");
//will need styFlex(
                    styBlock("node"+kids[k]+nSffx+"");
                    
                } else {
                    styNone("node"+kids[k]+nSffx+"");
                }
            }
            kidsVisible(kids[k], nSffx, onOff);
        }
    }
    return true;
}

function kidsDisplaySkip(nID, nSffx, onOff) {
	if (nodeKidList[nID] && nodeKidList[nID].length > 0) {
		for (var k = 0; k < nodeKidList[nID].length; k++) {
			kidsDisplay(nodeKidList[nID][k], nSffx, onOff);
		}
	}
	return true;
}
function kidsDisplay(nID, nSffx, onOff) {
    setNodeDisp(nID, nSffx, onOff);
	if (nodeKidList[nID] && nodeKidList[nID].length > 0) {
		for (var k = 0; k < nodeKidList[nID].length; k++) {
			kidsDisplay(nodeKidList[nID][k], nSffx, onOff);
		}
	}
	return true;
}
function setNodeDisp(nID, nSffx, onOff) {
	if (document.getElementById("node"+nID+nSffx+"")) {
		if (onOff) styBlock("node"+nID+nSffx+"");
		else styNone("node"+nID+nSffx+"");
	}
	return true;
}

function ajaxSearchExpandResults() {
	if (document.getElementById("ajaxSearchResults").className=="ajaxSearch") document.getElementById("ajaxSearchResults").className="ajaxSearchExpand";
	else document.getElementById("ajaxSearchResults").className="ajaxSearch";
	return true;
}

function reqUploadTitle(nID) {
	var labelID = parseInt("100"+nID+"");
	/* if ((document.getElementById("up"+nID+"FileID").value != "" || document.getElementById("up"+nID+"VidID") != "")
		&& document.getElementById("up"+nID+"TitleID").value.trim() == "") {
		setFormLabelRed(labelID);
		totFormErrors++;
	}
	else setFormLabelBlack(labelID); */
	return true;
}

function checkAjaxLoad() {
    if (document.getElementById("ajaxWrapLoad")) {
        if (document.getElementById("ajaxWrapLoad").style.display != "none") {
            if (document.getElementById("postNodeForm") || document.getElementById("footerLinks")) {
                document.getElementById("ajaxWrapLoad").style.display = "none";
            }
            setTimeout("checkAjaxLoad()", 50);
        }
    }
    return true;
}
setTimeout("checkAjaxLoad()", 100);

function runSearch(nID, treeID) {
    var sURL = "/search?t="+treeID;
    @if (isset($jqueryXtraSrch)) {!! $jqueryXtraSrch !!} @endif
    if (document.getElementById("searchBar"+nID+"t"+treeID)) {
        sURL += "&s="+encodeURIComponent(document.getElementById("searchBar"+nID+"t"+treeID).value);
    }
    if (document.getElementById("advUrlID")) {
        sURL += document.getElementById("advUrlID").value;
    }
    window.location.replace(sURL);
    return false;
}

function getSpinner() {
    return {!! json_encode($GLOBALS["SL"]->spinner()) !!};
}
function getSpinnerAjaxWrap() {
    return {!! json_encode('<div id="ajaxWrapLoad" class="container">') !!}+getSpinner()+{!! json_encode('</div>') !!};
}
function replaceAjaxWithSpinner() {
    if (document.getElementById("ajaxWrap")) document.getElementById("ajaxWrap").innerHTML=getSpinnerAjaxWrap();
    return true;
}

function changeLoopListType(fld) {
    if (document.getElementById(''+fld+'TypeID')) {
        if (document.getElementById(''+fld+'TypeID').value == 'manual') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
            document.getElementById(''+fld+'DefinitionID').value='';
            document.getElementById(''+fld+'LoopItemsID').value='';
            document.getElementById(''+fld+'TablesID').value='';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-def') {
            document.getElementById(''+fld+'Defs').style.display = 'block';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-loop') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'block';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-tbl') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'block';
        }
    }
    return true;
}

var uploadTypeVid = -1;

function flexAreaAdjust(o) {
    o.style.height = "1px";
    var newH = o.scrollHeight+25;
    o.style.height = (newH)+"px";
}

var slideGals = new Array(); // [3] current slide, [4] number of change requests
function initGalSlider(nIDtxt, kids, style) {
    var found = false;
    for (var i = 0; i < slideGals.length; i++) {
        if (slideGals[i][0] == nIDtxt) found = true;
    }
    if (!found) {
        slideGals[slideGals.length] = new Array(nIDtxt, kids.split(","), JSON.parse(style), 0, 0);
    }
    return true;
}

var hshoos = new Array();
var hshooCurr = 0;
var anchorOffsetBonus = 0;
function addHshoo(hash) {
    hshoos[hshoos.length] = new Array(hash, 0);
    return true;
}
function getHshooInd(currHash) {
    for (var i = 0; i < hshoos.length; i++) {
        if (hshoos[i][0] == currHash) return i;
    }
    return -1;
}
function hasHshoos() {
    return (hshoos.length > 0);
}
function chkHshooTopTabs() {
    if (hasHshoos() && document.getElementById('slTopTabsWrap') && document.getElementById('adminMenuTopTabs')) {
        document.getElementById('slTopTabsWrap').style.position='fixed';
        document.getElementById('adminMenuTopTabs').style.marginBottom='70px';
    } else {
        setTimeout("chkHshooTopTabs()", 2000);
    }
    return true;
}
setTimeout("chkHshooTopTabs()", 200);

function checkBoxAll(fldBase, count, isChecked) {
    for (var i = 0; i < count; i++) {
        var fldID = fldBase.concat(i);
        if (document.getElementById(fldID)) document.getElementById(fldID).checked = isChecked;
    }
    return true;
}

function isInViewport(node) {
    var rect = node.getBoundingClientRect();
    return (
        (rect.height > 0 || rect.width > 0) && rect.bottom >= 0 && rect.right >= 0 &&
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.left <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

function setPopDiaTxt(title, desc) {
    return true;
}

var formActionUrl = "/sub";

var cntDownOver = false;
function updateCountdown(divID, cntFrom, inc) {
    if (document.getElementById(divID)) {
        var newCnt = cntFrom-inc;
        if (newCnt >= 0) {
            document.getElementById(divID).innerHTML=newCnt;
            setTimeout("updateCountdown('"+divID+"', "+newCnt+", "+inc+")", (inc*1000));
        }
        if (newCnt <= 0) cntDownOver = true;
    }
    return true;
}
function startCountdown(divID, cntFrom, inc) {
    cntDownOver = false;
    if (document.getElementById(divID)) {
        setTimeout("updateCountdown('"+divID+"', "+cntFrom+", "+inc+")", (inc*1000));
    }
    return true;
}

function openNav() {
    document.getElementById("mySidenav").style.borderLeft = "1px {!! $css["color-main-off"] !!} solid";
    document.getElementById("mySidenav").style.boxShadow = "0px 0px 40px {!! $css["color-main-faint"] !!}";
    document.getElementById("mySidenav").style.width = "300px";
    document.getElementById("main").style.marginRight = "300px";
    document.getElementById("navBurger").style.display = "none";
    document.getElementById("navBurgerClose").style.display = "block";
}
function closeNav() {
    document.getElementById("mySidenav").style.borderLeft = "0px none";
    document.getElementById("mySidenav").style.boxShadow = "none";
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("main").style.marginRight = "0";
    document.getElementById("navBurger").style.display = "block";
    document.getElementById("navBurgerClose").style.display = "none";
}
function toggleNav() {
    if (document.getElementById("mySidenav").style.width == "300px") return closeNav();
    return openNav();
}

var currTree = 0;
var progressPerc = 0;
var treeMajorSects = new Array();
var treeMinorSects = new Array();
var treeMajorSectsDisabled = new Array();
var treeProTips = new Array();
var lastProTip = 0;

function getNextProTipText() {
    lastProTip++;
    if (treeProTips.length <= lastProTip) {
        lastProTip = 0;
    }
    return treeProTips[lastProTip];
}
function addProTipToAjax() {
    if (treeProTips.length > 0) {
        if (document.getElementById("ajaxWrapLoad")) {
            document.getElementById("ajaxWrapLoad").innerHTML += '<center><h3 class="slBlueDark pL15 pR15">'+getNextProTipText()+'</h3></center>';
            logLastProTip();
        }
    }
    return true;
}
function logLastProTip() {
    if (document.getElementById('hidFrameID')) {
        document.getElementById('hidFrameID').src='/ajax/log-pro-tip?tree='+currTree+'&tip='+lastProTip+'';
    }
    return true;
}

function addTopCust(navCode) {
    if (document.getElementById("myNavBar")) {
        if (document.getElementById("myNavBar").innerHTML.indexOf(navCode) < 0) {
            document.getElementById("myNavBar").innerHTML += navCode;
        }
    }
    return true;
}
function addTopNavItem(navTxt, navLink) {
    if (document.getElementById("myNavBar")) {
        if (navTxt == 'pencil') navTxt = "<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>";
        var newLink = "<a class=\"float-right slNavLnk\" href=\""+navLink+"\">"+navTxt+"</a>";
        if (document.getElementById("myNavBar").innerHTML.indexOf(newLink) < 0) {
            if (navTxt == 'pencil') document.getElementById("myNavBar").innerHTML = newLink+document.getElementById("myNavBar").innerHTML;
            else document.getElementById("myNavBar").innerHTML += newLink;
        }
    }
    return true;
}
function addSideNavItem(navTxt, navLink) {
    if (document.getElementById("mySideUL")) {
        var newLink = "<li class=\"nav-item\"><a href=\""+navLink+"\">"+navTxt+"</a></li>";
        if (document.getElementById("mySideUL").innerHTML.indexOf(newLink) < 0) {
            document.getElementById("mySideUL").innerHTML = newLink+document.getElementById("mySideUL").innerHTML;
        }
    }
    return true;
}
function printHeadBar(percIn) {
    if (percIn > 0) {
        progressPerc = percIn;
        if (document.getElementById("progWrap")) document.getElementById("progWrap").innerHTML = getProgBar();
    }
    return true;
}
function getProgBar() {
    return "<div class=\"progress progress-striped progress-bar-animated\"><div class=\"progress-bar bg-striped\" role=\"progressbar\" aria-valuenow=\""+progressPerc+"\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:"+progressPerc+"%\"><span class=\"sr-only\">"+progressPerc+"% Complete</span></div></div>";
}

@if (isset($treeJs)) {!! $treeJs !!} @endif

@if (isset($jsXtra)) {!! $jsXtra !!} @endif
