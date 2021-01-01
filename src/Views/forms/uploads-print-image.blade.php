<!-- resources/views/survloop/forms/uploads-print-image.blade.php -->
@if (!$GLOBALS['SL']->isPrintView() && !$GLOBALS['SL']->isPdfView())

<div class="w100 disBlo brdInfo" 
    style="height: {{ (2+$height) }}px; overflow: hidden;">
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $upDeets['filePub'] }}?orig=1" 
        target="_blank" class="disBlo w100" 
        ><img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
            (($refresh) ? $upDeets['fileFrsh'] : $upDeets['filePub']) }}" 
            @if ($GLOBALS['SL']->isPrintView()) border="0" 
            @else border="1" 
            @endif
            alt="{{ ((isset($upRow->up_title)) 
                ? $upRow->up_title : 'Uploaded Image') }}"
            class="w100" ></a>
</div>

@endif
