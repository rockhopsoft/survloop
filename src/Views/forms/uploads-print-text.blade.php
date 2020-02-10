<!-- resources/views/survloop/forms/uploads-print-text.blade.php -->
@if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->up_id)
    <div class="nodeAnchor"><a name="up{{ $upRow->up_id }}"></a></div>

    <div class="pT5 pB10">
        @if (trim($upRow->up_title) != '') 
            {{  $upRow->up_title }}<br />
        @endif
    @if (intVal($upRow->up_type) == $vidTypeID)

        @if ($canShow)
            @if (trim($upDeets["youtube"]) != '')
                {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['youtube'], true) !!}
            @elseif (trim($upDeets["vimeo"]) != '')
                {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['vimeo'], true) !!}
            @endif
        @else
            <i>Video URL</i>
        @endif

    @elseif (isset($upRow->up_upload_file) 
        && isset($upRow->up_stored_file) 
        && trim($upRow->up_upload_file) != '' 
        && trim($upRow->up_stored_file) != '')

        @if (in_array($upDeets["ext"], array("gif", "jpeg", "jpg", "png")))
            @if ($canShow)
                <a href="{{ $upDeets['filePub'] }}" target="_blank" class="fPerc80"
                    >{{ $upDeets['filePub'] }}</a>
            @else
                <i>Image URL</i>
            @endif
        @else
            @if ($canShow)
                <a href="{{ $upDeets['filePub'] }}" target="_blank" class="fPerc80"
                    >{{ $upDeets['filePub'] }}</a>
            @else
                <i>PDF URL</i>
            @endif
        @endif

    @endif
    </div>
@endif