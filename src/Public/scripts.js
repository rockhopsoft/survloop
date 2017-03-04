var allFldList = new Array();
function addFld(fld) {
	allFldList[allFldList.length] = fld;
	return true;
}
function blurAllFlds() {
	for (var i=0; i<allFldList.length; i++) {
		if (document.getElementById(allFldList[i])) document.getElementById(allFldList[i]).blur();
	}
	return true;
}

var foundForm = true;
function checkForm() {
	if (!foundForm) {
		if (document.getElementById('postNodeForm')) foundForm = true;
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
var formErrorsEng = '';

function chkFormCheck() {
    if (hasAttemptedSubmit) return checkNodeForm();
    return false;
}

function setFormErrs() {
	if (document.getElementById('formErrorMsg')) {
	    document.getElementById('formErrorMsg').innerHTML = '<h2>Please complete all required fields. <i class="fa fa-arrow-up"></i>'+formErrorsEng+'</h2>';
	    document.getElementById('formErrorMsg').style.display = 'block';
	}
	return true;
}
function clearFormErrs() {
	if (document.getElementById('formErrorMsg')) {
	    document.getElementById('formErrorMsg').innerHTML = '';
	    document.getElementById('formErrorMsg').style.display = 'none';
	}
	return true;
}

function setFormLabelBlack(nID) {
	if (document.getElementById('node'+nID+'')) {
	    document.getElementById('node'+nID+'').className=document.getElementById('node'+nID+'').className.replace('nodeWrapError', 'nodeWrap');
	}
	return true;
}

var firstNodeError = 0;
function setFormLabelRed(nID) {
    if (firstNodeError <= 0) {
        firstNodeError = nID;
        window.location='#n'+nID+'';
    }
	if (document.getElementById('node'+nID+'')) {
	    document.getElementById('node'+nID+'').className=document.getElementById('node'+nID+'').className.replace('nodeWrap', 'nodeWrapError').replace('nodeWrapErrorError', 'nodeWrapError');
	}
	return true;
}

function reqFormEmail(FldName) {
	if (document.getElementById(FldName)) {
		if (document.getElementById(FldName).value.trim() == '') return false;
		var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (!re.test(document.getElementById(FldName).value)) return false;
	}
	return true;
}

function reqFormTxt(fldID, nID) {
	if (document.getElementById(fldID) && document.getElementById(fldID).value.trim() == '') {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}
function reqFormFld(nID) {
	if (document.getElementById('n'+nID+'FldID') && document.getElementById('n'+nID+'FldID').value.trim() == '') {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}
function reqFormFldEmail(nID) {
	if (!reqFormEmail('n'+nID+'FldID')) {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}
function reqFormFldRadio(nID, maxOpts) {
	var foundCheck = false;
	for (var j=0; j<maxOpts; j++) {
		if (document.getElementById('n'+nID+'fld'+j+'') && document.getElementById('n'+nID+'fld'+j+'').checked) foundCheck = true;
	}
	if (!foundCheck) {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function checkFldDate(nID) {
    return (document.getElementById('n'+nID+'fldYearID').value != '0000' 
	    && document.getElementById('n'+nID+'fldMonthID').value != '00' 
	    && document.getElementById('n'+nID+'fldDayID').value != '00');
}

function reqFormFldDate(nID) {
	if (!checkFldDate(nID)) {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function reqFormFldDateAndLimit(nID, future, today, optional) {
	if (!checkFldDate(nID) || !chkFormFldDateLimit(nID, future, today, optional)) {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function reqFormFldDateLimit(nID, future, today, optional) {
	if (!chkFormFldDateLimit(nID, future, today, optional)) {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function chkFormFldDateLimit(nID, future, today, optional) {
    if (future == 0) return true;
    validDate = true;
    if (optional == 1 && !checkFldDate(nID)) { }
    else if (checkFldDate(nID)) {
        var todayYear = parseInt(today.substring(0, 4));
        var todayMonth = parseInt(today.substring(5, 7));
        var todayDay = parseInt(today.substring(8, 10));
        var userYear = parseInt(document.getElementById('n'+nID+'fldYearID').value);
        var userMonth = parseInt(document.getElementById('n'+nID+'fldMonthID').value);
        var userDay = parseInt(document.getElementById('n'+nID+'fldDayID').value);
        if (future < 0) { // the past is valid
            if (userYear > todayYear) validDate = false;
            else if (userYear == todayYear) {
                if (userMonth > todayMonth) validDate = false;
                else if (userMonth == todayMonth) {
                    if (userDay > todayDay) validDate = false;
                }
            }
        } else { // the future is valid
            if (userYear < todayYear) validDate = false;
            else if (userYear == todayYear) {
                if (userMonth < todayMonth) validDate = false;
                else if (userMonth == todayMonth) {
                    if (userDay < todayDay) validDate = false;
                }
            }
        }
    }
    else validDate = false;
    return validDate;
}

function charLimit(nID, limit) {
	if (document.getElementById('n'+nID+'FldID').value.length > limit) {
		document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'FldID').value.substring(0, limit);
	}
	var charRemain = limit-document.getElementById('n'+nID+'FldID').value.length;
	document.getElementById('charLimit'+nID+'Msg').innerHTML = limit+' Character Limit: '+charRemain+' Remaining';
	return true;
}

function formDateChange(nID) {
	document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'fldYearID').value+'-'+document.getElementById('n'+nID+'fldMonthID').value+'-'+document.getElementById('n'+nID+'fldDayID').value;
	chkFormCheck();
	return true;
}

function dateKeyUp(nID, which) {
	document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'fldMonthID').value+'/'+document.getElementById('n'+nID+'fldDayID').value+'/'+document.getElementById('n'+nID+'fldYearID').value;
	chkFormCheck();
	return true;
}


function formChangeFeetInches(nID) {
	if (document.getElementById('n'+nID+'FldID')) document.getElementById('n'+nID+'FldID').value = (12*parseInt(document.getElementById('n'+nID+'fldFeetID').value))+parseInt(document.getElementById('n'+nID+'fldInchID').value);
	chkFormCheck();
	return true;
}
function formRequireFeetInches(nID) {
	if (document.getElementById('n'+nID+'fldFeetID').value.trim() == '' || document.getElementById('n'+nID+'fldInchID').value.trim() == '') {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function formRequireGender(nID) {
	if (document.getElementById('n'+nID+'fld2') && document.getElementById('n'+nID+'fld2').value == '?') {
		return reqFormFldRadio(nID, 4);  // we also have 'Not Sure'
	}
	return reqFormFldRadio(nID, 3);
}

function checkNodeUp(nID, response, isMobile) {
    checkMutEx(nID, response);
    if (isMobile == 1) checkFingerClass(nID);
	chkFormCheck();
    return true;
}

function formKeyUpOther(nID, j) {
    if (document.getElementById('n'+nID+'fldOtherID').value.trim() != '') {
        document.getElementById('n'+nID+'fld'+j+'').checked=true;
        checkFingerClass(nID);
    }
	chkFormCheck();
    return true;
}

function formClickGender(nID) {
    if (document.getElementById('n'+nID+'fldOtherID')) {
        if ((document.getElementById('n'+nID+'fld0') && document.getElementById('n'+nID+'fld0').checked)
            || (document.getElementById('n'+nID+'fld1') && document.getElementById('n'+nID+'fld1').checked)
            || (document.getElementById('n'+nID+'fld3') && document.getElementById('n'+nID+'fld3').checked)) {
            document.getElementById('n'+nID+'fldOtherID').value = '';
        }
    }
	chkFormCheck();
    return true;
}

function focusNodeID(nID) {
    var fldID = 'n'+nID+'FldID';
    if (!document.getElementById(fldID)) {
        fldID = 'n'+nID+'fld0';
        if (!document.getElementById(fldID)) {
            fldID = 'n'+nID+'fldMonthID';
            if (!document.getElementById(fldID)) {
                fldID = 'n'+nID+'fldHrID';
                if (!document.getElementById(fldID)) {
                    fldID = 'n'+nID+'fldFeetID';
                    if (!document.getElementById(fldID)) {
                        fldID = '';
                    }
                }
            }
        }
    }
    if (fldID != '') document.getElementById(fldID).focus();
    return true;
}

function wordCountKeyUp(nID, limit) {
	if (document.getElementById("n"+nID+"FldID") && document.getElementById("wordCnt"+nID+"")) {
	  var cnt = getWordCnt(document.getElementById("n"+nID+"FldID"));
	  var cntWords = "<span class=\'slRedLight\'>"+cnt+"</span>";
	  if (cnt < limit) cntWords = "<span class=\'slBlueLight\'>"+cnt+"</span>";
	  document.getElementById("wordCnt"+nID+"").innerHTML=cntWords;
	}
	return true;
}

function nFldHP(nID) {
	if (document.getElementById('node'+nID+'')) {
		document.getElementById('node'+nID+'').style.display='none';
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
    for (var j=0; j<nodeResTot[nID]; j++) {
        if (document.getElementById('n'+nID+'fld'+j+'lab') 
            && document.getElementById('n'+nID+'fld'+j+'')) {
            if (document.getElementById('n'+nID+'fld'+j+'').checked) {
                document.getElementById('n'+nID+'fld'+j+'lab').className = 'fingerAct';
            }
            else document.getElementById('n'+nID+'fld'+j+'lab').className = 'finger';
        }
    }
    return true;
}

function checkMutEx(nID, response) {
    if (nID > 0 && response > 0 && nodeMutEx[nID] && nodeMutEx[nID].length > 0) {
        var hasMutEx = false;
        var clickedMutEx = false;
        for (var i=0; i<nodeMutEx[nID].length; i++) {
            if (nodeMutEx[nID][i] == response) {
                if (document.getElementById('n'+nID+'fld'+response+'').checked) {
                    clickedMutEx = true;
                    for (var j=0; j<nodeResTot[nID]; j++) {
                        if (j != response) {
                            document.getElementById('n'+nID+'fld'+j+'').checked = false;
                        }
                    }
                }
            }
        }
        if (!clickedMutEx) {
            for (var i=0; i<nodeMutEx[nID].length; i++) {
                if (nodeMutEx[nID][i] != response && document.getElementById('n'+nID+'fld'+response+'').checked) {
                    document.getElementById('n'+nID+'fld'+nodeMutEx[nID][i]+'').checked = false;
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

// used by form generator child reveal responsiveness:
var nodeKidList = new Array();
var conditionNodes = new Array();

function kidsVisible(nID, onOff, isFirst) {
	if (!isFirst && conditionNodes[nID]) return true;
	isFirst = false;
	if (nodeKidList[nID] && nodeKidList[nID].length > 0) {
		for (var k=0; k < nodeKidList[nID].length; k++) {
			setNodeVisib(nodeKidList[nID][k], onOff);
			kidsVisible(nodeKidList[nID][k], onOff, isFirst);
		}
	}
	return true;
}

function setNodeVisib(nID, onOff) {
	if (document.getElementById("n"+nID+"VisibleID")) {
		if (onOff) document.getElementById("n"+nID+"VisibleID").value=1;
		else document.getElementById("n"+nID+"VisibleID").value=0;
	}
	return true;
}


function getWordCnt(strIn) {
	if (strIn.value.trim() == '') return 0;
	return strIn.value.trim().split(' ').length;
}


function ajaxSearchExpandResults() {
	if (document.getElementById('ajaxSearchResults').className=='ajaxSearch') document.getElementById('ajaxSearchResults').className='ajaxSearchExpand';
	else document.getElementById('ajaxSearchResults').className='ajaxSearch';
	return true;
}





function reqUploadTitle(nID) {
	var labelID = parseInt('100'+nID+'');
	/* if ((document.getElementById('up'+nID+'FileID').value != "" || document.getElementById('up'+nID+'VidID') != "")
		&& document.getElementById('up'+nID+'TitleID').value.trim() == '') {
		setFormLabelRed(labelID);
		totFormErrors++;
	}
	else setFormLabelBlack(labelID); */
	return true;
}

function checkAjaxLoad() {
    if (document.getElementById('ajaxWrapLoad')) {
        if (document.getElementById('ajaxWrapLoad').style.display != 'none') {
            if (document.getElementById('postNodeForm') || document.getElementById('footerLinks')) {
                document.getElementById('ajaxWrapLoad').style.display = 'none';
            }
            setTimeout("checkAjaxLoad()", 50);
        }
    }
    return true;
}
setTimeout("checkAjaxLoad()", 100);

$(function() {
	
	$(document).on("click", ".upTypeBtn", function() {
		var nID = $(this).attr("name").replace("n", "").replace("fld", "");
		if (document.getElementById("n"+nID+"fld0") && document.getElementById("n"+nID+"fld0").checked) { // (Video)
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
	$('[data-toggle="tooltip"]').tooltip();
	
	$(document).on("click", ".navDeskMaj", function() {
		var majInd = $(this).attr("id").replace("maj", "");
		$("#minorNav"+majInd+"").slideToggle("fast");
	});
	$(document).on("click", "#navMobBurger1", function() {
		document.getElementById("navMobBurger1").style.display='none';
		document.getElementById("navMobBurger2").style.display='inline';
		$("#navMobFull").slideDown("fast");
	});
	$(document).on("click", "#navMobBurger2", function() {
		document.getElementById("navMobBurger1").style.display='inline';
		document.getElementById("navMobBurger2").style.display='none';
		$("#navMobFull").slideUp("fast");
	});
	
	
    $(document).on("click", ".nodeShowCond", function() {
        var nID = $(this).attr("id").replace("showCond", "");
        if (document.getElementById("condDeets"+nID+"")) {
            if (document.getElementById("condDeets"+nID+"").style.display=='inline') {
                document.getElementById("condDeets"+nID+"").style.display='none';
            } else {
                document.getElementById("condDeets"+nID+"").style.display='inline';
            }
        }
        return true;
    });
	
});


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


var holdSess = 0;
function holdSession() {
	if (holdSess > 0 && document.getElementById('hidFrameID')) {
		document.getElementById('hidFrameID').src='/holdSess';
		setTimeout("holdSession()", (5*60000));
	}
	return true;
}
setTimeout("holdSession()", (5*60000));
