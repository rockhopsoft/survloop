<!-- resources/views/survloop/forms/uploads-print.blade.php -->
@if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->up_id)
    <div class="nodeAnchor"><a name="up{{ $upRow->up_id }}"></a></div>
    <div class="pB20 mB20 page-break-avoid">
    @if (intVal($upRow->up_type) == $vidTypeID)

        <div class="mB10">
        @if ($canShow)
            {!! view(
                'vendor.survloop.forms.uploads-print-youtube', 
                [
                    "height"  => $height,
                    "upDeets" => $upDeets,
                    "upRow"   => $upRow
                ]
            )->render() !!}
        @else
            {!! view(
                'vendor.survloop.forms.uploads-print-no-preview', 
                [
                    "canShow" => $canShow,
                    "height"  => $height,
                    "icon"    => '<i class="fa fa-video-camera" aria-hidden="true"></i>',
                    "link"    => ''
                ]
            )->render() !!} 
        @endif
        </div>

    @elseif (isset($upRow->up_upload_file) 
        && isset($upRow->up_stored_file) 
        && trim($upRow->up_upload_file) != '' 
        && trim($upRow->up_stored_file) != '')

        @if (!$GLOBALS["SL"]->REQ->has('pdf'))
            <div class="mB10">
            @if (in_array($upDeets["ext"], array("gif", "jpeg", "jpg", "png")))
                @if ($canShow)
                    {!! view(
                        'vendor.survloop.forms.uploads-print-image', 
                        [
                            "height"  => $height,
                            "upDeets" => $upDeets,
                            "upRow"   => $upRow,
                            "refresh" => $GLOBALS["SL"]->REQ->has('refresh')
                        ]
                    )->render() !!}
                @else
                    {!! view(
                        'vendor.survloop.forms.uploads-print-no-preview', 
                        [
                            "canShow" => $canShow,
                            "height"  => $height,
                            "icon"    => '<i class="fa fa-file-image-o" aria-hidden="true"></i>',
                            "link"    => ''
                        ]
                    )->render() !!}
                @endif
            @else
                {!! view(
                    'vendor.survloop.forms.uploads-print-no-preview', 
                    [
                        "canShow" => $canShow,
                        "height"  => $height,
                        "icon"    => '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                        "link"    => $upDeets['filePub']
                    ]
                )->render() !!}
            @endif
            </div>
        @endif

    @endif

        {!! view(
            'vendor.survloop.forms.uploads-print-title', 
            [
                "cnt"     => $cnt,
                "upRow"   => $upRow,
                "isAdmin" => $isAdmin,
                "isOwner" => $isOwner,
                "ext"     => $upDeets["ext"]
            ]
        )->render() !!}

    @if ($GLOBALS["SL"]->REQ->has('pdf')
        && intVal($upRow->up_type) != $vidTypeID
        && isset($upRow->up_upload_file) 
        && isset($upRow->up_stored_file) 
        && trim($upRow->up_upload_file) != '' 
        && trim($upRow->up_stored_file) != '')
        <div><br /><i>Image included at the end of this report.</i></div>
    @endif

    </div>
@endif