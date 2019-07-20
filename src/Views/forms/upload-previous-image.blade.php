<!-- resources/views/survloop/forms/upload-previous-image.blade.php -->
<div class="w100 disBlo vaM" @if (!$GLOBALS['SL']->isPrintView())
	style="height: {{ (2+$height) }}px; overflow: hidden;" @endif >
    <a href="{{ $upDeets[$i]['filePub'] }}" target="_blank" 
        class="disBlo {{ $upDeets[$i]['imgClass'] }} " 
        ><img @if ($GLOBALS['SL']->isPrintView()) border=0 @else border=1 @endif
        src="{{ $upDeets[$i]['filePub'] }}" class=" {{ $upDeets[$i]['imgClass'] }} "
        alt="{{ ((isset($upRow->UpStoredFile)) ? $upRow->UpStoredFile : 'Uploaded Image') 
        }}"></a>
</div>
