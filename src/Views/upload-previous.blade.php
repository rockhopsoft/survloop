<!-- Stored in resources/views/survloop/upload-previous.blade.php -->

@if (!$uploads || sizeof($uploads) == 0) 
    <h3><span class="gry9">Nothing uploaded here.</span></h3>
@else
    <h2 style="margin-top: 0px;"><i class="fa fa-cloud-upload"></i> Uploaded:</h2>
    @foreach ($uploads as $i => $upRow)
        @if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->id)
            <a name="up{{ $upRow->id }}"></a>
            <div class="row uploadedWrap">
            
                <div class="col-md-4 m0 taC">
                
                    @if (intVal($upRow->type) == $vidTypeID)
                        @if (trim($upDeets[$i]["youtube"]) != '')
                            <iframe id="ytplayer{{ $upRow->id }}" type="text/html" width="100%" 
                                height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
                                src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" 
                                ></iframe>
                        @elseif (trim($upDeets[$i]["vimeo"]) != '')
                            <iframe id="vimplayer{{ $upRow->id }}" width="100%" height="{{ $height }}" class="mBn5"
                                frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                                src="https://player.vimeo.com/video/{{ $upDeets[$i]['vimeo'] }}" 
                                ></iframe>
                        @endif
                    @else
                        @if (in_array($upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png")))
                            <div class="w100 disBlo" style="height: {{ (2+$height) }}px; overflow: hidden;">
                                <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" class="disBlo w100" 
                                    ><img src="{{ $upDeets[$i]['filePub'] }}" border=1 class="w100"></a>
                            </div>
                        @else 
                            <div class="w100 disBlo BGblueLight vaM" style="height: {{ (2+$height) }}px;">
                                <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" 
                                    class="disBlo w100 taC vaM fPerc125 wht" style="height: {{ $height }}px;"
                                    ><div class="f60 wht"><i class="fa fa-file-pdf-o"></i></div>
                                    @if (strlen($upRow->upFile) > 40) <h4 class="wht">{{ $upRow->upFile }}</h4>
                                    @else <h3 class="wht">{{ $upRow->upFile }}</h3>
                                    @endif
                                </a>
                            </div>
                        @endif
                    @endif
                    
                </div>
                <div class="col-md-6 pT10">
                    
                    <div id="up{{ $upRow->id }}Info" class="disBlo fL">
                        <h2>{{  $upRow->title }}</h2> 
                        @if (trim($upRow->desc) != '') <div class="fPerc125">{{ $upRow->desc }}</div> @endif
                        <div class="gry9 pT5">
                            @if ($upRow->privacy == 'Open') Public @else Private @endif
                            {{ $GLOBALS['SL']->getDefValById($upRow->type) }}
                        </div>
                        {!! $upDeets[$i]["fileLnk"] !!}
                    </div>
                    
                    <div id="up{{ $upRow->id }}InfoEdit" class="disNon fL pT10 pB10">
                        <input type="hidden" name="up{{ $upRow->id }}EditVisib" id="up{{ $upRow->id }}EditVisibID" 
                            value="0">
                        <div class="nPrompt"><label for="up{{ $upRow->id }}EditTitleID">Title:</label></div>
                        <div class="nFld"><input type="text" class="form-control" name="up{{ $upRow->id }}EditTitle" 
                            id="up{{ $upRow->id }}EditTitleID" value="{{ $upRow->title }}"></div>
                        <?php /* <div class="nodeGap"></div>
                        <div class="nPrompt"><label for="up{{ $upRow->id }}EditDescID">Description:</label></div>
                        <div class="nFld"><input type="text" name="up{{ $upRow->id }}EditDesc" class="form-control" 
                            id="up{{ $upRow->id }}EditDescID" value="{{ $upRow->desc }}"></div> */ ?>
                        <div class="nodeGap"></div>
                        <div class="nPrompt"><label for="up{{ $upRow->id }}EditTypeID">Type:</label></div>
                        <div class="nFld"><select name="up{{ $upRow->id }}EditType" id="up{{ $upRow->id }}EditTypeID" 
                            class="form-control">
                                @foreach ($uploadTypes as $i => $ty)
                                    <option value="{{ $ty->DefID }}" 
                                        @if ($ty->DefID == $upRow->type) SELECTED @endif 
                                    >{{ $ty->DefValue }}</option>
                                @endforeach
                            </select></div>
                        <div class="nodeGap"></div>
                        <div class="nPrompt"><label for="up{{ $upRow->id }}EditPrivacyID">Privacy:</label></div>
                        <div class="nFld"><select name="up{{ $upRow->id }}EditPrivacy" 
                            id="up{{ $upRow->id }}EditPrivacyID" class="form-control">
                            <option value="Public" @if ($upRow->privacy == 'Public') SELECTED @endif 
                                >Public: Visible to whole world</option>
                            <option value="Private" @if ($upRow->privacy != 'Public') SELECTED @endif 
                                >Private: Visible only to investigators</option>
                            </select></div>
                        <div class="nodeGap"></div>
                        <input type="submit" value="Save Changes" class="btn btn-lg btn-primary w100 nFormUploadSave" 
                            style="color: #FFF; font-size: 18pt; padding: 10px;">
                    </div>
                    
                </div>
                <div class="col-md-2 m0 taR">
                    <a href="javascript:;" id="editLoopItem{{ $upRow->id }}" 
                        class="editLoopItem btn btn-default m10 mT20 fR"
                        ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                    <a href="javascript:;" id="delLoopItem{{ $upRow->id }}" 
                        class="delLoopItem nFormLnkDel nobld btn btn-default m10 mT20 fR"
                        ><i class="fa fa-times"></i> Delete</a>
                    <div class="fC"></div>
                    <div id="delLoopItem{{ $upRow->id }}confirm" 
                        class="nFormLnkDelConfirm red brdRed round10 mB20 p10 pB20 taC disNon">
                        <div class="pB10">Delete this upload?</div>
                        <a href="javascript:;" class="nFormLnkDelConfirmYes btn btn-md btn-default red m5 disIn" 
                            id="delLoopItem{{ $upRow->id }}confirmY">Yes</a>
                        <a href="javascript:;" class="nFormLnkDelConfirmNo btn btn-md btn-default m5 disIn" 
                            id="delLoopItem{{ $upRow->id }}confirmN">No</a>
                    </div>
                </div>
                
            </div>
        @endif
    @endforeach
    <div class="fC"></div>
    
@endif