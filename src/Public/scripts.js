var allFldList = new Array();
function addFld(fld) { allFldList[allFldList.length] = fld; return true; }
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

var totFormErrors = 0;
var formErrorsEng = '';

function setFormErrs() {
	if (document.getElementById('formErrorMsg')) document.getElementById('formErrorMsg').innerHTML = '<i class="fa fa-arrow-up"></i> Please complete all required fields. '+formErrorsEng;
	return true;
}
function clearFormErrs() {
	if (document.getElementById('formErrorMsg')) document.getElementById('formErrorMsg').innerHTML = '';
	return true;
}

function setFormLabelBlack(nID) {
	if (document.getElementById('node'+nID+'')) 		document.getElementById('node'+nID+'').className=document.getElementById('node'+nID+'').className.replace('nodeWrapError', 'nodeWrap');
	return true;
}
function setFormLabelRed(nID) {
	if (document.getElementById('node'+nID+'')) 		document.getElementById('node'+nID+'').className=document.getElementById('node'+nID+'').className.replace('nodeWrap', 'nodeWrapError').replace('nodeWrapErrorError', 'nodeWrapError');
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
function reqFormFldDate(nID) {
	//alert(document.getElementById('n'+nID+'fldYearID').value+'-'+document.getElementById('n'+nID+'fldMonthID').value+'-'+document.getElementById('n'+nID+'fldDayID').value);
	if (document.getElementById('n'+nID+'fldYearID').value == '00' || document.getElementById('n'+nID+'fldMonthID').value == '00' || document.getElementById('n'+nID+'fldDayID').value == '00') {
		setFormLabelRed(nID);
		totFormErrors++;
	}
	else setFormLabelBlack(nID);
	return true;
}

function charLimit(nID, limit) {
	if (document.getElementById('n'+nID+'FldID').value.length > limit) {
		document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'FldID').value.substring(0, limit);
	}
	var charRemain = limit-document.getElementById('n'+nID+'FldID').value.length;
	document.getElementById('charLimit'+nID+'Msg').innerHTML = limit+' Character Limit: '+charRemain+' Remaining';
	return true;
}

function dateChange(nID) {
	document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'fldYearID').value+'-'+document.getElementById('n'+nID+'fldMonthID').value+'-'+document.getElementById('n'+nID+'fldDayID').value;
	return true;
}

function dateKeyUp(nID, which) {
	document.getElementById('n'+nID+'FldID').value = document.getElementById('n'+nID+'fldMonthID').value+'/'+document.getElementById('n'+nID+'fldDayID').value+'/'+document.getElementById('n'+nID+'fldYearID').value;
	return true;
}


function formChangeFeetInches(nID) {
	if (document.getElementById('n'+nID+'FldID')) document.getElementById('n'+nID+'FldID').value = (12*parseInt(document.getElementById('n'+nID+'fldFeetID').value))+parseInt(document.getElementById('n'+nID+'fldInchID').value);
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

function wordCountKeyUp(nID) {
	if (document.getElementById("n"+nID+"FldID") && document.getElementById("wordCnt"+nID+"")) {
	  var cnt = getWordCnt(document.getElementById("n"+nID+"FldID"));
	  var cntWords = "<span class=\'slRedLight\'>"+cnt+"</span>";
	  if (cnt >= 200 && cnt <= 400) cntWords = "<span class=\'slBlueLight\'>"+cnt+"</span>";
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


var main = function(){
	if (document.getElementById('fixedHeader')) {
		var fixer = $('#fixedHeader');
		var scrollMin = 100;
		if ($(window).width() <= 480) scrollMin = 30;
		if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
		$(document).scroll(function(){
			if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
			else fixer.removeClass('fixed');
		});
	}
}
$(document).ready(main);

$(function() {
	
	$(document).on("click", ".upTypeBtn", function() {
		var nID = $(this).attr("name").replace("up", "").replace("Type", "");
		if (document.getElementById("up"+nID+"Type0") && document.getElementById("up"+nID+"Type0").checked) { // (Video)
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
	//alert('?');
	if (holdSess > 0 && document.getElementById('hidFrameID')) {
		document.getElementById('hidFrameID').src='/holdSess';
		setTimeout("holdSession()", (5*60000));
	}
	return true;
}
setTimeout("holdSession()", (5*60000));
