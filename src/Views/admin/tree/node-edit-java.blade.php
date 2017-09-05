/* resources/views/vendor/survloop/admin/tree/node-edit-java.blade.php */
<?php /* @if ($node->isInstruct()) 
    var trixElement = document.querySelector("trix-editor");
    trixElement.editor.setSelectedRange([0, 0]);
    trixElement.editor.insertHTML("{!! preg_replace("/\r|\n/", "", addslashes($node->nodeRow->NodePromptText)) !!}");
@endif */ ?>
function changeNodeType(newType) {
    document.getElementById('hasPage').style.display='none';
    document.getElementById('hasLoop').style.display='none';
    document.getElementById('hasCycle').style.display='none';
    document.getElementById('hasSort').style.display='none';
    document.getElementById('hasBranch').style.display='none';
    document.getElementById('hasDataManip').style.display='none';
    document.getElementById('hasSurvWidget').style.display='none';
    document.getElementById('hasLayout').style.display='none';
    document.getElementById('hasInstruct').style.display='none';
    document.getElementById('hasBigButt').style.display='none';
    document.getElementById('hasHeroImg').style.display='none';
    if (newType == 'branch' || newType == 'data' || newType == 'loop' || newType == 'sort' || newType == 'cycle' 
        || newType == 'page' || newType == 'instruct' || newType == 'instructRaw' || newType == 'heroImg' 
        || newType == 'bigButt' || newType == 'survWidget' || newType == 'layout') {
        document.getElementById('hasResponse').style.display='none';
        document.getElementById('hasResponseLayout').style.display='none';
        if (newType == 'bigButt') {
            document.getElementById('hasPrompt').style.display='none';
        }
        if  (newType == 'instruct' || newType == 'instructRaw') {
            document.getElementById('hasInstruct').style.display='block';
        }
        else if (newType == 'bigButt') document.getElementById('hasBigButt').style.display='block';
        else if (newType == 'heroImg') document.getElementById('hasHeroImg').style.display='block';
        else if (newType == 'data') document.getElementById('hasDataManip').style.display='block';
        else if (newType == 'branch') document.getElementById('hasBranch').style.display='block';
        else if (newType == 'loop') document.getElementById('hasLoop').style.display='block';
        else if (newType == 'cycle') document.getElementById('hasCycle').style.display='block';
        else if (newType == 'sort') document.getElementById('hasSort').style.display='block';
        else if (newType == 'survWidget') document.getElementById('hasSurvWidget').style.display='block';
        else if (newType == 'layout') document.getElementById('hasLayout').style.display='block';
    } else {
        document.getElementById('hasPrompt').style.display='block';
        document.getElementById('hasResponse').style.display='block';
        document.getElementById('hasResponseLayout').style.display='block';
    }
    if (document.getElementById('isPageBlock')) {
        if (newType == 'instruct' || newType == 'instructRaw' || newType == 'layout') {
            document.getElementById('isPageBlock').style.display='block';
        }
    }
    return true;
}

function changeResponseType(newType) {
    if (newType == 'Radio' || newType == 'Checkbox' || newType == 'Drop Down' || newType == 'Other/Custom') {
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
    return true;
}

function changeResponseListType() {
    if (document.getElementById('responseListTypeID')) {
        if (document.getElementById('responseListTypeID').value == 'manual') {
            document.getElementById('responseOptDefs').style.display = 'none';
            document.getElementById('responseOptLoops').style.display = 'none';
            document.getElementById('responseOptTbls').style.display = 'none';
            document.getElementById('responseDefinitionID').value='';
            document.getElementById('responseLoopItemsID').value='';
            document.getElementById('responseTablesID').value='';
        } else if (document.getElementById('responseListTypeID').value == 'auto-def') {
            document.getElementById('responseOptDefs').style.display = 'block';
            document.getElementById('responseOptLoops').style.display = 'none';
            document.getElementById('responseOptTbls').style.display = 'none';
        } else if (document.getElementById('responseListTypeID').value == 'auto-loop') {
            document.getElementById('responseOptDefs').style.display = 'none';
            document.getElementById('responseOptLoops').style.display = 'block';
            document.getElementById('responseOptTbls').style.display = 'none';
        } else if (document.getElementById('responseListTypeID').value == 'auto-tbl') {
            document.getElementById('responseOptDefs').style.display = 'none';
            document.getElementById('responseOptLoops').style.display = 'none';
            document.getElementById('responseOptTbls').style.display = 'block';
        }
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
     if (document.getElementById('nodeSurvWidgetTypeID').value == 'Send Email') {
         document.getElementById('widgetPrePost').style.display='none';
         document.getElementById('widgetEmail').style.display='block';
         document.getElementById('emailPreviewStuff').style.display='block';
     } else {
         document.getElementById('widgetEmail').style.display='none';
         document.getElementById('emailPreviewStuff').style.display='none';
         document.getElementById('widgetPrePost').style.display='block';
         
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
    if (document.getElementById('dataManipTypeWrap').checked
        || document.getElementById('dataManipTypeCloseSess').checked) {
        document.getElementById('dataNewRecord').style.display = 'none';
    } else {
        document.getElementById('dataNewRecord').style.display = 'block';
    }
    if (document.getElementById('dataManipTypeCloseSess').checked) {
        document.getElementById('manipCloseSess').style.display = 'block';
    } else {
        document.getElementById('manipCloseSess').style.display = 'none';
    }
    return true;
}

function checkPageBlock() {
    if (document.getElementById('opts71ID').checked) {
        document.getElementById('pageBlockOpts').style.display = 'block';
    }
    else {
        document.getElementById('pageBlockOpts').style.display = 'none';
    }
    
    return true;
}
function previewPageBlock() {
    if (document.getElementById('pageBlock') && document.getElementById('blockBGID')
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
            if (document.getElementById('blockImgFixID') && document.getElementById('blockImgFixID').checked) {
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
            if (document.getElementById('bigBtnStyleID').value == 'Default') preview += 'btn-default';
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