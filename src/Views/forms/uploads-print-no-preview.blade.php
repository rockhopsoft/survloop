<!-- resources/views/survloop/forms/uploads-print-no-preview.blade.php -->
@if (!$GLOBALS["SL"]->isPdfView())
    <div class="w100 disBlo bgInfo brdInfo" style="height: {{ (2+$height) }}px;">
        <a @if (!$canShow) href="javascript:;" 
            @else href="{{ $link }}" target="_blank" 
            @endif 
            class="disBlo w100 taL wht" style="height: {{ $height }}px;">
            <div class="disBlo fPerc400 wht pT20 mT20 pL20 mL20">{!! $icon !!}</div>
        </a>
    </div>
@endif