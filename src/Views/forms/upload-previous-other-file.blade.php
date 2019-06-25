<!-- resources/views/survloop/forms/upload-previous-other-file.blade.php -->
<div class="w100 disBlo bgPrimary vaM" style="height: {{ (2+$height) }}px;">
    <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" 
        class="disBlo w100 taC vaM wht" style="height: {{ $height }}px;"
        ><div class="fPerc300 mBn5"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></div>
        {{ $upRow->UpUploadFile }}
    </a>
</div>
