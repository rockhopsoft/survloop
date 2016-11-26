

$(function() {
	$(document).on("click", ".fldSpecBtn", function() {
		var FldID = $(this).attr("id").replace("fldSpecBtn", "");
		if (document.getElementById("fldSpec"+FldID+"").innerHTML == '') {
			$("#fldSpec"+FldID+"").load("/dashboard/db/ajax-field/"+FldID+"");
			//window.open("/dashboard/db/ajax-field/"+FldID+"");
		}
		$("#fldSpec"+FldID+"").slideToggle("fast");
		$("#fldSpecA"+FldID+"").slideToggle("fast");
		return true;
	});
	
	$(document).on("click", "#adminAboutBtn", function() { $("#adminAbout").slideUp("fast"); });
	$(document).on("click", "#adminAboutTog", function() { $("#adminAbout").slideToggle("fast"); });
	
	
});




// Database Designer

function checkKey(FldID, isKey) {
	if (document.getElementById('keyFor'+FldID+'') 
		&& document.getElementById('keyFor'+FldID+'').checked) {
		document.getElementById('foreign'+FldID+'').style.display='block';
	}
	else document.getElementById('foreign'+FldID+'').style.display='none';
	if (isKey == 0) {
		if (document.getElementById('keyNon'+FldID+'').checked) {
			document.getElementById('keyPri'+FldID+'').checked=false;
			document.getElementById('keyFor'+FldID+'').checked=false;
			document.getElementById('keyAlt'+FldID+'').checked=false;
		}
	}
	else {
		if (document.getElementById('keyPri'+FldID+'').checked 
			|| document.getElementById('keyFor'+FldID+'').checked 
			|| document.getElementById('keyAlt'+FldID+'').checked) {
			document.getElementById('keyNon'+FldID+'').checked=false;
		}
	}
	return true;
}

function clearGeneric() {
	document.getElementById('saveGenericID').value=0;
	document.fldEdit.target="_parent";
	document.getElementById('generState').innerHTML='<i class="fa fa-check-square-o"></i>';
	return true;
}

function saveGeneric() {
	document.getElementById('saveGenericID').value=1;
	document.getElementById('FldSpecTypeID').value='Generic';
	document.fldEdit.target="hidFrame";
	setTimeout("document.fldEdit.submit()", 500);
	setTimeout("clearGeneric()", 2000);
	return true;
}

function chkCom2(set) {
	if (!document.getElementById('c'+set+'2').checked) { 
		document.getElementById('c'+set+'3').checked=false; document.getElementById('c'+set+'5').checked=false; 
		document.getElementById('c'+set+'7').checked=false; document.getElementById('c'+set+'11').checked=false; 
		document.getElementById('c'+set+'13').checked=false; document.getElementById('c'+set+'17').checked=false; 
		document.getElementById('c'+set+'19').checked=false; 
	}
	return true;
}
function chkCom3(set) {
	if (document.getElementById('c'+set+'3').checked) { 
		document.getElementById('c'+set+'5').checked=true; document.getElementById('c'+set+'7').checked=true; 
		document.getElementById('c'+set+'11').checked=true; document.getElementById('c'+set+'13').checked=true; 
		document.getElementById('c'+set+'17').checked=true; document.getElementById('c'+set+'19').checked=true; 
		document.getElementById('c'+set+'2').checked=true; 
	}
	else {
		document.getElementById('c'+set+'5').checked=false; document.getElementById('c'+set+'7').checked=false; 
		document.getElementById('c'+set+'11').checked=false; document.getElementById('c'+set+'13').checked=false; 
		document.getElementById('c'+set+'17').checked=false; document.getElementById('c'+set+'19').checked=false; 
	}
	return true;
}
function chkComX(set, isChecked) {
	if (!isChecked) document.getElementById('c'+set+'3').checked=false;
	else {
		document.getElementById('c'+set+'2').checked=true; 
		if (document.getElementById('c'+set+'5').checked && document.getElementById('c'+set+'7').checked
			&& document.getElementById('c'+set+'11').checked && document.getElementById('c'+set+'13').checked 
			&& document.getElementById('c'+set+'17').checked && document.getElementById('c'+set+'19').checked) {
			document.getElementById('c'+set+'3').checked=true;
		}
	}
	return true;
}

function chkOp2(set) {
	if (!document.getElementById('o'+set+'2').checked) {
		document.getElementById('o'+set+'3').checked=false; document.getElementById('o'+set+'5').checked=false; 
		document.getElementById('o'+set+'7').checked=false; document.getElementById('o'+set+'11').checked=false; 
		document.getElementById('o'+set+'13').checked=false; document.getElementById('o'+set+'17').checked=false; 
	}
	return true;
}
function chkOp3(set) {
	if (document.getElementById('o'+set+'3').checked) {
		document.getElementById('o'+set+'5').checked=true; document.getElementById('o'+set+'7').checked=true; 
		document.getElementById('o'+set+'11').checked=true; document.getElementById('o'+set+'13').checked=true; 
		document.getElementById('o'+set+'17').checked=true; document.getElementById('o'+set+'2').checked=true; 
	}
	else {
		document.getElementById('o'+set+'5').checked=false; document.getElementById('o'+set+'7').checked=false; 
		document.getElementById('o'+set+'11').checked=false; document.getElementById('o'+set+'13').checked=false; 
		document.getElementById('o'+set+'17').checked=false; 
	}
	return true;
}
function chkOpX(set, isChecked) {
	if (!isChecked) document.getElementById('o'+set+'3').checked=false;
	else {
		document.getElementById('o'+set+'2').checked=true;
		if (document.getElementById('o'+set+'5').checked && document.getElementById('o'+set+'7').checked && document.getElementById('o'+set+'11').checked 
			&& document.getElementById('o'+set+'13').checked && document.getElementById('o'+set+'17').checked) {
			document.getElementById('o'+set+'3').checked=true;
		}
	}
	return true;
}

