<!-- resources/views/survloop/forms/uploads-print-title.blade.php -->
<p>{{  $upRow->UpTitle }}
@if ($isAdmin || $isOwner)
    <span class="mL10 slGrey">
        @if ($upRow->UpPrivacy == 'Public') (Public) @else (Private) @endif
    </span>
    <div class="mTn10 fPerc66 slGrey">{!! $upRow->UpUploadFile !!}</div>
@endif
</p>