<!-- resources/views/survloop/forms/uploads-print-title.blade.php -->
<p>
@if (trim($upRow->UpTitle) != '') <span class="mR10">{{  $upRow->UpTitle }}</span> @endif
@if ($isAdmin || $isOwner)
    <span class="slGrey">
        @if ($upRow->UpPrivacy == 'Public') (Public) @else (Private) @endif
    </span>
    <div class="mTn10 fPerc80 slGrey">{!! $upRow->UpUploadFile !!}</div>
@endif
</p>