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
						@if (trim($upRow->type) == $GLOBALS["DB"]->getDefID($GLOBALS["DB"]->sysOpts["upload-types"], 'Video'))
							@if (trim($upDeets[$i]['youtube']) != '')
								<iframe id="ytplayer{{ $upRow->id }}" type="text/html" width="80%" height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
									src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" ></iframe>
							@elseif (trim($upDeets[$i]['vimeo']) != '')
								<iframe id="ytplayer{{ $upRow->id }}" type="text/html" width="80%" height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
									src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" ></iframe>
							@endif
						@else
							@if (in_array($upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png")))
								<a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" class="disBlo w100 taC" 
									><img src="{{ $upDeets[$i]['filePub'] }}" style="height: {{ $height }}px;" border=1 ></a>
							@else 
								<a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" class="disBlo w100 taC fPerc125" style="height: {{ (2+$height) }}px;"
									><div class="p20 mT10 f36"><i class="fa fa-file-pdf-o"></i></div><b>{{ $upRow->upFile }}</b></a>
							@endif
						@endif
					</div>
					<div class="col-md-6">
						
						<div id="up{{ $upRow->id }}Info" class="disBlo fL p20">
							<span class="fPerc125"><b>{{  $upRow->title }}</b></span> 
							@if (trim($upRow->desc) != '') <br />{{ $upRow->desc }} @endif
							<div class="gry9 pT10">{{ $GLOBALS["DB"]->getDefValById($upRow->type) }}, @if ($upRow->privacy == 'Open') Public @else Private @endif</div>
							{!! $upDeets[$i]["fileLnk"] !!}
						</div>
						
						<div id="up{{ $upRow->id }}InfoEdit" class="disNon fL pT10 pB10 pL20">
							<input type="hidden" name="up{{ $upRow->id }}EditVisib" id="up{{ $upRow->id }}EditVisibID" value="0">
							<div class="row nFld">
								<div class="col-md-4">
									<label for="up{{ $upRow->id }}EditTitleID">Title:</label> 
								</div>
								<div class="col-md-8">
									<input type="text" name="up{{ $upRow->id }}EditTitle" id="up{{ $upRow->id }}EditTitleID" value="{{ $upRow->title }}" class="form-control"><br />
								</div>
							</div>
							<div class="row nFld">
								<div class="col-md-4">
									<label for="up{{ $upRow->id }}EditDescID">Description:</label> 
								</div>
								<div class="col-md-8">
									<input type="text" name="up{{ $upRow->id }}EditDesc" id="up{{ $upRow->id }}EditDescID" value="{{ $upRow->desc }}" class="form-control"><br />
								</div>
							</div>
							<div class="row nFld">
								<div class="col-md-4">
									<label for="up{{ $upRow->id }}EditTypeID">Type:</label> 
								</div>
								<div class="col-md-8">
									<select name="up{{ $upRow->id }}EditType" id="up{{ $upRow->id }}EditTypeID" class="form-control">
										@foreach ($uploadTypes as $i => $ty)
											<option value="{{ $ty->DefID }}" @if ($ty->DefID == $upRow->type) SELECTED @endif >{{ $ty->DefValue }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="row nFld mT20 @if ($v['isPublic']) disNon @endif ">
								<div class="col-md-4">
									<label for="up{{ $upRow->id }}EditPrivacyID">Privacy:</label> 
								</div>
								<div class="col-md-8">
									<select name="up{{ $upRow->id }}EditPrivacy" id="up{{ $upRow->id }}EditPrivacyID" class="form-control">
										<option value="Public" @if ($upRow->privacy == 'Public') SELECTED @endif >Visible To Public</option>
										<option value="Private" @if ($upRow->privacy != 'Public') SELECTED @endif >Private: Visible Only To Investigators</option>
									</select>
								</div>
							</div>
							<center><input type="submit" value="Save Changes" class="btn btn-lg btn-primary nFormUploadSave mT10"></center>
						</div>
						
					</div>
					<div class="col-md-2 pT10">
					
						<div class="mB20">
							<a href="#up{{ $upRow->id }}" class="nFormLnkEdit btn btn-md btn-default f22 slBlueDark mR20" 
								id="editLoopItem{{ $upRow->id }}"><i class="fa fa-pencil fa-flip-horizontal"></i></a>
							<a href="javascript:;" class="nFormLnkDel btn btn-xs btn-danger wht round20 mL20" 
								id="delLoopItem{{ $upRow->id }}"><i class="fa fa-times"></i></a>
						</div>
						
						<div id="delLoopItem{{ $upRow->id }}confirm" class="nFormLnkDelConfirm red brdRed round10 p5 taC disNon">
							<div class="pB10">Delete this upload?</div>
							<a href="javascript:;" class="nFormLnkDelConfirmYes btn btn-md btn-default red mR20" 
								id="delLoopItem{{ $upRow->id }}confirmY">Yes</a>
							<a href="javascript:;" class="nFormLnkDelConfirmNo btn btn-md btn-default" 
								id="delLoopItem{{ $upRow->id }}confirmN">No</a>
							<div class="fC"></div>
						</div>
						
					</div>
				</div>
			@endif
		@endforeach
		<div class="fC"></div>
	</div>
	
@endif