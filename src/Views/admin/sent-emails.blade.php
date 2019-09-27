@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/vendor/survloop/admin/sent-emails.blade.php -->
<div class="container">
<div class="slCard nodeWrap">
<div class="mB20">
    <h2 class="mB0">Sent Emails</h2>
</div>

@forelse ($emailed as $i => $email)
    {!! $GLOBALS["SL"]->printAccordian(
        $email->EmailedSubject . ' <span class="slGrey fPerc66">' . $email->EmailedTo . '</span>', 
        strip_tags($email->EmailedBody, '<p><br><a><i><b>'), 
        false
    ) !!}
@empty
    <i>No sent emails found!?!</i>
@endforelse
</div>
</div>
<div class="adminFootBuff"></div>
@endsection