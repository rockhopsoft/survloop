<!-- resources/views/vendor/survloop/admin/system-one-setting-textarea.blade.php -->

<textarea name="sys-{{ $opt }}" id="sys-{{ $opt }}-id" 
    class="form-control w100" autocomplete="off" 
    {!! $GLOBALS["SL"]->tabInd() !!} 
    style="font-family: Courier New;
    height: @if (isset($height) && intVal($height) > 0) {{ $height}}px; @else 100px; @endif
    ">@if (isset($GLOBALS["SL"]->sysOpts[$opt])){!! 
        $GLOBALS["SL"]->sysOpts[$opt] 
    !!}@endif</textarea>
