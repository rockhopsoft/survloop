<!-- Stored in resources/views/survloop/upload-tool.blade.php -->

<div id="uploadErrors" class="slRedDark"></div>
<div id="up{{ $nID }}Wrap" class="uploadWrap">
    <div class="uploadTypes">
        <h2><i class="fa fa-cloud-upload"></i> New Upload:</h2>
        @foreach ($uploadTypes as $j => $ty)
            <label for="n{{ $nID }}fld{{ $j }}" id="n{{ $nID }}fld{{ $j }}lab" class="finger">
                <div class="disIn mR5"><input id="n{{ $nID }}fld{{ $j }}" name="n{{ $nID }}fld" 
                    value="{{ $ty->DefID }}" type="radio" autocomplete="off" class="upTypeBtn"
                    onClick="checkNodeUp({{ $nID }}, {{ $j }}, 1);" ></div>    
                    {{ $ty->DefValue }}
            </label>
        @endforeach
        
        <div id="up{{ $nID }}Info" class="disNon">
            <div class="nodeGap"></div>
            <div id="up{{ $nID }}FormVideo" class="disNon">
                <div class="nPrompt"><label for="up{{ $nID }}VidID">
                    Video URL: <span class="fPerc66 gry9">
                    (eg. https://www.youtube.com/watch?v=s4nQ_mFJV4I or https://vimeo.com/22914107)</span>
                </label></div>
                <div class="nFld">
                    <input type="text" id="up{{ $nID }}VidID" name="up{{ $nID }}Vid" value="" class="form-control">
                </div>
            </div>
            <div id="up{{ $nID }}FormFile" class="disNon">
                <div class="nPrompt"><label for="up{{ $nID }}FileID">
                    Select Upload File: <span class="fPerc66 gry6">( .PNG .JPG .GIF .PDF )</span>
                </label></div>
                <div class="nFld">
                    <input type="file" name="up{{ $nID }}File" id="up{{ $nID }}FileID" class="p5 form-control" 
                        style="border: 1px #CCC solid;">
                </div>
            </div>
            <div class="nodeGap"></div>
            <div id="node100{{ $nID }}" class="nodeWrap">
                <div id="nLabel100{{ $nID }}" class="nPrompt"><label for="up{{ $nID }}TitleID">Upload Title:</label></div>
                <div class="nFld">
                    <input type="text" id="up{{ $nID }}TitleID" name="up{{ $nID }}Title" value="" class="form-control">
                </div>
            </div>
            <?php /* <div class="nodeGap"></div>
            <div class="nPrompt"><label for="up{{ $nID }}DescID">Upload Description:</label></div>
            <div class="nFld">
                <input type="text" id="up{{ $nID }}DescID" name="up{{ $nID }}Desc" value="" class="form-control">
            </div> */ ?>
            <div class="nodeGap"></div>
            <div class="nPrompt"><label for="up{{ $nID }}VidID">Upload Privacy:</label></div> 
            <div class="nFld">
                <select name="up{{ $nID }}Privacy" id="up{{ $nID }}PrivacyID" class="form-control">
                    <option value="Public" @if ($isPublic) CHECKED @endif >Public: Visible to whole world</option>
                    <option value="Private" @if (!$isPublic) CHECKED @endif >Private: Visible only to investigators</option>
                </select>
            </div>
            <div class="nodeSidenote">
                <span class="fPerc80 red">Be sure to set privacy to "private" for any documents which may contain 
                private information like addresses, phone numbers, emails, social security numbers, etc.</span>
            </div>
            <input type="submit" value="Upload More Evidence" class="btn btn-lg btn-default w100 fPerc125" 
                id="nFormUpload" style="padding: 15px;">
        </div>
        
        <hr>
        
        {!! $getPrevUploads !!}
        
    </div>
</div>

