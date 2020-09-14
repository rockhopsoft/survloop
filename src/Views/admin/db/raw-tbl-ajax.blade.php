// resources/views/vendor/survloop/admin/db/raw-tbl-ajax.blade.php

function columnSort(sortFld, sortDir) {
    if (document.getElementById("ajaxTbl")) {
        document.getElementById("ajaxTbl").innerHTML='<div class="pT30" style="width: 380px;">'+getSpinner()+'</div>';
        var url = "/dashboard/db/tbl-raw?ajax=1&tbl={{ $tbl->tbl_name }}&sortFld="+sortFld+"&sortDir="+sortDir;
        console.log(url);
        $("#ajaxTbl").load(url);
    }
}
$(document).on("click", ".columnSort", function() {
    columnSort($(this).attr("data-sort-fld"), $(this).attr("data-sort-dir"));
});
