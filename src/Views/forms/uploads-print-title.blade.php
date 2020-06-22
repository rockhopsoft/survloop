<!-- resources/views/survloop/forms/uploads-print-title.blade.php -->
<p>
@if (trim($upRow->up_title) != '') 
    <span class="mR10">{{  $upRow->up_title }}</span>
    <?php /*
    @if ($GLOBALS["SL"]->isPdfView() && $ext == 'pdf')
        <span class="mR10">({{ strtoupper($ext) }} attached below)</span>
    @endif
    */ ?>
@endif
@if ($isAdmin || $isOwner)
    <span class="slGrey">
        @if ($upRow->up_privacy == 'Public') (Public) @else (Private) @endif
    </span>
    <div class="mTn10 fPerc80 slGrey">{!! $upRow->up_upload_file !!}</div>
@endif
</p>