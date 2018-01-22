/* generated from resources/views/vendor/survloop/scripts-js.blade.php */

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

var heroActions = new Array();
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
        for (var h=0; h < heroActions.length; h++) {
            if (document.getElementById("heroAction"+heroActions[h]+"")) {
                document.getElementById("heroAction"+heroActions[h]+"").style.width = window.innerWidth;
            }
        }
    }
    window.onresize = function() { chkLogoResize(); }
    chkLogoResize();
@endif

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

var hasAttemptedSubmit = false;
var totFormErrors = 0;
var formErrorsEng = "";

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

function setFormLabelBlack(nID) {
	if (document.getElementById("node"+nID+"")) {
	    document.getElementById("node"+nID+"").className=document.getElementById("node"+nID+"").className.replace("nodeWrapError", "nodeWrap");
	}
	return true;
}

var firstNodeError = 0;
function setFormLabelRed(nID) {
    if (firstNodeError <= 0) {
        firstNodeError = nID;
        scrollTo(document.getElementById("#n"+nID+""));
        //window.location="#n"+nID+"";
    }
	if (document.getElementById("node"+nID+"")) {
	    document.getElementById("node"+nID+"").className=document.getElementById("node"+nID+"").className.replace("nodeWrap", "nodeWrapError").replace("nodeWrapErrorError", "nodeWrapError");
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

function reqFormTxt(fldID, nID) {
	if (document.getElementById(fldID) && document.getElementById(fldID).value.trim() == "") {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}
function reqFormFld(nID) {
	if (document.getElementById("n"+nID+"FldID") && document.getElementById("n"+nID+"FldID").value.trim() == "") {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}
function reqFormFldEmail(nID) {
	if (!reqFormEmail("n"+nID+"FldID")) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}
function reqFormFldGreater(nID, min) {
	if (document.getElementById("n"+nID+"FldID") && (document.getElementById("n"+nID+"FldID").value.trim() == "" || Number.parseFloat(document.getElementById("n"+nID+"FldID").value) < Number.parseFloat(min))) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}
function reqFormFldRadio(nID, maxOpts) {
	var foundCheck = false;
	for (var j=0; j < maxOpts; j++) {
		if (document.getElementById("n"+nID+"fld"+j+"") && document.getElementById("n"+nID+"fld"+j+"").checked) foundCheck = true;
	}
	if (!foundCheck) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}

var radioNodes = new Array();
function addRadioNode(nID) {
    radioNodes[radioNodes.length] = nID;
    return true;
}
function chkIsRadioNode(nID) {
    for (var i=0; i < radioNodes.length; i++) {
        if (radioNodes[i] == nID) return true;
    }
    return false;
}
function runRadioClick(nID, response) {
    if (document.getElementById("n"+nID+"fld"+response+"") && document.getElementById("n"+nID+"radioCurrID")) {
        if (document.getElementById("n"+nID+"fld"+response+"").value != document.getElementById("n"+nID+"radioCurrID").value) {
            document.getElementById("n"+nID+"radioCurrID").value = document.getElementById("n"+nID+"fld"+response+"").value;
        } else {
            document.getElementById("n"+nID+"fld"+response+"").checked = false;
            document.getElementById("n"+nID+"radioCurrID").value = "";
            checkFingerClass(nID);
            chkFormCheck();
        }
    }
    return true;
}


function checkFldDate(nID) {
    return (document.getElementById("n"+nID+"fldYearID").value != "0000" 
	    && document.getElementById("n"+nID+"fldMonthID").value != "00" 
	    && document.getElementById("n"+nID+"fldDayID").value != "00");
}

function reqFormFldDate(nID) {
	if (!checkFldDate(nID)) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}

function reqFormFldDateAndLimit(nID, future, today, optional) {
	if (!checkFldDate(nID) || !chkFormFldDateLimit(nID, future, today, optional)) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}

function reqFormFldDateLimit(nID, future, today, optional) {
	if (!chkFormFldDateLimit(nID, future, today, optional)) {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}

function chkFormFldDateLimit(nID, future, today, optional) {
    if (future == 0) return true;
    validDate = true;
    if (optional == 1 && !checkFldDate(nID)) {
    } else if (checkFldDate(nID)) {
        var todayYear = parseInt(today.substring(0, 4));
        var todayMonth = parseInt(today.substring(5, 7));
        var todayDay = parseInt(today.substring(8, 10));
        var userYear = parseInt(document.getElementById("n"+nID+"fldYearID").value);
        var userMonth = parseInt(document.getElementById("n"+nID+"fldMonthID").value);
        var userDay = parseInt(document.getElementById("n"+nID+"fldDayID").value);
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

function charLimit(nID, limit) {
    if (document.getElementById("n"+nID+"FldID")) {
        if (document.getElementById("n"+nID+"FldID").value.length > limit) {
            document.getElementById("n"+nID+"FldID").value = document.getElementById("n"+nID+"FldID").value.substring(0, limit);
        }
        var charRemain = limit-document.getElementById("n"+nID+"FldID").value.length;
        document.getElementById("charLimit"+nID+"Msg").innerHTML = limit+" Character Limit: "+charRemain+" Remaining";
    }
	return true;
}

function formDateChange(nID) {
	document.getElementById("n"+nID+"FldID").value = document.getElementById("n"+nID+"fldYearID").value+"-"+document.getElementById("n"+nID+"fldMonthID").value+"-"+document.getElementById("n"+nID+"fldDayID").value;
	chkFormCheck();
	return true;
}

function dateKeyUp(nID, which) {
	document.getElementById("n"+nID+"FldID").value = document.getElementById("n"+nID+"fldMonthID").value+"/"+document.getElementById("n"+nID+"fldDayID").value+"/"+document.getElementById("n"+nID+"fldYearID").value;
	chkFormCheck();
	return true;
}


function formChangeFeetInches(nID) {
	if (document.getElementById("n"+nID+"FldID")) document.getElementById("n"+nID+"FldID").value = (12*parseInt(document.getElementById("n"+nID+"fldFeetID").value))+parseInt(document.getElementById("n"+nID+"fldInchID").value);
	chkFormCheck();
	return true;
}
function formRequireFeetInches(nID) {
	if (document.getElementById("n"+nID+"fldFeetID").value.trim() == "" || document.getElementById("n"+nID+"fldInchID").value.trim() == "") {
		setFormLabelRed(nID);
		totFormErrors++;
	} else {
	    setFormLabelBlack(nID);
	}
	return true;
}

function formRequireGender(nID) {
	if (document.getElementById("n"+nID+"fld2") && document.getElementById("n"+nID+"fld2").value == "?") {
		return reqFormFldRadio(nID, 4);  // we also have "Not Sure"
	}
	return reqFormFldRadio(nID, 3);
}

var graphs = new Array();
function addGraph(nID, nIDtxt, baseurl) {
    graphs[graphs.length] = [ nID, nIDtxt, baseurl ];
    return true;
}
var graphFlds = new Array();
function addGraphFld(nID, nIDtxt, fld, parent) {
    graphFlds[graphFlds.length] = [ nID, nIDtxt, parent, fld, "" ];
    return true;
}

function checkNodeUp(nID, response, isMobile) {
    checkMutEx(nID, response);
    if (isMobile == 1) checkFingerClass(nID);
    if (chkIsRadioNode(nID)) setTimeout("runRadioClick('"+nID+"', '"+response+"')", 10);
	chkFormCheck();
    return true;
}

function formKeyUpOther(nID, j) {
    if (document.getElementById("n"+nID+"fldOtherID"+j+"").value.trim() != "") {
        document.getElementById("n"+nID+"fld"+j+"").checked=true;
        checkFingerClass(nID);
    }
	chkFormCheck();
    return true;
}

function formClickGender(nID) {
    if (document.getElementById("n"+nID+"fldOtherID")) {
        if ((document.getElementById("n"+nID+"fld0") && document.getElementById("n"+nID+"fld0").checked)
            || (document.getElementById("n"+nID+"fld1") && document.getElementById("n"+nID+"fld1").checked)
            || (document.getElementById("n"+nID+"fld3") && document.getElementById("n"+nID+"fld3").checked)) {
            document.getElementById("n"+nID+"fldOtherID").value = "";
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

function focusNodeID(nID) {
    var fldID = "n"+nID+"FldID";
    if (!document.getElementById(fldID)) {
        fldID = "n"+nID+"fld0";
        if (!document.getElementById(fldID)) {
            fldID = "n"+nID+"fldMonthID";
            if (!document.getElementById(fldID)) {
                fldID = "n"+nID+"fldHrID";
                if (!document.getElementById(fldID)) {
                    fldID = "n"+nID+"fldFeetID";
                    if (!document.getElementById(fldID)) {
                        fldID = "";
                    }
                }
            }
        }
    }
    if (fldID != "") document.getElementById(fldID).focus();
    return true;
}

function charCountKeyUp(nID) {
	if (document.getElementById("n"+nID+"FldID")) {
	    var count = 0;
	    if (document.getElementById("n"+nID+"FldID").value.trim() != "") {
	        count = document.getElementById("n"+nID+"FldID").value.trim().length;
        }
	    if (document.getElementById("wordCnt"+nID+"")) {
            document.getElementById("wordCnt"+nID+"").innerHTML=count;
        }
	}
	return true;
}
function keywordCountKeyUp(nID) {
	if (document.getElementById("n"+nID+"FldID")) {
	    var keywords = new Array();
	    if (document.getElementById("n"+nID+"FldID").value.trim() != "") {
	        keywords = document.getElementById("n"+nID+"FldID").value.trim().split(",");
        }
	    if (document.getElementById("keywordCnt"+nID+"")) {
            document.getElementById("keywordCnt"+nID+"").innerHTML=keywords.length;
        }
	}
	return true;
}

function wordCountKeyUp(nID, limit) {
	if (document.getElementById("n"+nID+"FldID")) {
	    var words = new Array();
	    if (document.getElementById("n"+nID+"FldID").value.trim() != "") {
	        words = document.getElementById("n"+nID+"FldID").value.trim().split(" ");
        }
	    if (limit > 0 && limit < 10000000000 && words.length > limit) {
	        var newLimited = "";
	        for (var i = 0; i < limit; i++) {
	            newLimited += " " + words[i];
	        }
	        document.getElementById("n"+nID+"FldID").value = newLimited.trim();
	    }
	    if (document.getElementById("wordCnt"+nID+"")) {
            var cntWords = "<span class=\"slRedLight\">"+words.length+"</span>";
            if (words.length < limit) cntWords = "<span class=\"slBlueDark\">"+words.length+"</span>";
            document.getElementById("wordCnt"+nID+"").innerHTML=cntWords;
        }
	}
	return true;
}

function checkMin(nID, minVal) {
	if (document.getElementById("n"+nID+"FldID")) {
	    var currVal = document.getElementById("n"+nID+"FldID");
	    if (currVal.value.trim() != '' && currVal.value < minVal) currVal.value = minVal;
	}
    return true;
}
function checkMax(nID, maxVal) {
	if (document.getElementById("n"+nID+"FldID")) {
	    var currVal = document.getElementById("n"+nID+"FldID");
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

function checkFingerClass(nID) {
    for (var j = 0; j < nodeResTot[nID]; j++) {
        if (document.getElementById("n"+nID+"fld"+j+"lab") && document.getElementById("n"+nID+"fld"+j+"")) {
            if (document.getElementById("n"+nID+"fld"+j+"").checked) {
                document.getElementById("n"+nID+"fld"+j+"lab").className = "fingerAct";
            } else {
                document.getElementById("n"+nID+"fld"+j+"lab").className = "finger";
            }
        }
    }
    return true;
}

function checkMutEx(nID, response) {
    if (nID > 0 && response > 0 && nodeMutEx[nID] && nodeMutEx[nID].length > 0) {
        var hasMutEx = false;
        var clickedMutEx = false;
        for (var i = 0; i < nodeMutEx[nID].length; i++) {
            if (nodeMutEx[nID][i] == response) {
                if (document.getElementById("n"+nID+"fld"+response+"").checked) {
                    clickedMutEx = true;
                    for (var j=0; j < nodeResTot[nID]; j++) {
                        if (j != response) {
                            document.getElementById("n"+nID+"fld"+j+"").checked = false;
                        }
                    }
                }
            }
        }
        if (!clickedMutEx) {
            for (var i=0; i < nodeMutEx[nID].length; i++) {
                if (nodeMutEx[nID][i] != response && document.getElementById("n"+nID+"fld"+response+"").checked) {
                    document.getElementById("n"+nID+"fld"+nodeMutEx[nID][i]+"").checked = false;
                }
            }
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
    return {!! json_encode($GLOBALS["SL"]->sysOpts["spinner-code"]) !!};
}
function getSpinnerAjaxWrap() {
    return {!! json_encode('<div id="ajaxWrapLoad" class="container">') !!}+getSpinner()+{!! json_encode('</div>') !!};
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
    setTimeout(function() {
        var newH = o.scrollHeight+25;
        o.style.height = (newH)+"px";
    }, 1);
}

$(document).ready(function(){
    
    function chkFormSess() {
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
        return true;
    }
    setTimeout(function() { chkFormSess(); }, (58*60000));
    
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
	
    $(document).on("click", ".upTypeBtn", function() {
		var nID = $(this).attr("name").replace("n", "").replace("fld", "");
		if (document.getElementById("n"+nID+"fld"+uploadTypeVid+"") && document.getElementById("n"+nID+"fld"+uploadTypeVid+"").checked) { // (Video)
			$("#up"+nID+"FormFile").slideUp("fast");
			$("#up"+nID+"FormVideo").slideDown("fast");
		}
		else { // not video, but file upload
			$("#up"+nID+"FormVideo").slideUp("fast");
			$("#up"+nID+"FormFile").slideDown("fast");
		}
		$("#up"+nID+"Info").slideDown("fast");
		return true;
	});
	/* $("[data-toggle=\"tooltip\"]").tooltip(); */
	
	$(document).on("click", ".navDeskMaj", function() {
		var majInd = $(this).attr("id").replace("maj", "");
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
    
    function toggleHidFld(fldGrp) {
        if (document.getElementById("hidFld"+fldGrp+"")) {
            if (document.getElementById("hidFld"+fldGrp+"").style.display!="block") {
                $("#hidFld"+fldGrp+"").slideDown("fast");
            } else {
                $("#hidFld"+fldGrp+"").slideUp("fast");
            }
        }
        return true;
    }
	$(document).on("click", ".hidFldBtn", function() {
        var fldGrp = $(this).attr("id").replace("hidFldBtn", "");
        toggleHidFld(fldGrp);
	});
	$(document).on("click", ".hidFldBtnSelf", function() {
        var fldGrp = $(this).attr("id").replace("hidFldBtn", "");
        toggleHidFld(fldGrp);
        $(this).slideUp("fast");
	});
    
    if (!document.getElementById('loginLnk')) $("#headClear").load("/js-load-menu");
    
    $(document).on("click", ".adminAboutTog", function() {
        if (document.getElementById('adminAbout')) {
            $("#adminAbout").slideToggle('slow');
        }
	});
	
	function updateImgSelect(nID) {
        if (document.getElementById("n"+nID+"FldID") && document.getElementById("n"+nID+"SelImg")) {
            var imgSrc = "";
            if (document.getElementById("n"+nID+"FldID").value.trim() != "") {
                imgSrc = document.getElementById("n"+nID+"FldID").value.trim();
            }
            document.getElementById("n"+nID+"SelImg").src=imgSrc;
        }
        return true;
    }
	$(document).on("click", ".openImgUpdate", function() {
        updateImgSelect($(this).attr("id").replace("imgUpd", "").replace("n", "").replace("FldID", ""));
	});
    function defaultImgSelect(nID) {
        if (document.getElementById("n"+nID+"FldID")) {
            document.getElementById("n"+nID+"FldID").value = defMetaImg.replace(appUrl, "");
        }
        updateImgSelect(nID);
        return true;
    }
	$(document).on("click", ".openImgReset", function() {
	    var nID = $(this).attr("id").replace("imgReset", "");
        defaultImgSelect(nID);
        updateImgSelect(nID);
	});
    function openImgSelect(nID, title, presel) {
        if (document.getElementById("dialogTitle")) document.getElementById("dialogTitle").innerHTML = title;
        $("#nondialog").fadeOut(300);
        window.scrollTo(0, 0);
        $("#dialogBody").load("/ajax/img-sel?nID="+nID+"&presel="+encodeURIComponent(presel));
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
    function openImgDetail(nID, imgID) {
        if (document.getElementById("imgDeetDiv"+nID+"")) {
            document.getElementById("imgDeetDiv"+nID+"").innerHTML = '<center>'+getSpinner()+'</center>';
            document.getElementById("hidFldImgUp"+nID+"").style.display = "none";
            document.getElementById("hidFldBtnImgUp"+nID+"").style.display = "block";
            $("#imgDeetDiv"+nID+"").load( "/ajax/img-deet?nID="+nID+"&imgID="+imgID+"" );
        }
        return true;
    }
	$(document).on("click", ".openImgDetail", function() {
	    var ids = $(this).attr("id").replace("selectImg", "").split("sel");
        openImgDetail(ids[0], ids[1]);
	});
    function getImgNode(imgID) {
	    var nID = "";
	    if (document.getElementById("imgNode"+imgID+"ID")) nID = document.getElementById("imgNode"+imgID+"ID").value;
	    return nID;
    }
    function imgChoose(imgID) {
        var nID = getImgNode(imgID);
        $("#nondialog").fadeIn(300);
        $("#dialog").fadeOut(300);
	    var url = "";
	    if (document.getElementById("imgUrl"+imgID+"ID")) url = document.getElementById("imgUrl"+imgID+"ID").value;
        if (document.getElementById("n"+nID+"FldID")) {
            document.getElementById("n"+nID+"FldID").value=url;
        }
        updateImgSelect(nID);
        return true;
    }
	$(document).on("click", ".imgChoose", function() {
	    imgChoose($(this).attr("id").replace("imgChoose", ""));
	});
    function imgSaveDeet(imgID) {
        var nID = getImgNode(imgID);
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
    function imgUpBtn(nID) {
        if (document.getElementById("img"+nID+"fileUpdate")) {
            document.getElementById("img"+nID+"fileUpdate").innerHTML='<center>'+getSpinner()+'</center>';
            var formData = new FormData(document.getElementById("formUpImg"+nID+"ID"));
            $.ajax({
                url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-up",
                type: "POST", 
                data: formData, 
                contentType: false,
                processData: false,
                success: function(data) {
                    $("#img"+nID+"fileUpdate").empty();
                    $("#img"+nID+"fileUpdate").append(data);
                }, 
                error: function(xhr, status, error) {
                    $("#img"+nID+"fileUpdate").append("<div>(error - "+xhr.responseText+")</div>");
                }
            });
        }
        return true;
    }
	$(document).on("click", ".imgUpBtn", function() {
	    imgUpBtn($(this).attr("id").replace("imgUp", ""));
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
function addTopNavItem(navTxt, navLink) {
    if (document.getElementById("myNavBarIn")) {
        document.getElementById("myNavBarIn").innerHTML += "<a class=\"pull-right slNavLnk\" href=\""+navLink+"\">"+navTxt+"</a>";
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

function hideRightSide() {
	if (document.getElementById("mainBody")) document.getElementById("mainBody").className="col-md-10";
	if (document.getElementById("rightSide")) document.getElementById("rightSide").className="disNon";
	return true;
}
function showRightSide() {
	if (document.getElementById("mainBody")) document.getElementById("mainBody").className="col-md-7";
	if (document.getElementById("rightSide")) document.getElementById("rightSide").className="col-md-3";
	return true;
}


@if (isset($jsXtra)) {!! $jsXtra !!} @endif
