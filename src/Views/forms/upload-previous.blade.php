<!-- resources/views/survloop/forms/upload-previous.blade.php -->

<div class="nodeAnchor"><a id="upPrev{{ $nIDtxt }}" name="upPrev{{ $nIDtxt }}"></a></div>
@if (!isset($uploads) || sizeof($uploads) == 0) 
    <h4><span class="slGrey">Nothing uploaded here.</span></h4>
@else
    <h4 class="mB0">
        <i class="fa fa-cloud-upload"></i> {{ sizeof($uploads) }} 
        Previous @if (sizeof($uploads) == 1) Upload: @else Uploads: @endif
    </h2>
    @foreach ($uploads as $i => $upRow)
        @if ((!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->up_id)
            && isset($upDeets[$i]))
            <div class="nodeAnchor">
                <a id="up{{ $upRow->up_id }}" name="up{{ $upRow->up_id }}"></a>
            </div>
            <div class="uploadedWrap">
                <div class="row">
                    <div class="col-md-4 m0 taC">
                        
                @if (intVal($upRow->up_type) == $vidTypeID 
                    && (trim($upDeets[$i]["youtube"]) != '' 
                        || trim($upDeets[$i]["vimeo"]) != '')
                        || trim($upDeets[$i]["archiveVid"]) != '')
                        || trim($upDeets[$i]["instagram"]) != ''))

                    {!! view(
                        'vendor.survloop.forms.upload-previous-youtube', 
                        [
                            "i"       => $i,
                            "height"  => $height,
                            "upDeets" => $upDeets,
                            "upRow"   => $upRow
                        ]
                    )->render() !!}

                @elseif (isset($upRow->up_upload_file) 
                    && isset($upRow->up_stored_file) 
                    && trim($upRow->up_upload_file) != '' 
                    && trim($upRow->up_stored_file) != '')

                    @if (in_array($upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png")))
                        {!! view(
                            'vendor.survloop.forms.upload-previous-image', 
                            [
                                "i"       => $i,
                                "height"  => $height,
                                "upDeets" => $upDeets,
                                "upRow"   => $upRow,
                                "refresh" => $edit
                            ]
                        )->render() !!}
                    @else
                        {!! view(
                            'vendor.survloop.forms.upload-previous-other-file', 
                            [
                                "i"       => $i,
                                "height"  => $height,
                                "upDeets" => $upDeets,
                                "upRow"   => $upRow,
                                "refresh" => $edit
                            ]
                        )->render() !!}
                    @endif

                @endif

                    </div>
                    <div class="col-md-6 pT10">
                        
                        <div id="up{{ $upRow->up_id }}Info" class="disBlo fL">
                            <h4>{{  $upRow->up_title }}</h4> 
    <?php /*@if (trim($upRow->UpDesc) != '') <div class="fPerc133">{{ $upRow->UpDesc }}</div> @endif */?>
                            <div class="slGrey pT5">
                                @if (isset($GLOBALS["SL"]->treeSettings["uploads-public"]) 
                                    && intVal($GLOBALS["SL"]->treeSettings["uploads-public"][0]) > 0)
                                    @if (in_array($upRow->up_privacy, ['Public', 'Open'])) Public 
                                    @else Private 
                                    @endif
                                @endif
                                {{ $GLOBALS['SL']->def->getValById($upRow->up_type) }}
                            </div>
                            {!! $upDeets[$i]["fileLnk"] !!}
                        </div>
                        
                        <div id="up{{ $upRow->up_id }}InfoEdit" class="disNon pB10">
                            <input name="up{{ $upRow->up_id }}EditVisib" 
                                id="up{{ $upRow->up_id }}EditVisibID" 
                                type="hidden" value="0">
                            <div class="nPrompt">
                                <label for="up{{ $upRow->up_id }}EditTitleID">Upload Title:</label>
                            </div>
                            <div class="nFld mT0">
                                <input type="text" 
                                    class="form-control form-control-lg ntrStp slTab" 
                                    name="up{{ $upRow->up_id }}EditTitle" 
                                    id="up{{ $upRow->up_id }}EditTitleID" 
                                    value="{{ $upRow->up_title }}" 
                                    {!! $GLOBALS["SL"]->tabInd() !!} >
                            </div>
                            <?php /* <div class="nodeGap"></div>
                            <div class="nPrompt"><label for="up{{ $upRow->up_id }}EditDescID">Description:</label></div>
                            <div class="nFld">
                                <input type="text" name="up{{ $upRow->up_id }}EditDesc" id="up{{ $upRow->up_id }}EditDescID" 
                                    class="form-control form-control-lg ntrStp slTab" 
                                    value="{{ $upRow->UpDesc }}" {!! $GLOBALS["SL"]->tabInd() !!}></div> */ ?>
                            @if (sizeof($uploadTypes) > 1)
                                <div class="nodeHalfGap"></div>
                                <div class="nPrompt">
                                    <label for="up{{ $upRow->up_id }}EditTypeID">
                                        Upload Type:
                                    </label>
                                </div>
                                <div class="nFld mT0">
                                    <select name="up{{ $upRow->up_id }}EditType" 
                                        id="up{{ $upRow->up_id }}EditTypeID" 
                                        class="form-control form-control-lg ntrStp slTab"
                                        {!! $GLOBALS["SL"]->tabInd() !!}>
                                    @foreach ($uploadTypes as $ty)
                                        <option value="{{ $ty->def_id }}" 
                                            @if ($ty->def_id == $upRow->up_type) SELECTED @endif 
                                            >{{ $ty->def_value }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            @endif
                            @if (isset($GLOBALS["SL"]->treeSettings["uploads-public"]) 
                                && intVal($GLOBALS["SL"]->treeSettings["uploads-public"][0]) > 0)
                                <div class="nodeHalfGap"></div>
                                <div class="nPrompt">
                                    <label for="up{{ $upRow->up_id }}EditPrivacyID">Privacy:</label>
                                </div>
                                <div class="nFld mT0">
                                    <select name="up{{ $upRow->up_id }}EditPrivacy" 
                                        id="up{{ $upRow->up_id }}EditPrivacyID" 
                                        class="form-control form-control-lg ntrStp slTab"
                                        {!! $GLOBALS["SL"]->tabInd() !!}>
                                        <option value="Public" 
                                            @if ($upRow->up_privacy == 'Public') SELECTED @endif 
                                            >Public: Visible to whole world</option>
                                        <option value="Private" 
                                            @if ($upRow->up_privacy != 'Public') SELECTED @endif 
                                            >Private: Visible only to those authorized</option>
                                    </select>
                                </div>
                            @endif
                            <div class="nodeHalfGap"></div>
                            <input type="submit" value="Save Changes" 
                                id="editItemSave{{ $upRow->up_id }}"
                                class="nFormUploadSave btn btn-lg btn-primary btn-block">
                        </div>
                    </div>
                    <div class="col-md-2">
                    
                        <div id="editLoopItem{{ $upRow->up_id }}block" class="disBlo">
                            <a href="javascript:;" id="editLoopItem{{ $upRow->up_id }}" 
                                class="nFormLnkEdit btn btn-secondary btn-sm w100 mT10"
                                ><i class="fa fa-pencil fa-flip-horizontal mR5"></i> Edit</a>
                            <a href="javascript:;" id="delLoopItem{{ $upRow->up_id }}" 
                                class="nFormLnkDel nobld btn btn-secondary btn-sm w100 mT20"
                                ><i class="fa fa-trash-o"></i> Delete</a>
                    @if (in_array($upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png")))
                            <a href="javascript:;" id="hidivBtnRotate{{ $upRow->up_id }}" 
                                class="hidivBtn nobld btn btn-secondary btn-sm w100 mT20"
                                ><i class="fa fa-undo" aria-hidden="true"></i> Rotate</a>
                    @endif
                        </div>

                        <div id="delLoopItem{{ $upRow->up_id }}confirm" 
                            class="nFormLnkDelConfirm red brdRed round10 w100 p5 mT5 disNon">
                            Delete upload?
                            <a href="javascript:;" 
                                class="nFormLnkDelConfirmYes btn btn-primary btn-sm w100 mT10 red" 
                                id="delLoopItem{{ $upRow->up_id }}confirmY">Yes</a>
                            <a href="javascript:;" 
                                class="nFormLnkDelConfirmNo btn btn-secondary btn-sm w100 mT5" 
                                id="delLoopItem{{ $upRow->up_id }}confirmN">No</a>
                        </div>
                        
                    </div>
                </div>
                <div id="hidivRotate{{ $upRow->up_id }}" class="disNon pT30">
                    <div class="nPrompt">
                        <h4>Click which way this image should be rotated:</h4>
                    </div>
                    <div class="disBlo pT10 pB10">
                        <div class="row">
                            <div class="col-md-4 taC">
                                <a href="?upRotate={{ $upRow->up_id }}&rots=1" style="border: 1px;"
                                    ><img src="{{ $upDeets[$i]['filePub'] }}" 
                                    class="w100 mT30 mB30" border="1"
                                    style="transform: rotate(-90deg);" ></a>
                            </div>
                            <div class="col-md-4 taC">
                                <a href="?upRotate={{ $upRow->up_id }}&rots=2" style="border: 1px;"
                                    ><img src="{{ $upDeets[$i]['filePub'] }}" 
                                    class="w100 mT30 mB30" border="1"
                                    style="transform: rotate(-180deg);" ></a>
                            </div>
                            <div class="col-md-4 taC">
                                <a href="?upRotate={{ $upRow->up_id }}&rots=3" style="border: 1px;"
                                    ><img src="{{ $upDeets[$i]['filePub'] }}" 
                                    class="w100 mT30 mB30" border="1"
                                    style="transform: rotate(-270deg);" ></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <div class="fC"></div>
    
@endif
