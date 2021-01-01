/* generated from resources/views/vendor/survloop/js/scripts-forms.blade.php */

var treeID = 0;
var treeType = "";

var foundForm = true;
var formActionUrl = "/sub";
var pressedSubmit = false;
var hasAttemptedSubmit = false;
var otherFormSub = false;
var checkingForm = false;
var totFormErrors = 0;
var formErrorsEng = "";
var firstNodeError = "";
var errorFocus = [];

var allFldList = [];
var nodeList = [];
var nodeParents = [];
var nodeKidList = [];
var nodeTblList = [];
var nodeSffxs = new Array("");
var conditionNodes = [];
var radioNodes = [];
var reqNodes = [];
var nodeMobile = [];
var nodeTags = [];
var nodeTagList = [];

var nodeResTot = {};
var nodeMutEx = {};

var loopItemsNextID = 0;
var currItemCnt = 0;
var addingLoopItem = 0;
var maxItemCnt = 0;
var uploadTypeVid = -1;

var autoSaveDelay = 60000;

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

function addIsMobile(nID, isMobile) {
    if (nID > 0) {
        nodeMobile[nID] = isMobile;
    }
    return true;
}

function setFormErrs() {
	if (document.getElementById("formErrorMsg")) {
        var errTxt = "<b>Oops, you missed a required field";
        formErrorsEng = formErrorsEng.trim();
        if (formErrorsEng.length == 0) {
            errTxt += " "+formErrorsEng;
        }
	    document.getElementById("formErrorMsg").innerHTML = errTxt+"</b>";
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

function stripN(nIDtxt) {
    if (nIDtxt.substring(0, 1) == "n") {
        nIDtxt = nIDtxt.substring(1);
    }
    return nIDtxt;
}
function stripNodeFromFldID(fldID) {
    return stripN(fldID).replace("FldID", "");
}

function txt2nID(nIDtxt) {
    nIDtxt = stripN(nIDtxt);
    if (nIDtxt.indexOf('cyc') > 0) {
        return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('cyc'))));
    }
    if (nIDtxt.indexOf('res') > 0) {
        return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('res'))));
    }
    if (nIDtxt.indexOf('tbl') > 0) {
        return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('tbl'))));
    }
    return parseInt(nIDtxt);
}

function getNodeAndResFromFldID(nFldID) {
    var nodeAndRes = new Array('', -1);
    if (nFldID.substring(0, 1) == 'n' && nFldID.indexOf('radioCurrID') < 0 && nFldID.indexOf('fldOtherID') < 0) {
        if (nFldID.indexOf('FldID') > 0) {
            nodeAndRes[0] = nFldID.substring(1, nFldID.indexOf('FldID'));
        } else if (nFldID.indexOf('fld') > 0) {
            nodeAndRes[0] = nFldID.substring(1, nFldID.indexOf('fld'));
            nodeAndRes[1] = parseInt(nFldID.substring(3+nFldID.indexOf('fld')));
        }
    }
    return nodeAndRes;
}

function getReqNodeInd(nIDtxt) {
    var reqInd = -1;
    for (var i = 0; i < reqNodes.length; i++) {
        if (reqNodes[i][0] == nIDtxt) reqInd = i;
    }
    if (reqInd < 0) reqInd = reqNodes.length;
    addHshoo("#n"+nIDtxt+"");
	return reqInd;
}
function addReqNode(nIDtxt, type) {
    reqNodes[getReqNodeInd(nIDtxt)] = new Array(nIDtxt, type);
    return true;
}
function addReqNodeRadio(nIDtxt, type, max) {
    reqNodes[getReqNodeInd(nIDtxt)] = new Array(nIDtxt, type, max);
    return true;
}
function addReqNodeRadioCustom(nIDtxt, type, idList) {
    reqNodes[getReqNodeInd(nIDtxt)] = new Array(nIDtxt, type, idList);
    return true;
}
function addReqNodeTbl(nID, nIDtxt, type, max, cols, req) {
    reqNodes[getReqNodeInd(nIDtxt)] = new Array(nIDtxt, type, nID, max, cols, req);
    return true;
}
function addReqNodeDateLimit(nIDtxt, type, max, date, optional) {
    reqNodes[getReqNodeInd(nIDtxt)] = new Array(nIDtxt, type, max, date, optional);
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
    if (document.getElementById(nIDtxt+"fld"+response) && document.getElementById(nIDtxt+"radioCurrID")) {
        if (document.getElementById(nIDtxt+"fld"+response).value != document.getElementById(nIDtxt+"radioCurrID").value) {
            document.getElementById(nIDtxt+"radioCurrID").value = document.getElementById(nIDtxt+"fld"+response+"").value;
        } else {
            document.getElementById(nIDtxt+"fld"+response+"").checked = false;
            document.getElementById(nIDtxt+"radioCurrID").value = "";
        }
        checkFingerClass(nIDtxt);
    }
    return true;
}

var monthAbbr = [];
@for ($m = 1; $m <= 12; $m++)
    monthAbbr[{{ $m }}] = "{{ date("M", mktime(0, 0, 0, $m, 1, 2000)) }}";
@endfor

function checkFldDate(nIDtxt) {
    return (document.getElementById("n"+nIDtxt+"fldYearID").value != "0000" 
	    && document.getElementById("n"+nIDtxt+"fldMonthID").value != "00" 
	    && document.getElementById("n"+nIDtxt+"fldDayID").value != "00");
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

var gryFlds = [];
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
	    var keywords = [];
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
	    var words = [];
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
            if (0 < warn && warn <= words.length) cntWords = "<span class=\"txtDanger\">"+words.length+"</span>";
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

function addResTot(nIDtxt, tot) {
    nodeResTot[nIDtxt] = tot;
    return true;
}

function checkFingerClassTime(nIDtxt) {
    var printNidtxt = nIDtxt;
    if (nIDtxt.substring(0, 1) != 'n') {
        printNidtxt = "n"+nIDtxt+"";
    }
    for (var j = 0; j < nodeResTot[nIDtxt]; j++) {
        var fld = ""+printNidtxt+"fld"+j+"";
        if (document.getElementById(fld+"lab")) {
            if (document.getElementById(fld) && document.getElementById(fld).checked) {
                document.getElementById(fld+"lab").className = "fingerAct";
            } else {
                document.getElementById(fld+"lab").className = "finger";
            }
        }
    }
    return true;
}

function checkFingerClass(nIDtxt) {
    setTimeout("checkFingerClassTime('"+nIDtxt+"')", 100);
    return true;
}

function addMutEx(nIDtxt, response) {
    if (!(nIDtxt in nodeMutEx)) nodeMutEx[nIDtxt] = [];
    nodeMutEx[nIDtxt][nodeMutEx[nIDtxt].length] = response;
    return true;
}

function checkMutEx(nIDtxt, response) {
    if (nID > 0 && response >= 0 && nodeMutEx[nIDtxt] && nodeMutEx[nIDtxt].length > 0) {
        var clickedMutEx = false;
        for (var i = 0; i < nodeMutEx[nIDtxt].length; i++) {
            if (nodeMutEx[nIDtxt][i] == response && document.getElementById("n"+nIDtxt+"fld"+response+"") && document.getElementById("n"+nIDtxt+"fld"+response+"").checked) {
                clickedMutEx = true;
                for (var j=0; j < nodeResTot[nIDtxt]; j++) {
                    if (j != response && document.getElementById("n"+nIDtxt+"fld"+j+"")) {
                        document.getElementById("n"+nIDtxt+"fld"+j+"").checked = false;
                    }
                }
            }
        }
        if (!clickedMutEx) {
            for (var i=0; i < nodeMutEx[nIDtxt].length; i++) {
                if (nodeMutEx[nIDtxt][i] != response && document.getElementById("n"+nIDtxt+"fld"+nodeMutEx[nIDtxt][i]+"") && document.getElementById("n"+nIDtxt+"fld"+response+"") && document.getElementById("n"+nIDtxt+"fld"+response+"").checked) {
                    document.getElementById("n"+nIDtxt+"fld"+nodeMutEx[nIDtxt][i]+"").checked = false;
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
    var foundCheck = false;
    for (var j = 0; j < nodeResTot[nIDtxt]; j++) {
        if (document.getElementById("n"+nIDtxt+"fld"+j+"")) {
            if (document.getElementById("n"+nIDtxt+"fld"+j+"").checked) foundCheck = true;
        }
    }
    for (var j = 0; j < nodeResTot[nIDtxt]; j++) {
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
    if (document.getElementById("radioUnHide"+nIDtxt+"")) {
        document.getElementById("radioUnHide"+nIDtxt+"").style.display="none";
    }
    for (var j = 0; j < nodeResTot[nIDtxt]; j++) {
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

function getTagNodeInd(nIDtxt) {
    for (var i = 0; i < nodeTags.length; i++) {
        if (nodeTags[i][0] == nIDtxt) return i;
    }
    return -1;
}

function addTagOpt(nIDtxt, tagID, tagText, preSel) {
    return addTagOptExtra(nIDtxt, tagID, tagText, preSel, '');
}

function addTagOptExtra(nIDtxt, tagID, tagText, preSel, classExtra) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd < 0) {
        nodeInd = nodeTags.length;
        nodeTags[nodeInd] = new Array(nIDtxt, []);
        nodeTagList[nodeInd] = [];
    }
    var found = false;
    for (var t=0; t < nodeTags[nodeInd][1].length; t++) {
        if (nodeTags[nodeInd][1][t][0] == tagID) {
            found = true;
        }
    }
    if (!found) {
        nodeTags[nodeInd][1][nodeTags[nodeInd][1].length] = new Array(tagID, tagText, preSel, classExtra);
    }
    nodeTagList[nodeInd][nodeTagList[nodeInd].length] = tagID;
    return true;
}

function getTagInd(nodeInd, tagID) {
    var tagInd = -1;
    for (var t=0; t < nodeTags[nodeInd][1].length; t++) {
        if (nodeTags[nodeInd][1][t][0] == tagID) {
            tagInd = t;
        }
    }
    return tagInd;
}

function selectTag(nIDtxt, tagID) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd >= 0 && nodeTags[nodeInd] && nodeTags[nodeInd][1].length > 0) {
        var tagInd = getTagInd(nodeInd, tagID);
        if (tagInd >= 0) nodeTags[nodeInd][1][tagInd][2] = 1;
    }
    updateTagList(nIDtxt);
    return true;
}

function deselectTag(nIDtxt, tagID) {
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeInd >= 0 && nodeTags[nodeInd] && nodeTags[nodeInd][1].length > 0) {
        var tagInd = getTagInd(nodeInd, tagID);
        if (tagInd >= 0) nodeTags[nodeInd][1][tagInd][2] = 0;
    }
    updateTagList(nIDtxt);
    return false;
}

function printTag(nIDtxt, tagID, tagText) {
    return printTagExtra(nIDtxt, tagID, tagText, '');
}

function printTagExtra(nIDtxt, tagID, tagText, classExtra) {
    return "<a data-tag-nid=\""+nIDtxt+"\" data-tag-id=\""+tagID+"\" class=\"btn btn-primary formTagDeselect "+classExtra+" \" href=\"javascript:;\">"+tagText+"<i class=\"fa fa-times\" aria-hidden=\"true\"></i></a> ";
}

function updateTagList(nIDtxt) {
    var tagIDs = ",";
    var tagHtml = "";
    var nodeInd = getTagNodeInd(nIDtxt);
    if (nodeTags[nodeInd] && nodeTagList[nodeInd]) {
        for (var i = 0; i < nodeTagList[nodeInd].length; i++) {
            var tagID = nodeTagList[nodeInd][i];
            var tagInd = getTagInd(nodeInd, tagID);
            if (tagInd >= 0 && nodeTags[nodeInd][1][tagInd][2] == 1 && tagIDs.indexOf(","+tagID+",") < 0) {
                tagIDs += tagID+",";
                tagHtml += printTagExtra(nIDtxt, tagID, nodeTags[nodeInd][1][tagInd][1], nodeTags[nodeInd][1][tagInd][3]);
            }
        }
    }
    if (document.getElementById("n"+nIDtxt+"tagIDsID")) {
        document.getElementById("n"+nIDtxt+"tagIDsID").value=tagIDs;
    }
    if (document.getElementById("n"+nIDtxt+"tags")) {
        document.getElementById("n"+nIDtxt+"tags").innerHTML=tagHtml;
    }
    return true;
}

function disableElement(fldID) {
    if (document.getElementById(fldID)) {
        document.getElementById(fldID).style.background = "#DDD";
        document.getElementById(fldID).style.color = "#AAA";
        if (document.getElementById(fldID).disabled) {
            document.getElementById(fldID).disabled = true;
        }
    }
    return true;
}

var disableSpreadKids = [];
function disableSpreadsheetRow(nID, row) {
    var rowID = "n"+nID+"tbl"+row+"row";
    disableElement(rowID);
    if (nodeKidList[nID] && nodeKidList[nID].length > 0) {
        for (var k = 0; k < nodeKidList[nID].length; k++) {
            disableSprdRowKids(nID, row, nodeKidList[nID][k]);
        }
    }
    if (disableSpreadKids[nID] && disableSpreadKids[nID].length > 0) {
        for (var k = 0; k < disableSpreadKids[nID].length; k++) {
            disableSprdRowKids(nID, row, disableSpreadKids[nID][k]);
        }
    }
    return true;
}
function disableSprdRowKids(nID, row, kid) {
    var fldID = "n"+kid+"tbl"+row+"FldID";
    disableElement(fldID);
    fldID = "n"+kid+"tbl"+row+"VisibleID";
    if (document.getElementById(fldID)) {
        document.getElementById(fldID).value = 0;
    }
    return true;
}

function styBlock(id) {
    if (document.getElementById(id)) document.getElementById(id).style.display="block";
    if (document.getElementById(id+"kids")) document.getElementById(id+"kids").style.display="block";
}

function styNone(id) {
    if (document.getElementById(id)) document.getElementById(id).style.display="none";
    if (document.getElementById(id+"kids")) document.getElementById(id+"kids").style.display="none";
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
function setNodeVisibTxt(nIDtxt, onOff) {
    if (document.getElementById("n"+nIDtxt+"VisibleID")) {
        if (onOff) document.getElementById("n"+nIDtxt+"VisibleID").value=1;
        else document.getElementById("n"+nIDtxt+"VisibleID").value=0;
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
/* console.log("node kids[k] = "+kids[k]+" , nSffx = "+nSffx+""); */
/* will need styFlex( */
                    styBlock("node"+kids[k]+nSffx+"");
                } else {
                    styNone("node"+kids[k]+nSffx+"");
                }
            }
            kidsVisible(kids[k], nSffx, onOff);
            checkFingerClass(kids[k]+nSffx);
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

function changeLoopListType(fld) {
    if (document.getElementById(''+fld+'TypeID')) {
        if (document.getElementById(''+fld+'TypeID').value == 'manual') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
            document.getElementById(''+fld+'TblCond').style.display = 'none';
            document.getElementById(''+fld+'Months').style.display = 'none';
            document.getElementById(''+fld+'DefinitionID').value='';
            document.getElementById(''+fld+'LoopItemsID').value='';
            document.getElementById(''+fld+'TablesID').value='';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-def') {
            document.getElementById(''+fld+'Defs').style.display = 'block';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
            document.getElementById(''+fld+'Months').style.display = 'none';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-loop') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'block';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
            document.getElementById(''+fld+'Months').style.display = 'none';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-tbl' || document.getElementById(''+fld+'TypeID').value == 'auto-tbl-all') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'block';
            if (document.getElementById(''+fld+'TypeID').value == 'auto-tbl-all') {
                document.getElementById(''+fld+'TblCond').style.display = 'block';
            }
            document.getElementById(''+fld+'Months').style.display = 'none';
        } else if (document.getElementById(''+fld+'TypeID').value == 'auto-months') {
            document.getElementById(''+fld+'Defs').style.display = 'none';
            document.getElementById(''+fld+'Loops').style.display = 'none';
            document.getElementById(''+fld+'Tbls').style.display = 'none';
            document.getElementById(''+fld+'TblCond').style.display = 'none';
            document.getElementById(''+fld+'Months').style.display = 'block';
        }
    }
    return true;
}

function checkBoxAll(fldBase, count, isChecked) {
    for (var i = 0; i < count; i++) {
        var fldID = fldBase.concat(i);
        if (document.getElementById(fldID)) document.getElementById(fldID).checked = isChecked;
    }
    return true;
}

function setLoopItemID(itemID) {
    if (document.getElementById("loopItemID")) document.getElementById("loopItemID").value=itemID;
    return true;
}

function topSearchFocus() {
    if (document.getElementById('admSrchFld')) {
        setTimeout("document.getElementById('admSrchFld').focus()", 100);
    }
    return true;
}
function setSearchResultsDiv(divID) {
    if (document.getElementById("sResultsDivID")) document.getElementById("sResultsDivID").value=divID;
    return true;
}
function setSearchResultsUrlBase(url) {
    if (document.getElementById("sResultsUrlID")) document.getElementById("sResultsUrlID").value=url;
    return true;
}
function searchDataSetsChecked() {
    var tblChecked = "";
    for (var i=0 ; i < 20; i++) {
        var fldID = "srchBarDataSet"+i+"";
        if (document.getElementById(fldID) && document.getElementById(fldID).checked) {
            tblChecked = ","+document.getElementById(fldID).value+"";
        }
    }
    return true;
}
function multiSearchDataSetsChecked() {
    var tblChecked = searchDataSetsChecked();
    return (tblChecked.indexOf(",") > 0);
}
function didSearchDataSetChange() {
    if (document.getElementById("sPrevSearchTblsID")) {
        var tblChecked = searchDataSetsChecked();
        if (document.getElementById("sPrevSearchTblsID").value.localeCompare(tblChecked) != 0) {
            return true;
        }
    }
    return false;
}

function ReqGreaterThan() {
    var obj = {};
    obj.checks = [];
    obj.add = function(fldMin, fldMax) {
        var ind = obj.checks.length;
        obj.checks[ind] = {
            fldMin : fldMin,
            fldMax : fldMax
        };
    };
    obj.validateAll = function() {
        var results = {
            nIDgood : [],
            nIDbad : []
        };
        if (obj.checks.length > 0) {
            for (var i=0; i < obj.checks.length; i++) {
                if (document.getElementById(obj.checks[i].fldMin) && document.getElementById(obj.checks[i].fldMax) && document.getElementById(obj.checks[i].fldMin).value.trim() != '' && document.getElementById(obj.checks[i].fldMax).value.trim() != '') {
                    if (parseInt(document.getElementById(obj.checks[i].fldMin).value) > parseInt(document.getElementById(obj.checks[i].fldMax).value)) {
                        results.nIDbad[results.nIDbad.length]=obj.checks[i].fldMin;
                        results.nIDbad[results.nIDbad.length]=obj.checks[i].fldMax;
                    } else {
                        results.nIDgood[results.nIDgood.length]=obj.checks[i].fldMin;
                        results.nIDgood[results.nIDgood.length]=obj.checks[i].fldMax;
                    }
                } else {
                    results.nIDgood[results.nIDgood.length]=obj.checks[i].fldMin;
                    results.nIDgood[results.nIDgood.length]=obj.checks[i].fldMax;
                }
            }
        }
        return results;
    };
    return obj;
}
var reqGreaterThan = new ReqGreaterThan();
function addReqGreaterThan(fldMin, fldMax) {
    if (document.getElementById(fldMin) && document.getElementById(fldMax)) {
        reqGreaterThan.add(fldMin, fldMax);
        return true;
    }
    return false;
}
function addReqGreaterThanNodes(nIDtxtMin, nIDtxtMax) {
    return addReqGreaterThan("n"+nIDtxtMin+"FldID", "n"+nIDtxtMax+"FldID");
}
function addReqGreaterThanNodesCycle(nIDtxtMin, nIDtxtMax, cnt) {
    for (var i=0; i < cnt; i++) {
        addReqGreaterThanNodes(nIDtxtMin+"cyc"+i, nIDtxtMax+"cyc"+i);
    }
    return true;
}
// For now, manually load this via some JS in the Node, e.g. a cycle of up to four loops:
// addReqGreaterThanNodesCycle('1016', '1553', 4);

