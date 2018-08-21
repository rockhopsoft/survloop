/* generated from resources/views/vendor/survloop/scripts-js.blade.php */

function debugTxt(txt) {
    if (document.getElementById("absDebug")) {
        document.getElementById("absDebug").innerHTML = txt+"<br />"+document.getElementById("absDebug").innerHTML;
    }
    return true;
}

var appUrl = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}";
var defMetaImg = '{{ $GLOBALS['SL']->sysOpts['meta-img'] }}';

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
var treeListChk = new Array(); // when [0] is changed, reload field drops [1], [2]...

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
	    document.getElementById("formErrorMsg").innerHTML = "<h2>Please complete all required fields. <i class=\"fa fa-arrow-up\"></i>"+formErrorsEng+"</h2>";
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
        if (foundFocus != "") {
            document.getElementById(foundFocus).focus();
        } else if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").focus();
        } else if (document.getElementById("n"+nIDtxt+"fld0")) {
            document.getElementById("n"+nIDtxt+"fld0").focus();
        } else {
            scrollTo(document.getElementById("#n"+nIDtxt+""));
            /* window.location="#n"+nIDtxt+""; */
        }
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
	if (document.getElementById("n"+nIDtxt+"FldID") && document.getElementById("n"+nIDtxt+"FldID").value.trim() == "") {
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

function wordCountKeyUp(nIDtxt, limit) {
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
            var cntWords = "<span class=\"slRedLight\">"+words.length+"</span>";
            if (words.length < limit) cntWords = "<span class=\"slBlueDark\">"+words.length+"</span>";
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
function addTagOpt(nID, tagID, tagText, preSel) {
    if (!nodeTags[nID]) nodeTags[nID] = new Array();
    if (!nodeTags[nID][tagID]) nodeTags[nID][tagID] = new Array(tagText, preSel);
    if (!nodeTagList[nID]) nodeTagList[nID] = new Array();
    nodeTagList[nID][nodeTagList[nID].length] = tagID;
    return true;
}
function selectTag(nID, tagID) {
    if (nodeTags[nID] && nodeTags[nID][tagID]) nodeTags[nID][tagID][1] = 1;
    updateTagList(nID);
    return true;
}
function deselectTag(nID, tagID) {
    if (nodeTags[nID] && nodeTags[nID][tagID]) nodeTags[nID][tagID][1] = 0;
    updateTagList(nID);
    return false;
}
function printTag(nID, tagID, tagText) {
    return "<a onClick=\"return deselectTag("+nID+", "+tagID+");\" class=\"btn btn-primary\" href=\"javascript:;\">"+tagText+"<i class=\"fa fa-times\" aria-hidden=\"true\"></i></a> ";
}
function updateTagList(nID) {
    var tagIDs = ",";
    var tagHtml = "";
    if (nodeTags[nID] && nodeTagList[nID]) {
        for (var i = 0; i < nodeTagList[nID].length; i++) {
            var tagID = nodeTagList[nID][i];
            if (nodeTags[nID][tagID] && nodeTags[nID][tagID][1] == 1 && tagIDs.indexOf(","+tagID+",") < 0) {
                tagIDs += tagID+",";
                tagHtml += printTag(nID, tagID, nodeTags[nID][tagID][0]);
            }
        }
    }
    if (document.getElementById("n"+nID+"tagIDsID")) document.getElementById("n"+nID+"tagIDsID").value=tagIDs;
    if (document.getElementById("n"+nID+"tags")) document.getElementById("n"+nID+"tags").innerHTML=tagHtml;
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
                if (onOff) styBlock("node"+kids[k]+nSffx+"");
                else styNone("node"+kids[k]+nSffx+"");
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
    //alert(sURL);
    window.location.replace(sURL);
    return false;
}

function getSpinner() {
    return @if (isset($GLOBALS["SL"]->sysOpts["spinner-code"])) {!! 
        json_encode($GLOBALS["SL"]->sysOpts["spinner-code"]) !!} @endif;
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
function addHshoo(hash) {
    hshoos[hshoos.length] = new Array(hash, 0);
    return true;
}

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

$(document).ready(function(){
        
    function popDialog(title, desc) {
        if (document.getElementById("dialogPop")) {
            document.getElementById("dialogPop").title=title;
            document.getElementById("dialogPop").innerHTML=desc;
            $( "#dialogPop" ).dialog();
            if (document.getElementById( "nondialog" )) {
                $( "#nondialog" ).addClass( "opac20" );
            }
        }
        return true;
    }
    $(document).on("click", ".popDialog", function() {
		var title = $(this).attr("data-dia-title");
		var desc = $(this).attr("data-dia-desc");
        popDialog(title, desc);
    });
    function unPopDialog() { $( "#nondialog" ).removeClass( "opac20" ); return true; }
    $(document).on("click", ".unPopDialog", function() { unPopDialog(); });
    
    function chkFormSess() {
        /*
        if (document.getElementById("csrfTok")) {
            var src = "/time-out";
            if (document.getElementById("postNodeForm") && document.getElementById("stepID") && document.getElementById("treeID")) {
                src += "?form="+document.getElementById("treeID").value;
            } else if (document.getElementById("isLoginID") || document.getElementById("isSignupID")) {
                src += "?login=1";
            }
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            $("#dialog").fadeIn(300);
        }
        */
        return true;
    }
    setTimeout(function() { chkFormSess(); }, (115*60000));
    
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
        setTimeout(function() { chkRunSaveReload(); }, 1000);
    }
    setTimeout(function() { chkRunSaveReload(); }, 1000);
    $(document).on("click", ".nFormSaveReload", function() { runSaveReload(); });
    
    function runFormSubAjax() {
        blurAllFlds();
        var formData = new FormData(document.getElementById("postNodeForm"));
        replaceAjaxWithSpinner();
        window.scrollTo(0, 0);
        var actionUrl = "/sub";
        if (document.getElementById("postActionID")) actionUrl = document.getElementById("postActionID").value;
        $.ajax({
            url: actionUrl,
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
            if (!document.getElementById('emailBlockID')) {
                runFormSubAjax();
            } else {
                if (document.getElementById("stepID") && document.getElementById("stepID").value=="next") {
                    if (document.getElementById("ajaxID")) document.getElementById("ajaxID").value=0;
                    document.postNode.action="/register";
                    document.postNode.target="_parent";
                    document.postNode.submit();
                } else {
                    runFormSubAjax();
                }
            }
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
    setTimeout(function() { if (!document.getElementById("isPage")) postNodeAutoSave(); }, 60000);
    
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
        if (otherFormSub) {
            return runFormSub();
            otherFormSub = false;
        }
        if (document.getElementById("admMenu")) {
            var leftPos = $(document).scrollLeft();
            if (leftPos > 0) {
                document.getElementById("leftSideWrap").style.position="static";
            } else {
                document.getElementById("leftSideWrap").style.position="fixed";
            }
        }
        setTimeout(function() { timeoutChecks(); }, 500);
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
	
	$(document).on("click", ".navDeskMaj", function() {
		var majInd = $(this).attr("id").replace("maj", "");
		if ($(this).attr("data-jumpnode") && document.getElementById("stepID") && document.getElementById("jumpToID")) {
            document.getElementById("jumpToID").value = $(this).attr("data-jumpnode");
            if (document.getElementById("dataLoopRootID")) document.getElementById("stepID").value="exitLoopJump";
            return runFormSub();
		}
		for (var i = 0; i < treeMajorSects.length; i++) {
		    if (i != majInd && document.getElementById("minorNav"+i+"") 
		        && document.getElementById("majSect"+i+"Vert2")) {
		        document.getElementById("minorNav"+i+"").style.display = 'none';
		        document.getElementById("majSect"+i+"Vert2").style.display = 'none';
            }
		}
		if (document.getElementById("minorNav"+majInd+"") && document.getElementById("majSect"+majInd+"Vert2")) {
		    if (document.getElementById("minorNav"+majInd+"").style.display == 'block') {
		        document.getElementById("minorNav"+majInd+"").style.display = 'none';
		        document.getElementById("majSect"+majInd+"Vert2").style.display = 'none';
            } else {
		        document.getElementById("minorNav"+majInd+"").style.display = 'block';
		        document.getElementById("majSect"+majInd+"Vert2").style.display = 'block';
            }
        }
	});
	
	$(document).on("click", "#navMobBurger1", function() {
		document.getElementById("navMobBurger1").style.display="none";
		document.getElementById("navMobBurger2").style.display="inline";
		$("#navMobFull").slideDown("fast");
	});
	$(document).on("click", "#navMobBurger2", function() {
		document.getElementById("navMobBurger1").style.display="inline";
		document.getElementById("navMobBurger2").style.display="none";
		$("#navMobFull").slideUp("fast");
	});
	
	$(document).on("click", ".nodeShowCond", function() {
        var nID = $(this).attr("id").replace("showCond", "");
        if (document.getElementById("condDeets"+nID+"")) {
            if (document.getElementById("condDeets"+nID+"").style.display=="inline") {
                document.getElementById("condDeets"+nID+"").style.display="none";
            } else {
                document.getElementById("condDeets"+nID+"").style.display="inline";
            }
        }
        return true;
    });

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
    
    $("a.hsho").on('click', function(event) {
        var hash = $(this).attr("data-hash").trim();
        if (hash !== "") {
            if (document.getElementById(hash)) {
                var newTop = (1+$("#"+hash+"").offset().top);
                $('html, body').animate({ scrollTop: newTop }, 800, 'swing', function(){ });
            }
        }
    });
    $("a.hshoo").on('click', function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            if (document.getElementById(hash.replace('#', ''))) {
                var newTop = (1+$(hash).offset().top);
                $('html, body').animate({ scrollTop: newTop }, 800, 'swing', function(){
                    window.location.hash = hash;
                });
            }
        }
    });
    function chkHshoosPos() {
        for (var i = 0; i < hshoos.length; i++) {
            if (document.getElementById(hshoos[i][0].replace('#', ''))) {
                hshoos[i][1] = $(hshoos[i][0]).offset().top;
                var admLnk = "admLnk"+hshoos[i][0].replace('#', '');
                if (document.getElementById(admLnk)) {
                    if (document.getElementById(admLnk).className.indexOf("active") !== false) hshooCurr = i;
                }
            }
        }
        var absMin = -1000000000;
        var newArr = new Array();
        for (var i = 0; i < hshoos.length; i++) {
            var min = new Array(0, 1000000000);
            for (var j = 0; j < hshoos.length; j++) {
                if (absMin < hshoos[j][1] && hshoos[j][1] < min[1]) { // 0 < h && 
                    min = new Array(j, hshoos[j][1]);
                }
            }
            if (min[1] > -1000000000) {
                absMin = min[1];
                newArr[newArr.length] = new Array(hshoos[min[0]][0], hshoos[min[0]][1]);
            }
        }
        hshoos = newArr;
        return true;
    }
    function chkHshooScroll() {
        if (hshoos.length > 0) {
            hshooCurr = -1;
            var currScroll = $(document).scrollTop();
            for (var i = 0; i < hshoos.length; i++) {
                var compareScroll = Math.ceil(currScroll)+2;
                if (hshoos[i][1] <= compareScroll) hshooCurr = i;
            }
            if (hshooCurr < 0) hshooCurr = 0;
            for (var i = 0; i < hshoos.length; i++) {
                var admLnk = "admLnk"+hshoos[i][0].replace('#', '');
                if (document.getElementById(admLnk)) {
                    if (i == hshooCurr) $("#"+admLnk+"").addClass('active');
                    else $("#"+admLnk+"").removeClass('active');
                }
            }
        }
        return true;
    }
    setTimeout(function() { chkHshoosPos(); }, 10);
    setTimeout(function() { chkHshooScroll(); }, 50);
    $(document).scroll(function() {
        chkHshooScroll();
    });
    
    function chkScrollPar() {
        var scrolled = $(window).scrollTop();
        $(".parallax").each(function(index, element) {
            var initY = $(this).offset().top;
            var height = $(this).height();
            var endY  = initY + $(this).height();
            var visible = isInViewport(this);
            if (visible) {
                var diff = scrolled - initY;
                var ratio = Math.round((diff / height) * 100);
                $(this).css('background-position','center ' + parseInt(-(ratio * 1.5)) + 'px');
            }
        });
    }
    $(window).scroll(function() {
        chkScrollPar();
    });
    setTimeout(function() { chkScrollPar() }, 10);
    
	$(document).on("click", ".searchBarBtn", function() {
        var nID = $(this).attr("id").replace("searchTxt", "").split("t");
        return runSearch(nID[0], nID[1]);
	});
	$(document).on("keyup", ".searchBar", function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            var nID = $(this).attr("id").replace("searchBar", "").split("t");
            return runSearch(nID[0], nID[1]);
        }
    });
    
    $(".slSortable").sortable({
        axis: "y",
        update: function (event, ui) {
            var submitURL = $(this).attr("data-url");
            document.getElementById("hidFrameID").src=submitURL+"&"+$(this).sortable("serialize");
        }
    });
    $(".slSortable").disableSelection();
    
    $(document).on("click", ".dialogOpen", function() {
	    if (document.getElementById("dialogBody") && document.getElementById("dialogTitle")) {
            document.getElementById("dialogBody").innerHTML='<center>'+getSpinner()+'</center>';
            var src = $(this).attr("href");
            var title = $(this).attr("title");
            document.getElementById("dialogTitle").innerHTML=title;
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            $("#dialog").fadeIn(300);
        }
		return false;
	});
	$(document).on("click", ".dialogClose", function() {
		$("#dialog").fadeOut(300);
		$("#nondialog").fadeIn(300);
	});
	
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
	
	if (document.getElementById('tblSelect')) {
        //alert("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+" - /dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
        $("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+"");
        $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
    }
    
    function switchTreeOpts(fldID, treeID) {
	    for (var i = 0; i < treeListChk.length; i++) {
	        if (treeListChk[i][0] == fldID) {
	            for (var j = 1; j < treeListChk[i].length; j++) {
	                var loadURL = "/ajax-get-flds/"+treeID+"";
	                if (document.getElementById(treeListChk[i][j]+"presel")) {
	                    loadURL += "?fld="+document.getElementById(treeListChk[i][j]+"presel").value;
	                }
	                $("#"+treeListChk[i][j]+"").load(loadURL);
	            }
	        }
	    }
	    return true;
    }
    $(".switchTree").change(function(){
        var treeFld = $(this).attr("id");
        switchTreeOpts(treeFld, document.getElementById(treeFld).value);
	});
    setTimeout(function() {
        for (var i = 0; i < treeListChk.length; i++) {
            if (treeListChk[i][0] && document.getElementById(treeListChk[i][0])) {
                switchTreeOpts(treeListChk[i][0], document.getElementById(treeListChk[i][0]).value);
            }
        }
    }, 10);
    
    function toggleHidiv(fldGrp) {
        if (document.getElementById("hidiv"+fldGrp+"")) {
            if (document.getElementById("hidiv"+fldGrp+"").style.display!="block") {
                $("#hidiv"+fldGrp+"").slideDown("fast");
            } else {
                $("#hidiv"+fldGrp+"").slideUp("fast");
            }
        }
        return true;
    }
	$(document).on("click", ".hidivCrt", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        if (document.getElementById("hidiv"+fldGrp+"") && document.getElementById("hidivCrt"+fldGrp+"")) {
            if (document.getElementById("hidiv"+fldGrp+"").style.display!="block") {
                $("#hidivCrt"+fldGrp+"").attr('class', 'fa fa-chevron-up');
            } else {
                $("#hidivCrt"+fldGrp+"").attr('class', 'fa fa-chevron-down');
            }
        }
        return true;
	});
	$(document).on("click", ".hidivBtn", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        toggleHidiv(fldGrp);
	});
	$(document).on("click", ".hidivBtnSelf", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        $(this).slideUp("fast");
        setTimeout(function() { toggleHidiv(fldGrp); }, 350);
	});
    
    function toggleHidnode(nID) {
        if (document.getElementById("node"+nID+"")) {
            if (document.getElementById("node"+nID+"").style.display!="block") {
                setNodeVisib(""+nID+"", "", true);
                $("#node"+nID+"").slideDown("fast");
            } else {
                setNodeVisib(""+nID+"", "", false);
                $("#node"+nID+"").slideUp("fast");
            }
        }
        return true;
    }
	$(document).on("click", ".hidnodeBtn", function() {
        var nID = $(this).attr("id").replace("hidnodeBtn", "");
        toggleHidnode(nID);
	});
	$(document).on("click", ".hidnodeBtnSelf", function() {
        var nID = $(this).attr("id").replace("hidnodeBtn", "");
        toggleHidnode(nID);
        $(this).slideUp("fast");
	});
	
	$(document).on("click", ".hidTogAll", function() {
	    if ($(this).attr("data-list") && $(this).attr("data-list").trim() != '') {
            var list = $(this).attr("data-list").split(",");
            if (list.length > 0 && document.getElementById("hidiv"+list[0]+"")) {
                var nowShow = false;
                if (document.getElementById("hidiv"+list[0]+"").style.display!="block") nowShow = true;
                for (var j = 0; j < list.length; j++) {
                    if (document.getElementById("hidiv"+list[j]+"")) {
                        if (nowShow) $("#hidiv"+list[j]+"").slideDown("fast");
                        else $("#hidiv"+list[j]+"").slideUp("fast");
                    }
                    if (document.getElementById("hidivCrt"+list[j]+"")) {
                        if (nowShow) $("#hidivCrt"+list[j]+"").attr('class', 'fa fa-chevron-up');
                        else $("#hidivCrt"+list[j]+"").attr('class', 'fa fa-chevron-down');
                    }
                }
            }
        }
	});
	
	
	
	$(document).on("click", ".ajx", function() {
        var ajxUrl = $(this).attr("data-ajx");
        var dest = $(this).attr("data-dst");
        if (document.getElementById(dest)) $("#"+dest+"").load(ajxUrl);
	});
    
	function chkMenuLoad() {
	    if (!document.getElementById('loginLnk') && document.getElementById('headClear')) {
	        $("#headClear").load("/js-load-menu");
	    }
	}
    setTimeout(function() { chkMenuLoad(); }, 500);
    setTimeout(function() { chkMenuLoad(); }, 2000);
	
    $(document).on("click", ".adminAboutTog", function() {
        if (document.getElementById('adminAbout')) {
            $("#adminAbout").slideToggle('slow');
        }
	});
	
	function updateImgSelect(nIDtxt) {
        if (document.getElementById("n"+nIDtxt+"FldID") && document.getElementById("n"+nIDtxt+"SelImg")) {
            var imgSrc = "";
            if (document.getElementById("n"+nIDtxt+"FldID").value.trim() != "") {
                imgSrc = document.getElementById("n"+nIDtxt+"FldID").value.trim();
            }
            document.getElementById("n"+nIDtxt+"SelImg").src=imgSrc;
        }
        return true;
    }
	$(document).on("click", ".openImgUpdate", function() {
        updateImgSelect($(this).attr("id").replace("imgUpd", "").replace("n", "").replace("FldID", ""));
	});
    function defaultImgSelect(nIDtxt) {
        if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").value = defMetaImg.replace(appUrl, "");
        }
        updateImgSelect(nIDtxt);
        return true;
    }
	$(document).on("click", ".openImgReset", function() {
	    var nIDtxt = $(this).attr("id").replace("imgReset", "");
        defaultImgSelect(nIDtxt);
        updateImgSelect(nIDtxt);
	});
    function openImgSelect(nIDtxt, title, presel) {
        if (document.getElementById("dialogTitle")) document.getElementById("dialogTitle").innerHTML = title;
        $("#nondialog").fadeOut(300);
        window.scrollTo(0, 0);
        $("#dialogBody").load("/ajax/img-sel?nIDtxt="+nIDtxt+"&presel="+encodeURIComponent(presel));
        $("#dialog").fadeIn(300);
        return true;
    }
	$(document).on("click", ".openImgSelect", function() {
	    var imgID = $(this).attr("id").replace("imgSelect", "");
	    var title = "";
	    if (document.getElementById("imgSelect"+imgID+"Title")) {
	        title = document.getElementById("imgSelect"+imgID+"Title").innerHTML.trim();
	    } else if ($(this).attr("data-title") && $(this).attr("data-title").trim() != '') {
	        title = $(this).attr("data-title").trim();
	    }
        openImgSelect(imgID, title, $(this).attr("data-presel"));
	});
    function openImgDetail(nIDtxt, imgID) {
        if (document.getElementById("imgDeetDiv"+nIDtxt+"")) {
            document.getElementById("imgDeetDiv"+nIDtxt+"").innerHTML = '<center>'+getSpinner()+'</center>';
            document.getElementById("hidivImgUp"+nIDtxt+"").style.display = "none";
            document.getElementById("hidivBtnImgUp"+nIDtxt+"").style.display = "block";
            $("#imgDeetDiv"+nIDtxt+"").load( "/ajax/img-deet?nIDtxt="+nIDtxt+"&imgID="+imgID+"" );
        }
        return true;
    }
	$(document).on("click", ".openImgDetail", function() {
	    var ids = $(this).attr("id").replace("selectImg", "").split("sel");
        openImgDetail(ids[0], ids[1]);
	});
    function getImgNode(imgID) {
	    var nIDtxt = "";
	    if (document.getElementById("imgNode"+imgID+"ID")) nIDtxt = document.getElementById("imgNode"+imgID+"ID").value;
	    return nIDtxt;
    }
    function imgChoose(imgID) {
        var nIDtxt = getImgNode(imgID);
        $("#nondialog").fadeIn(300);
        $("#dialog").fadeOut(300);
	    var url = "";
	    if (document.getElementById("imgUrl"+imgID+"ID")) url = document.getElementById("imgUrl"+imgID+"ID").value;
        if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").value=url;
        }
        updateImgSelect(nIDtxt);
        return true;
    }
	$(document).on("click", ".imgChoose", function() {
	    imgChoose($(this).attr("id").replace("imgChoose", ""));
	});
    function imgSaveDeet(imgID) {
        var nIDtxt = getImgNode(imgID);
        if (document.getElementById("img"+imgID+"saveUpdate")) {
            document.getElementById("img"+imgID+"saveUpdate").innerHTML='<center>'+getSpinner()+'</center>';
            var formData = new FormData(document.getElementById("formSaveImg"+imgID+"ID"));
            $.ajax({
                url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-save",
                type: "POST", 
                data: formData, 
                contentType: false,
                processData: false,
                success: function(data) {
                    $("#img"+imgID+"saveUpdate").empty();
                    $("#img"+imgID+"saveUpdate").append(data);
                }, 
                error: function(xhr, status, error) {
                    $("#img"+imgID+"saveUpdate").append("<div>(error - "+xhr.responseText+")</div>");
                }
            });
        }
        return true;
    }
	$(document).on("click", ".imgSaveDeet", function() {
	    var imgID = $(this).attr("id").replace("imgSave", "");
	    imgSaveDeet(imgID);
	});
	$(document).on("keyup", ".imgSaveDeetFld", function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            if ($(this).attr("data-imgid")) imgSaveDeet($(this).attr("data-imgid"));
        }
    });
    function imgUpBtn(nIDtxt) {
        if (document.getElementById("img"+nIDtxt+"fileUpdate")) {
            document.getElementById("img"+nIDtxt+"fileUpdate").innerHTML='<center>'+getSpinner()+'</center>';
            var formData = new FormData(document.getElementById("formUpImg"+nIDtxt+"ID"));
            $.ajax({
                url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-up",
                type: "POST", 
                data: formData, 
                contentType: false,
                processData: false,
                success: function(data) {
                    $("#img"+nIDtxt+"fileUpdate").empty();
                    $("#img"+nIDtxt+"fileUpdate").append(data);
                }, 
                error: function(xhr, status, error) {
                    $("#img"+nIDtxt+"fileUpdate").append("<div>(error - "+xhr.responseText+")</div>");
                }
            });
        }
        return true;
    }
	$(document).on("click", ".imgUpBtn", function() {
	    imgUpBtn($(this).attr("id").replace("imgUp", ""));
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
    
	function pullNewGraph(nID) {
	    for (var i=0; i < graphFlds.length; i++) {
	        if (graphFlds[i][0] == nID) {
	            var p = graphFlds[i][2];
	            for (var g=0; g < graphs.length; g++) {
	                if (graphs[g][0] == p) {
                        var graphUrl = graphs[g][2];
                        var fVars = "";
                        for (var j=0; j < graphFlds.length; j++) {
                            if (graphFlds[j][2] == graphFlds[i][2]) {
                                fVars += "__"+graphFlds[j][3]+"|";
                                if (document.getElementById("n"+graphFlds[j][1]+"FldID")) {
                                    fVars += document.getElementById("n"+graphFlds[j][1]+"FldID").value;
                                } else {
                                    fVars += $(".nCbox"+nID+":checked").map(function() { return this.value; }).get();
                                }
                            }
                        }
                        if (fVars.length > 0) graphUrl += "?f="+fVars.substring(2);
                        $("#n"+p+"ajaxLoad").load(graphUrl);
                        //document.getElementById('node287').innerHTML+="<br />"+graphUrl;
                    }
                }
	        }
	    }
	    return true;
    }
    $(document).on("change", ".graphUpDrp", function() { pullNewGraph($(this).attr("data-nid")); return true; });
    $(document).on("click", ".graphUp", function() { pullNewGraph($(this).attr("data-nid")); return true; });
    
    function sliHgt(nIDtxt, next) {
        for (var i = 0; i < slideGals.length; i++) {
            if (next < 0) next = slideGals[i][3];
            if (slideGals[i][0] == nIDtxt && document.getElementById("blockWrap"+slideGals[i][1][next]+"")) {
                var newH = $("#blockWrap"+slideGals[i][1][next]+"").height();
                if (document.getElementById("node"+nIDtxt+"kids")) {
                    document.getElementById("node"+nIDtxt+"kids").style.height=newH+"px";
                }
                var btnHgt = 30+newH;
                var hvrHgt = newH;
                if (document.getElementById("sliLft"+nIDtxt+"")) {
                    document.getElementById("sliLft"+nIDtxt+"").style.height=btnHgt+"px";
                    document.getElementById("sliLft"+nIDtxt+"").style.marginTop="-"+newH+"px";
                    document.getElementById("sliLftHvr"+nIDtxt+"").style.height=newH+"px";
                }
                if (document.getElementById("sliRgt"+nIDtxt+"")) {
                    document.getElementById("sliRgt"+nIDtxt+"").style.height=btnHgt+"px";
                    document.getElementById("sliRgt"+nIDtxt+"").style.marginTop="-"+newH+"px";
                    document.getElementById("sliRgtHvr"+nIDtxt+"").style.height=newH+"px";
                }
            }
        }
        return true;
    }
    function sliChange(nIDtxt, next) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                if (document.getElementById("sliNav"+nIDtxt+"dot"+slideGals[i][3]+"") && document.getElementById("sliNav"+nIDtxt+"dot"+next+"")) {
                    $("#sliNav"+nIDtxt+"dot"+slideGals[i][3]+"").removeClass('sliNavAct');
                    $("#sliNav"+nIDtxt+"dot"+slideGals[i][3]+"").addClass('sliNav');
                    $("#sliNav"+nIDtxt+"dot"+next+"").removeClass('sliNav');
                    $("#sliNav"+nIDtxt+"dot"+next+"").addClass('sliNavAct');
                }
                for (var j = 0; j < slideGals[i][1].length; j++) {
                    var kidID = slideGals[i][1][j];
                    if (document.getElementById("blockWrap"+kidID+"")) {
                        if (j == next) {
                            $("#blockWrap"+kidID+"").delay(451).fadeIn(450);
                            setTimeout(function() { sliHgt(nIDtxt, j); }, 451);
                        } else {
                            $("#blockWrap"+kidID+"").fadeOut(450);
                        }
                    }
                }
                slideGals[i][3] = next;
            }
        }
        return true;
    }
    function sliLoadAuto(nIDtxt) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                if (slideGals[i][4] > 0) {
                    slideGals[i][4] = 0;
                } else {
                    sliNext(nIDtxt);
                }
                setTimeout(function() { sliLoadAuto(nIDtxt); }, 8000);
            }
        }
        return true;
    }
    function sliLoadHgts() {
        for (var i = 0; i < slideGals.length; i++) sliHgt(slideGals[i][0], -1);
        return true;
    }
    function sliLoad() {
        for (var i = 0; i < slideGals.length; i++) {
            var nIDtxt = slideGals[i][0];
            sliChange(nIDtxt, 0);
            sliHgt(nIDtxt, 0);
            setTimeout(function() { sliLoadAuto(nIDtxt); }, 8000);
        }
        sliLoadHgts();
        return true;
    }
    setTimeout(function() { sliLoad(); }, 1);
    function sliNext(nIDtxt) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                var next = slideGals[i][3]+1;
                if (next >= slideGals[i][1].length) next = 0;
                sliChange(nIDtxt, next);
            }
        }
        return true;
    }
    $(document).on("click", ".sliRgt", function() {
        var nIDtxt = $(this).attr("id").replace("sliRgt", "");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) slideGals[i][4]++;
        }
        sliNext(nIDtxt);
        return true;
    });
    $(document).on("click", ".sliLft", function() {
        var nIDtxt = $(this).attr("id").replace("sliLft", "");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                var next = slideGals[i][3]-1;
                if (next < 0) next = slideGals[i][1].length-1;
                slideGals[i][4]++;
                sliChange(nIDtxt, next);
            }
        }
        return true;
    });
    $(document).on("click", ".sliNav", function() {
        var n = $(this).attr("id").replace("sliNav", "").split("dot");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == n[0]) slideGals[i][4]++;
        }
        sliChange(n[0], n[1]);
        return true;
    });
    
@if ($GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
    function chkLogoResize() {
        if (!document.getElementById('slLogoImg')) return false;
        if (window.innerWidth <= 480) {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}';
        } else if (window.innerWidth <= 768) {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-md'] }}';
        } else {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}';
        }
    }
    setTimeout(function() { chkLogoResize(); }, 1);
@endif

    var winResizeHold = false;
    $(window).on('resize', function(){
        if (!winResizeHold) {
            winResizeHold = true;
            var win = $(this); //this = window
            sliLoadHgts();
@if ($GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
            chkLogoResize();
@endif
            setTimeout(function() { winResizeHold = false; }, 250);
        }
    });
    
    $(document).on("click", ".admSrchFldFocus", function() {
        if (document.getElementById("admSrchFld")) {
            $("#admSrchFld").focus();
        }
    });
    
    function chkFixedHeader() {
        if (document.getElementById('fixedHeader')) {
            var newW = 30+Math.round($("#postNodeForm").outerWidth());
            if (document.getElementById('leftSideWrap') && document.getElementById('leftSideWrap').style.width != '0px') {
                newW = Math.round($("#mainBody").outerWidth())-40;
                document.getElementById('fixedHeader').style.paddingTop = '20px';
                document.getElementById('fixedHeader').style.paddingRight = '20px';
            }
            document.getElementById('fixedHeader').style.width = ''+newW+'px';
            setTimeout(function() { chkFixedHeader(); }, 10000);
        } else {
            setTimeout(function() { chkFixedHeader(); }, 60000);
        }
    }
    setTimeout(function() { chkFixedHeader(); }, 100);
    
    function openAdmMenu() {
        if (document.getElementById("leftAdmMenu") && document.getElementById("leftAdmMenu").style.display != 'block') {
            if (document.getElementById("menuUnColpsBtn")) $("#menuUnColpsBtn").slideUp("fast");
            if (document.getElementById("menuColpsBtn")) $("#menuColpsBtn").slideDown("fast");
            if (document.getElementById("leftSideWdth")) {
                if (document.getElementById("leftSideWrap")) {
                    $("#leftSideWrap").animate({
                        padding: "10px 15px 0px 15px"
                    }, {
                        duration: 150,
                        specialEasing: {
                            width: 'swing'
                        }
                    });
                }
                $("#leftSideWdth").animate({
                    width: "230px"
                }, {
                    duration: 150,
                    specialEasing: {
                        width: 'swing'
                    }
                });
                setTimeout(function() { $("#leftAdmMenu").slideDown(150); }, 150);
            }
        }
    }
    function closeAdmMenu() {
        if (document.getElementById("leftAdmMenu") && document.getElementById("leftAdmMenu").style.display != 'none') {
            if (document.getElementById("menuColpsBtn")) $("#menuColpsBtn").slideUp("fast");
            if (document.getElementById("menuUnColpsBtn")) $("#menuUnColpsBtn").slideDown("fast");
               $("#leftAdmMenu").slideUp(150);
            if (document.getElementById("leftSideWdth")) {
                setTimeout(function() { 
                    if (document.getElementById("leftSideWrap")) {
                        $("#leftSideWrap").animate({
                            padding: "0px 6px"
                        }, {
                            duration: 150,
                            specialEasing: {
                                width: 'swing'
                            }
                        });
                    }
                    $("#leftSideWdth").animate({
                        width: "24px"
                    }, {
                        duration: 150,
                        specialEasing: {
                            width: 'swing'
                        }
                    });
                }, 150);
            }
        }
    }
    $(document).on("click", "#menuColpsBtn", function() { closeAdmMenu(); });
    $(document).on("click", "#menuUnColpsBtn", function() { openAdmMenu(); });
    $(window).resize(function() {
        if ($(window).width() <= 992) closeAdmMenu();
        else openAdmMenu();
    });
    
    $(document).on("click", ".clickBox", function() {
        if ($(this).attr("data-url")) window.location=$(this).attr("data-url");
        return true;
	});
    $(document).on({
        mouseenter: function () {
            $(this).css("background-color", "{!! $css['color-main-faint'] !!}");
        },
        mouseleave: function () {
            $(this).css("background-color", "{!! $css['color-main-bg'] !!}");
        }
    }, ".clickBox");
	
    
    @if (isset($jqueryXtra)) {!! $jqueryXtra !!} @endif
	
});

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
    document.getElementById("mySidenav").style.boxShadow = "0px 0px 60px {!! $css["color-main-grey"] !!}";
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

var progressPerc = 0;
var treeMajorSects = new Array();
var treeMinorSects = new Array();
var treeMajorSectsDisabled = new Array();
function addTopCust(navCode) {
    if (document.getElementById("myNavBarIn")) {
        if (document.getElementById("myNavBarIn").innerHTML.indexOf(navCode) < 0) {
            document.getElementById("myNavBarIn").innerHTML += navCode;
        }
    }
    return true;
}
function addTopNavItem(navTxt, navLink) {
    if (document.getElementById("myNavBarIn")) {
        if (navTxt == 'pencil') navTxt = "<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>";
        var newLink = "<a class=\"pull-right slNavLnk\" href=\""+navLink+"\">"+navTxt+"</a>";
        if (document.getElementById("myNavBarIn").innerHTML.indexOf(newLink) < 0) {
            document.getElementById("myNavBarIn").innerHTML += newLink;
        }
    }
    return true;
}
function addSideNavItem(navTxt, navLink) {
    if (document.getElementById("mySideUL")) {
        document.getElementById("mySideUL").innerHTML += "<li><a href=\""+navLink+"\">"+navTxt+"</a></li>";
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
    return "<div class=\"progress progress-striped active\"><div class=\"progress-bar progress-bar-striped\" role=\"progressbar\" aria-valuenow=\""+progressPerc+"\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:"+progressPerc+"%\"><span class=\"sr-only\">"+progressPerc+"% Complete</span></div></div>";
}


@if (isset($jsXtra)) {!! $jsXtra !!} @endif
