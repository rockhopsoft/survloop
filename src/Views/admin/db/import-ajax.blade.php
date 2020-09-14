// resources/views/vendor/survloop/admin/db/import-ajax.blade.php

function updateImportType() {
    var typeImport = "";
    if (document.getElementById("importTblTypeNew") && document.getElementById("importTblTypeNew").checked) {
        typeImport = "New";
    } else if (document.getElementById("importTblTypeOld") && document.getElementById("importTblTypeOld").checked) {
        typeImport = "Old";
    }
    if (typeImport == "New") {
        document.getElementById("importTblTypeNewlab").className="fingerAct";
        document.getElementById("importTblTypeOldlab").className="finger";
        $("#importTblNew").slideDown(150);
        $("#importTblOld").slideUp(150);
    } else {
        document.getElementById("importTblTypeNewlab").className="finger";
        document.getElementById("importTblTypeOldlab").className="fingerAct";
        $("#importTblNew").slideUp(150);
        $("#importTblOld").slideDown(150);
    }
}
$(document).on("click", ".updateImportType", function() { updateImportType(); });

function updateFldImport(ind) {
    if (document.getElementById("fldImport"+ind+"ID") && document.getElementById("fldEng"+ind+"Wrap") && document.getElementById("fldName"+ind+"Wrap")) {
        if (document.getElementById("fldImport"+ind+"ID").value == 1) {
            document.getElementById("fldEng"+ind+"Wrap").style.display = "block";
            document.getElementById("fldName"+ind+"Wrap").style.display = "block";
        } else {
            document.getElementById("fldEng"+ind+"Wrap").style.display = "none";
            document.getElementById("fldName"+ind+"Wrap").style.display = "none";
        }
    }
}
$(document).on("click", ".updateFldImport", function() {
    updateFldImport($(this).attr("data-fld-ind"));
});

function importSkipAll(newVal) {
    if (document.getElementById("colCntID")) {
        var tot = document.getElementById("colCntID").value;
        for (var ind = 0; ind < tot; ind++) {
            if (document.getElementById("fldImport"+ind+"ID")) {
                document.getElementById("fldImport"+ind+"ID").value = newVal;
                updateFldImport(ind);
            }
        }
    }
    return true;
}
function importSkipAllBtn() {
    if (document.getElementById("importSkipAll") && document.getElementById("importSkipNone")) {
        document.getElementById("importSkipAll").style.display = "none";
        document.getElementById("importSkipNone").style.display = "block";
        importSkipAll(0);
    }
}
function importSkipNoneBtn() {
    if (document.getElementById("importSkipAll") && document.getElementById("importSkipNone")) {
        document.getElementById("importSkipAll").style.display = "block";
        document.getElementById("importSkipNone").style.display = "none";
        importSkipAll(1);
    }
}
$(document).on("click", "#importSkipAllBtn", function() { importSkipAllBtn(); });
$(document).on("click", "#importSkipNoneBtn", function() { importSkipNoneBtn(); });


