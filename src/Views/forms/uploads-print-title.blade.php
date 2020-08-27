<!-- resources/views/survloop/forms/uploads-print-title.blade.php -->
<p>
@if (trim($upRow->up_title) != '') 
    <b>Upload #{{ (1+$cnt) }}: {{  $upRow->up_title }}</b>
    <?php /*
    @if ($GLOBALS["SL"]->isPdfView() && $ext == 'pdf')
        <span class="mR10">({{ strtoupper($ext) }} attached below)</span>
    @endif
    */ ?>
@endif
@if (in_array($GLOBALS["SL"]->dataPerms, ['sensitive', 'full-pdf']))
    <span class="slGrey">
        @if ($upRow->up_privacy == 'Public') (Public) @else (Private) @endif
    </span>
    <div class="mTn10 fPerc80 slGrey">{!! $upRow->up_upload_file !!}</div>
@endif
</p>