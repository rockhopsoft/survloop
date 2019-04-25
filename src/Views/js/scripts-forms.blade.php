/* generated from resources/views/vendor/survloop/js/scripts-forms.blade.php */

var foundForm = true;
var formActionUrl = "/sub";
var pressedSubmit = false;
var hasAttemptedSubmit = false;
var otherFormSub = false;
var checkingForm = false;
var totFormErrors = 0;
var formErrorsEng = "";
var firstNodeError = "";
var errorFocus = new Array();

var allFldList = new Array();
var nodeList = new Array();
var nodeParents = new Array();
var nodeKidList = new Array();
var nodeTblList = new Array();
var nodeSffxs = new Array("");
var conditionNodes = new Array();
var radioNodes = new Array();
var nodeResTot = new Array();
var nodeMutEx = new Array();
var reqNodes = new Array();
var nodeMobile = new Array();
var nodeTags = new Array();
var nodeTagList = new Array();

var loopItemsNextID = 0;
var currItemCnt = 0;
var maxItemCnt = 0;
var uploadTypeVid = -1;

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

function stripN(nIDtxt) {
    if (nIDtxt.substring(0, 1) == "n") nIDtxt = nIDtxt.substring(1);
    return nIDtxt;
}

function txt2nID(nIDtxt) {
    nIDtxt = stripN(nIDtxt);
    if (nIDtxt.indexOf('cyc') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('cyc'))));
    if (nIDtxt.indexOf('res') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('res'))));
    if (nIDtxt.indexOf('tbl') > 0) return parseInt(nIDtxt.substring(0, (1+nIDtxt.indexOf('tbl'))));
    return parseInt(nIDtxt);
}

function getNodeAndResFromFldID(nFldID) {
    var nodeAndRes = new Array('', -1);
    if (nFldID.substring(0, 1) == 'n' && nFldID.indexOf('radioCurrID') < 0 && nFldID.indexOf('fldOtherID') < 0) {
        if (nFldID.indexOf('FldID') > 0) {
            nodeAndRes[0] = nFldID.substring(0, nFldID.indexOf('FldID'));
        } else if (nFldID.indexOf('fld') > 0) {
            nodeAndRes[0] = nFldID.substring(0, nFldID.indexOf('fld'));
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

function addResTot(nID, tot) {
    nodeResTot[nID] = tot;
    return true;
}

function addMutEx(nID, response) {
    if (!nodeMutEx[nID]) nodeMutEx[nID] = new Array();
    nodeMutEx[nID][nodeMutEx[nID].length] = response;
    return true;
}

function checkFingerClass(nIDtxt) {
    for (var j = 0; j < nodeResTot[stripN(nIDtxt)]; j++) {
        if (document.getElementById(nIDtxt+"fld"+j+"lab") && document.getElementById(nIDtxt+"fld"+j+"")) {
            if (document.getElementById(nIDtxt+"fld"+j+"").checked) {
                document.getElementById(nIDtxt+"fld"+j+"lab").className = "fingerAct";
            } else {
                document.getElementById(nIDtxt+"fld"+j+"lab").className = "finger";
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
/* console.log("node kids[k] = "+kids[k]+" , nSffx = "+nSffx+""); */
/* will need styFlex( */
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

function checkBoxAll(fldBase, count, isChecked) {
    for (var i = 0; i < count; i++) {
        var fldID = fldBase.concat(i);
        if (document.getElementById(fldID)) document.getElementById(fldID).checked = isChecked;
    }
    return true;
}
