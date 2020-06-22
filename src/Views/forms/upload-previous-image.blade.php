<!-- resources/views/survloop/forms/upload-previous-image.blade.php -->
<div class="w100 disBlo vaM" @if (!$GLOBALS['SL']->isPrintView())
	style="height: {{ (2+$height) }}px; overflow: hidden;" @endif >
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ $upDeets[$i]['filePub'] }}?orig=1"
        class="disBlo {{ $upDeets[$i]['imgClass'] }} " target="_blank"
        ><img @if ($GLOBALS['SL']->isPrintView()) border=0 @else border=1 @endif
            src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
            }}{{ (($refresh) ? $upDeets[$i]['fileFrsh'] : $upDeets[$i]['filePub']) }}" 
            class=" {{ $upDeets[$i]['imgClass'] }} "
            alt="{{ ((isset($upRow->up_stored_file)) 
                ? $upRow->up_stored_file : 'Uploaded Image') 
            }}"></a>
</div>
