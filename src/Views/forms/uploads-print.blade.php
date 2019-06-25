<!-- resources/views/survloop/forms/uploads-print.blade.php -->
@if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->UpID)
    <a name="up{{ $upRow->UpID }}"></a>
    @if ($GLOBALS['SL']->isPrintView())
        {!! view('vendor.survloop.forms.uploads-print-title', [
            "upRow"   => $upRow,
            "isAdmin" => $isAdmin,
            "isOwner" => $isOwner
        ])->render() !!}
    @endif
    @if (intVal($upRow->UpType) == $vidTypeID)
        @if ($canShow)
            {!! view('vendor.survloop.forms.uploads-print-youtube', [
                "height"  => $height,
                "upDeets" => $upDeets,
                "upRow"   => $upRow
            ])->render() !!}
        @else
            {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                "canShow" => $canShow,
                "height"  => $height,
                "icon"    => '<i class="fa fa-video-camera" aria-hidden="true"></i>',
                "link"    => ''
            ])->render() !!}
        @endif
    @elseif (isset($upRow->UpUploadFile) && isset($upRow->UpStoredFile) && trim($upRow->UpUploadFile) != '' 
        && trim($upRow->UpStoredFile) != '')
        @if (in_array($upDeets["ext"], array("gif", "jpeg", "jpg", "png")))
            @if ($canShow)
                {!! view('vendor.survloop.forms.uploads-print-image', [
                    "height"  => $height,
                    "upDeets" => $upDeets,
                    "upRow"   => $upRow
                ])->render() !!}
            @else
                {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                    "canShow" => $canShow,
                    "height"  => $height,
                    "icon"    => '<i class="fa fa-file-image-o" aria-hidden="true"></i>',
                    "link"    => ''
                ])->render() !!}
            @endif
        @else
            {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                "canShow" => $canShow,
                "height"  => $height,
                "icon"    => '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                "link"    => $upDeets['filePub']
            ])->render() !!}
        @endif
    @endif
    @if (!$GLOBALS['SL']->isPrintView())
        {!! view('vendor.survloop.forms.uploads-print-title', [
            "upRow"   => $upRow,
            "isAdmin" => $isAdmin,
            "isOwner" => $isOwner
        ])->render() !!}
    @endif
@endif