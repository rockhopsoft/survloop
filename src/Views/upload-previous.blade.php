<!-- Stored in resources/views/survloop/upload-previous.blade.php -->
@if (!$uploads || sizeof($uploads) == 0) 
    <h3><span class="slGrey">Nothing uploaded here.</span></h3>
@else
    <h2 style="margin-bottom: 10px;"><i class="fa fa-cloud-upload"></i> {{ sizeof($uploads) }} Previous Uploads:</h2>
    @foreach ($uploads as $i => $upRow)
        @if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->UpID)
            <a name="up{{ $upRow->UpID }}"></a>
            <div class="row uploadedWrap">
                <div class="col-md-4 m0 taC">
                
                    @if (intVal($upRow->type) == $vidTypeID 
                        && (trim($upDeets[$i]["youtube"]) != '' || trim($upDeets[$i]["vimeo"]) != ''))
                        @if (trim($upDeets[$i]["youtube"]) != '')
                            <iframe id="ytplayer{{ $upRow->UpID }}" type="text/html" width="100%" 
                                height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
                                src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" 
                                ></iframe>
                        @elseif (trim($upDeets[$i]["vimeo"]) != '')
                            <iframe id="vimplayer{{ $upRow->UpID }}" width="100%" height="{{ $height }}" class="mBn5"
                                frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                                src="https://player.vimeo.com/video/{{ $upDeets[$i]['vimeo'] }}" 
                                ></iframe>
                        @endif
                    @else
                        @if (in_array($upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png")))
                            <div class="w100 disBlo row2 vaM" 
                                style="height: {{ (2+$height) }}px; overflow: hidden;">
                                <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" 
                                    class="disBlo {{ $upDeets[$i]['imgClass'] }} " ><img border=1 
                                    src="{{ $upDeets[$i]['filePub'] }}" class=" {{ $upDeets[$i]['imgClass'] }} "></a>
                            </div>
                        @else
                            <div class="w100 disBlo row2 vaM" style="height: {{ (2+$height) }}px;">
                                <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" 
                                    class="disBlo w100 taC vaM fPerc133 wht" style="height: {{ $height }}px;"
                                    ><div class="f60 wht pT20"><i class="fa fa-file-pdf-o"></i></div>
                                    @if (strlen($upRow->upFile) > 40) <h4 class="wht">{{ $upRow->upFile }}</h4>
                                    @else <h3 class="wht">{{ $upRow->upFile }}</h3>
                                    @endif
                                </a>
                            </div>
                        @endif
                    @endif
                    
                </div>
                <div class="col-md-6 pT10">
                    
                    <div id="up{{ $upRow->UpID }}Info" class="disBlo fL">
                        <h2>{{  $upRow->UpTitle }}</h2> 
                   <?php /*@if (trim($upRow->UpDesc) != '') <div class="fPerc133">{{ $upRow->UpDesc }}</div> @endif */?>
                        <div class="slGrey pT5">
                            @if (isset($GLOBALS["SL"]->treeSettings["uploads-public"]) 
                                && intVal($GLOBALS["SL"]->treeSettings["uploads-public"][0]) > 0)
                                @if ($upRow->UpPrivacy == 'Open') Public @else Private @endif
                            @endif
                            {{ $GLOBALS['SL']->getDefValById($upRow->type) }}
                        </div>
                        {!! $upDeets[$i]["fileLnk"] !!}
                    </div>
                    
                    <div id="up{{ $upRow->UpID }}InfoEdit" class="disNon pB10">
                        <input type="hidden" name="up{{ $upRow->UpID }}EditVisib" id="up{{ $upRow->UpID }}EditVisibID" 
                            value="0">
                        <div class="nPrompt"><label for="up{{ $upRow->UpID }}EditTitleID">Upload Title:</label></div>
                        <div class="nFld mT0">
                            <input type="text" class="form-control input-lg" name="up{{ $upRow->UpID }}EditTitle" 
                                id="up{{ $upRow->UpID }}EditTitleID" value="{{ $upRow->UpTitle }}">
                        </div>
                        <?php /* <div class="nodeGap"></div>
                        <div class="nPrompt"><label for="up{{ $upRow->UpID }}EditDescID">Description:</label></div>
                        <div class="nFld">
                            <input type="text" name="up{{ $upRow->UpID }}EditDesc" class="form-control input-lg" 
                                id="up{{ $upRow->UpID }}EditDescID" value="{{ $upRow->UpDesc }}"></div> */ ?>
                        @if (sizeof($uploadTypes) > 1)
                            <div class="nodeHalfGap"></div>
                            <div class="nPrompt"><label for="up{{ $upRow->UpID }}EditTypeID">Upload Type:</label></div>
                            <div class="nFld mT0"><select name="up{{ $upRow->UpID }}EditType" 
                                id="up{{ $upRow->UpID }}EditTypeID" class="form-control input-lg">
                                    @foreach ($uploadTypes as $i => $ty)
                                        <option value="{{ $ty->DefID }}" 
                                            @if ($ty->DefID == $upRow->type) SELECTED @endif 
                                            >{{ $ty->DefValue }}</option>
                                    @endforeach
                                </select></div>
                        @endif
                        @if (isset($GLOBALS["SL"]->treeSettings["uploads-public"]) 
                            && intVal($GLOBALS["SL"]->treeSettings["uploads-public"][0]) > 0)
                            <div class="nodeHalfGap"></div>
                            <div class="nPrompt"><label for="up{{ $upRow->UpID }}EditPrivacyID">Privacy:</label></div>
                            <div class="nFld mT0"><select name="up{{ $upRow->UpID }}EditPrivacy" 
                                id="up{{ $upRow->UpID }}EditPrivacyID" class="form-control input-lg">
                                <option value="Public" @if ($upRow->UpPrivacy == 'Public') SELECTED @endif 
                                    >Public: Visible to whole world</option>
                                <option value="Private" @if ($upRow->UpPrivacy != 'Public') SELECTED @endif 
                                    >Private: Visible only to those authorized</option>
                                </select></div>
                        @endif
                        <div class="nodeHalfGap"></div>
                        <input type="submit" value="Save Changes" class="nFormUploadSave btn btn-lg btn-primary w100">
                    </div>
                    
                </div>
                <div class="col-md-2">
                
                    <div id="editLoopItem{{ $upRow->UpID }}block" class="disBlo">
                        <a href="javascript:;" id="editLoopItem{{ $upRow->UpID }}" 
                            class="nFormLnkEdit btn btn-default btn-sm w100 mT10"
                            ><i class="fa fa-pencil fa-flip-horizontal mR5"></i> Edit</a>
                        <a href="javascript:;" id="delLoopItem{{ $upRow->UpID }}" 
                            class="nFormLnkDel nobld btn btn-default btn-sm w100 mT20"
                            ><i class="fa fa-times"></i> Delete</a>
                    </div>
                    <div id="delLoopItem{{ $upRow->UpID }}confirm" 
                        class="nFormLnkDelConfirm red brdRed round10 w100 p5 mT5 disNon">
                        Delete upload?
                        <a href="javascript:;" class="nFormLnkDelConfirmYes btn btn-primary btn-sm w100 mT10 red" 
                            id="delLoopItem{{ $upRow->UpID }}confirmY">Yes</a>
                        <a href="javascript:;" class="nFormLnkDelConfirmNo btn btn-default btn-sm w100 mT5" 
                            id="delLoopItem{{ $upRow->UpID }}confirmN">No</a>
                    </div>
                    
                </div>
            </div>
        @endif
    @endforeach
    <div class="fC"></div>
    
@endif