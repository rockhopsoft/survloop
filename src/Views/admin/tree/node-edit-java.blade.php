/* resources/views/vendor/survloop/admin/tree/node-edit-java.blade.php */

var treeListChk = new Array(); // when [0] is changed, reload field drops [1], [2]...
treeListChk[0] = [ "nodeSurvWidgetTreeID", "nodeWidgGrphYID", "nodeWidgGrphXID", "nodeWidgBarYID", "nodeWidgBarL1ID", "nodeWidgBarL2ID", "nodeWidgPieYID" ];

function changeNodeType(newType) {
    document.getElementById('hasPage').style.display='none';
    document.getElementById('hasPageOpts').style.display='none';
    document.getElementById('pageLoopDesc').style.display='none';
    document.getElementById('pagePreview').style.display='none';
    document.getElementById('hasLoop').style.display='none';
    document.getElementById('hasCycle').style.display='none';
    document.getElementById('hasSort').style.display='none';
    document.getElementById('hasBranch').style.display='none';
    document.getElementById('hasDataManip').style.display='none';
    document.getElementById('widgetType').style.display='none';
    document.getElementById('hasSurvWidget').style.display='none';
    document.getElementById('hasLayout').style.display='none';
    document.getElementById('hasInstruct').style.display='none';
    document.getElementById('hasBigButt').style.display='none';
    document.getElementById('dataPrintType').style.display='none';
    document.getElementById('hasDataPrint').style.display='none';
    document.getElementById('hasSendEmail').style.display='none';
    document.getElementById('emailPreviewStuff').style.display='none';
    if (newType == 'branch' || newType == 'data' || newType == 'loop' || newType == 'sort' || newType == 'cycle' 
        || newType == 'page' || newType == 'instruct' || newType == 'instructRaw' || newType == 'heroImg' 
        || newType == 'bigButt' || newType == 'survWidget' || newType == 'sendEmail' || newType == 'layout' 
        || newType == 'dataPrint') {
        document.getElementById('hasResponse').style.display='none';
        document.getElementById('hasResponseLayout').style.display='none';
        document.getElementById('responseType').style.display='none';
        if (newType == 'bigButt') document.getElementById('hasPrompt').style.display='none';
        if  (newType == 'instruct' || newType == 'instructRaw') {
            document.getElementById('hasInstruct').style.display='block';
        } else if (newType == 'page') {
            document.getElementById('hasPage').style.display='block';
            document.getElementById('hasPageOpts').style.display='block';
            document.getElementById('pagePreview').style.display='block';
        } else if (newType == 'dataPrint') {
            document.getElementById('dataPrintType').style.display='block';
            document.getElementById('hasDataPrint').style.display='block';
        }
        else if (newType == 'bigButt') document.getElementById('hasBigButt').style.display='block';
        else if (newType == 'data') document.getElementById('hasDataManip').style.display='block';
        else if (newType == 'branch') document.getElementById('hasBranch').style.display='block';
        else if (newType == 'loop') {
            document.getElementById('hasLoop').style.display='block';
            document.getElementById('hasPage').style.display='block';
            document.getElementById('pageLoopDesc').style.display='block';
        }
        else if (newType == 'cycle') document.getElementById('hasCycle').style.display='block';
        else if (newType == 'sort') document.getElementById('hasSort').style.display='block';
        else if (newType == 'survWidget') {
            document.getElementById('hasSurvWidget').style.display='block';
            document.getElementById('widgetType').style.display='block';
        }
        else if (newType == 'layout') document.getElementById('hasLayout').style.display='block';
        else if (newType == 'sendEmail') {
            document.getElementById('hasSendEmail').style.display='block';
            document.getElementById('emailPreviewStuff').style.display='block';
        }
    } else {
        document.getElementById('hasPrompt').style.display='block';
        document.getElementById('hasResponse').style.display='block';
        document.getElementById('hasResponseLayout').style.display='block';
        document.getElementById('responseType').style.display='block';
    }
    if (document.getElementById('isPageBlock')) {
        if (newType == 'instruct' || newType == 'instructRaw' || newType == 'layout') {
            document.getElementById('isPageBlock').style.display='block';
        }
    }
    return true;
}
setTimeout("changeNodeType(document.getElementById('nodeTypeID').value)", 10);

function changeResponseType(newType) {
    if (newType == 'Radio' || newType == 'Checkbox' || newType == 'Drop Down' || newType == 'Spreadsheet Table' || newType == 'Other/Custom') {
        document.getElementById('resOpts').style.display='block';
        //document.getElementById('resNotMulti').style.display='none';
        if (newType == 'Drop Down') {
            document.getElementById('taggerOpts').style.display='block';
        } else {
            document.getElementById('taggerOpts').style.display='none';
        }
    } else {
        document.getElementById('resOpts').style.display='none';
        /* if (newType == 'Text' || newType == 'Long Text') {
            document.getElementById('resNotMulti').style.display='block';
        } */
    }
    if (newType == 'Text') {
        document.getElementById('resCanAuto').style.display='block';
    } else {
        document.getElementById('resCanAuto').style.display='none';
    }
    if (newType == 'Long Text') {
        document.getElementById('resNotWrdCnt').style.display='block';
    } else {
        document.getElementById('resNotWrdCnt').style.display='none';
    }
    if (newType == 'Date' || newType == 'Date Picker' || newType == 'Date Time') {
        document.getElementById('DateOpts').style.display='block';
    } else {
        document.getElementById('DateOpts').style.display='none';
    }
    if (newType == 'Text:Number' || newType == 'Slider') {
        document.getElementById('NumberOpts').style.display='block';
    } else {
        document.getElementById('NumberOpts').style.display='none';
    }
    if (newType == 'Spreadsheet Table') {
        document.getElementById('spreadTblOpts').style.display='block';
        document.getElementById('storeResponseDiv').style.display='none';
    } else {
        document.getElementById('spreadTblOpts').style.display='none';
        document.getElementById('storeResponseDiv').style.display='block';
    }
    if (newType == 'Radio') {
        document.getElementById('nOptsRadio').style.display='block';
    } else {
        document.getElementById('nOptsRadio').style.display='none';
    }
    if (newType == 'Checkbox' || newType == 'Other/Custom') {
        for (var i=0; i < {{ $resLimit }}; i++) {
            if (document.getElementById('resMutEx'+i+'')) {
                document.getElementById('resMutEx'+i+'').style.display='block';
            }
        }
    } else {
        for (var i=0; i < {{ $resLimit }}; i++) {
            if (document.getElementById('resMutEx'+i+'')) {
                document.getElementById('resMutEx'+i+'').style.display='none';
            }
        }
    }
    if (document.getElementById('resOptsLab') && document.getElementById('resOptsLabTbl')) {
        if (newType == 'Spreadsheet Table') {
            document.getElementById('resOptsLab').style.display='none';
            document.getElementById('resOptsLabTbl').style.display='block';
        } else {
            document.getElementById('resOptsLab').style.display='block';
            document.getElementById('resOptsLabTbl').style.display='none';
        }
    }
    return true;
}

function changeDataPrintType(newType) {
    if (newType == 'Data Print Block' || newType == 'Data Print Columns' || newType == 'Print Vert Progress') {
        document.getElementById('dataPrintPull').style.display='none';
        document.getElementById('dataPrintTitle').style.display='block';
        document.getElementById('dataPrintConds').style.display='none';
    } else {
        document.getElementById('dataPrintPull').style.display='block';
        document.getElementById('dataPrintTitle').style.display='none';
        document.getElementById('dataPrintConds').style.display='block';
    }
    return true;
}

function changeRequiredType() {
    if (!document.getElementById('responseReqOpts')) return false;
    if (document.getElementById('opts5ID') && document.getElementById('opts5ID').checked) {
        document.getElementById('responseReqOpts').style.display = 'block';
        return true;
    }
    document.getElementById('responseReqOpts').style.display = 'none';
    return true;
}

function changeResponseMobileType() {
    if (!document.getElementById('responseCheckOpts') || !document.getElementById('changeResponseMobileID')) return false;
    if (document.getElementById('changeResponseMobileID') && document.getElementById('changeResponseMobileID').value == 'mobile') {
        document.getElementById('responseCheckOpts').style.display = 'none';
        return true;
    }
    document.getElementById('responseCheckOpts').style.display = 'block';
    return true;
}

function changeLayoutType() {
    if (!document.getElementById('nodeLayoutTypeID')) return false;
    if (document.getElementById('nodeLayoutTypeID').value == 'Page Block') {
        document.getElementById('layoutSizeRow').style.display = 'none';
        document.getElementById('layoutSizeCol').style.display = 'none';
    } else if (document.getElementById('nodeLayoutTypeID').value == 'Layout Row') {
        document.getElementById('layoutSizeRow').style.display = 'block';
        document.getElementById('layoutSizeCol').style.display = 'none';
    } else if (document.getElementById('nodeLayoutTypeID').value == 'Layout Column') {
        document.getElementById('layoutSizeRow').style.display = 'none';
        document.getElementById('layoutSizeCol').style.display = 'block';
    }
    return true;
}

function changeWidgetType() {
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Search Results'
         || document.getElementById('nodeSurvWidgetTypeID').value == 'Search Featured'
         || document.getElementById('nodeSurvWidgetTypeID').value == 'Record Previews') {
         document.getElementById('widgetRecLimitID').style.display='block';
     } else {
         document.getElementById('widgetRecLimitID').style.display='none';
     }
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Plot Graph' || document.getElementById('nodeSurvWidgetTypeID').value == 'Line Graph') {
         document.getElementById('widgetGraph').style.display='block';
     } else {
         document.getElementById('widgetGraph').style.display='none';
     }
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Bar Graph') {
         document.getElementById('widgetBarChart').style.display='block';
     } else {
         document.getElementById('widgetBarChart').style.display='none';
     }
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Pie Chart') {
         document.getElementById('widgetPieChart').style.display='block';
     } else {
         document.getElementById('widgetPieChart').style.display='none';
     }
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Map') {
         document.getElementById('widgetMap').style.display='block';
     } else {
         document.getElementById('widgetMap').style.display='none';
     }
     return true;
}

function toggleWordCntLimit() {
    if (!document.getElementById('opts47ID')) return false;
    if (document.getElementById('opts47ID').checked) {
        document.getElementById('resWordLimit').style.display='block';
    } else {
        document.getElementById('resWordLimit').style.display='none';
    }
    return true;
}

var maxRes = 0; var i = 0;
function checkRes() {
    maxRes = 0;
    for (i=0; i < {{ $resLimit }}; i++) {
        if (document.getElementById('response'+i+'ID').value != '' 
            || document.getElementById('response'+i+'vID').value != '') maxRes = i;
    }
    for (i=0; i <= (maxRes+1); i++) {
        if (document.getElementById('r'+i+'')) document.getElementById('r'+i+'').style.display = 'block';
    }
    for (i=(maxRes+2); i < {{ $resLimit }}; i++) {
        if (document.getElementById('r'+i+'')) document.getElementById('r'+i+'').style.display = 'none';
    }
    return true;
}

function checkDataManipFlds() {
    if (document.getElementById('dataManipTypeCloseSess').checked) {
        document.getElementById('dataNewRecord').style.display = 'none';
        document.getElementById('manipCloseSess').style.display = 'block';
    } else {
        document.getElementById('dataNewRecord').style.display = 'block';
        document.getElementById('manipCloseSess').style.display = 'none';
    }
    return true;
}

function checkPageBlock() {
    if (document.getElementById('opts71ID').checked) {
        document.getElementById('pageBlockOpts').style.display = 'block';
    } else {
        document.getElementById('pageBlockOpts').style.display = 'none';
    }
    if (document.getElementById('opts97ID').checked) {
        document.getElementById('nodeCachingOpt').style.display = 'block';
    } else {
        document.getElementById('nodeCachingOpt').style.display = 'none';
    }
    return true;
}
function previewPageBlock() {
    if (document.getElementById('pageBlock') && document.getElementById('opts71ID') 
        && document.getElementById('opts71ID').checked && document.getElementById('blockBGID')
        && document.getElementById('blockTextID') && document.getElementById('blockLinkID')) {
        if (document.getElementById('blockImgID') && document.getElementById('blockImgID').value.trim() != '') {
            document.getElementById('pageBlock').style.backgroundImage = "url('"+document.getElementById('blockImgID').value+"')";
            if (document.getElementById('blockImgTypeB') && document.getElementById('blockImgTypeB').checked) {
                document.getElementById('pageBlock').style.backgroundSize = "auto";
                document.getElementById('pageBlock').style.backgroundRepeat = "repeat";
            } else {
                document.getElementById('pageBlock').style.backgroundSize = "100%";
                document.getElementById('pageBlock').style.backgroundRepeat = "no-repeat";
            }
            if ((document.getElementById('blockImgFixY') && document.getElementById('blockImgFixY').checked) || (document.getElementById('blockImgFixP') && document.getElementById('blockImgFixP').checked)) {
                document.getElementById('pageBlock').style.backgroundAttachment = "fixed";
            } else {
                document.getElementById('pageBlock').style.backgroundAttachment = "scroll";
            }
        } else {
            document.getElementById('pageBlock').style.background=document.getElementById('blockBGID').value;
        }
        document.getElementById('pageBlock').style.color=document.getElementById('blockTextID').value;
        document.getElementById('blockLinkh4').style.color=document.getElementById('blockLinkID').value;
        setTimeout("previewPageBlock()", 1000);
    }
    return true;
}
setTimeout("previewPageBlock()", 50);

function previewBigBtn() {
    if (document.getElementById('buttonPreview') && document.getElementById('bigBtnStyleID')
        && document.getElementById('bigBtnTextID')) {
        var btnText = document.getElementById('bigBtnTextID').value;
        if (btnText == '') btnText = 'Button Text';
        var preview = '<div class="nFld m0"><a href="javascript:;" class="';
        if (document.getElementById('bigBtnStyleID').value != 'Text') {
            preview += 'btn ';
            if (document.getElementById('bigBtnStyleID').value == 'Default') preview += 'btn-secondary';
            else preview += 'btn-primary';
            preview += ' btn-lg nFldBtn';
        }
        preview += '">'+btnText+'</a>';
        document.getElementById('buttonPreview').innerHTML = preview;
    }
    return true;
}
setTimeout("previewBigBtn()", 50);

function checkData() {
    maxRes = 0;
    for (i=1; i < {{ $resLimit }}; i++) {
        if (document.getElementById('manipMore'+i+'StoreID').value != '') maxRes = i;
    }
    for (i=1; i <= (maxRes+1); i++) {
        if (document.getElementById('dataManipFld'+i+'')) document.getElementById('dataManipFld'+i+'').style.display = 'block';
    }
    for (i=(maxRes+2); i < {{ $resLimit }}; i++) {
        if (document.getElementById('dataManipFld'+i+'')) document.getElementById('dataManipFld'+i+'').style.display = 'none';
    }
    return true;
}
setTimeout("checkData()", 100);

function chkPageForTree() {
    if (document.getElementById('reportPageID') && document.getElementById('searchPageID') && document.getElementById('reportPageTreeID')) {
        if (document.getElementById('reportPageID').checked || document.getElementById('searchPageID').checked) {
            document.getElementById('reportPageTreeID').style.display='block';
        } else {
            document.getElementById('reportPageTreeID').style.display='none';
        }
    }
    return true;
}
setTimeout("chkPageForTree()", 100);

$(document).ready(function(){

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

});

