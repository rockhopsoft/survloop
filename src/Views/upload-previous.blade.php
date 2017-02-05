<!-- Stored in resources/views/survloop/upload-previous.blade.php -->

@if (!$uploads || sizeof($uploads) == 0) 
    <div class="uploadWrap gry9 p10" style="margin-top: 1px;"><i>Nothing uploaded here.</i></div>
@else
    <div class="uploadWrap" style="margin-top: 1px;">
        <h2 style="margin-top: 0px;"><i class="fa fa-cloud-upload"></i> Uploaded:</h2>
        @foreach ($uploads as $i => $upRow)
            @if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->id)
                <a name="up{{ $upRow->id }}"></a>
                <div class="row brdDrk round20 mB20">
                
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
                    <div class="col-md-6">
                        
                        <div id="up{{ $upRow->id }}Info" class="disBlo fL pL20">
                            <h3>{{  $upRow->title }}</h3> 
                            <p class="fPerc125"> @if (trim($upRow->desc) != '') {{ $upRow->desc }} @endif </p>
                            <div class="gry9 pT10">
                                {{ $GLOBALS["DB"]->getDefValById($upRow->type) }}, 
                                @if ($upRow->privacy == 'Open') Public @else Private @endif
                            </div>
                            {!! $upDeets[$i]["fileLnk"] !!}
                        </div>
                        
                        <div id="up{{ $upRow->id }}InfoEdit" class="disNon fL pT10 pB10 pL20">
                            <input type="hidden" name="up{{ $upRow->id }}EditVisib" id="up{{ $upRow->id }}EditVisibID" 
                                value="0">
                            <div class="row nFld">
                                <div class="col-md-4">
                                    <label for="up{{ $upRow->id }}EditTitleID">Title:</label> 
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="up{{ $upRow->id }}EditTitle" 
                                        id="up{{ $upRow->id }}EditTitleID" value="{{ $upRow->title }}"><br />
                                </div>
                            </div>
                            <div class="row nFld">
                                <div class="col-md-4">
                                    <label for="up{{ $upRow->id }}EditDescID">Description:</label> 
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="up{{ $upRow->id }}EditDesc" class="form-control" 
                                        id="up{{ $upRow->id }}EditDescID" value="{{ $upRow->desc }}"><br />
                                </div>
                            </div>
                            <div class="row nFld">
                                <div class="col-md-4">
                                    <label for="up{{ $upRow->id }}EditTypeID">Type:</label> 
                                </div>
                                <div class="col-md-8">
                                    <select name="up{{ $upRow->id }}EditType" id="up{{ $upRow->id }}EditTypeID" 
                                        class="form-control">
                                        @foreach ($uploadTypes as $i => $ty)
                                            <option value="{{ $ty->DefID }}" 
                                                @if ($ty->DefID == $upRow->type) SELECTED @endif 
                                            >{{ $ty->DefValue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row nFld mT20 @if ($v['isPublic']) disNon @endif ">
                                <div class="col-md-4">
                                    <label for="up{{ $upRow->id }}EditPrivacyID">Privacy:</label> 
                                </div>
                                <div class="col-md-8">
                                    <select name="up{{ $upRow->id }}EditPrivacy" id="up{{ $upRow->id }}EditPrivacyID" 
                                        class="form-control">
                                        <option value="Public" @if ($upRow->privacy == 'Public') SELECTED @endif 
                                            >Public: Visible to whole world</option>
                                        <option value="Private" @if ($upRow->privacy != 'Public') SELECTED @endif 
                                            >Private: Visible only to investigators</option>
                                    </select>
                                </div>
                            </div>
                            <center><input type="submit" value="Save Changes" 
                                class="btn btn-lg btn-primary nFormUploadSave mT10"></center>
                        </div>
                        
                    </div>
                    <div class="col-md-2 m0 taL">
                    
                        <a href="javascript:;" id="editLoopItem{{ $upRow->id }}"
                            class="nFormLnkEdit btn btn-md btn-default f22 slBlueDark fL m20"
                            ><i class="fa fa-pencil fa-flip-horizontal"></i></a>
                        <a href="javascript:;" class="nFormLnkDel btn btn-xs btn-danger wht round20 fL m20" 
                            id="delLoopItem{{ $upRow->id }}"><i class="fa fa-times"></i></a>
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
    </div>
    
@endif