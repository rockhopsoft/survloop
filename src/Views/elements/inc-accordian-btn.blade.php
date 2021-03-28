<!-- resources/views/vendor/survloop/elements/inc-accordian-btn.blade.php -->

<i id="hidivBtnAcc{{ $accordID }}" aria-hidden="true"
@if ($ico == 'caret')
    @if ($open) class="fa fa-caret-up mTn5"
    @else class="fa fa-caret-down mTn5"
    @endif style="font-size: 22px; color: #000;"
@else
    @if ($open) class="fa fa-chevron-up"
    @else class="fa fa-chevron-down"
    @endif
    @if ($big) style="margin-top: -3px;"
    @elseif ($type == 'text') style="font-size: 16px;"
    @elseif ($type == 'textL') style="font-size: 14px; margin-right: 5px;"
    @endif
@endif ></i>