@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/vendor/survloop/admin/email-manage.blade.php -->

<div class="row mB20">
    <div class="col-6">
        <h2 class="mB0">Manage Email Templates</h2>
        <div class="slGrey">(eg. in response to completing a survey)</div>
    </div>
    <div class="col-6 pT20 taR">
        <a href="/dashboard/email/-3" class="btn btn-secondary">Create New Email Template</a>
        <a href="javascript:;" class="btn btn-secondary mL20" id="showAll">Show/Hide All Email Bodies</a>
    </div>
</div>

<div class="fL slGrey pL20 mL20"><h4 class="disIn m0">Email Subject Line</h4> Email Type; Internal Name</div>
<div class="fR slGrey pR20">Emails Sent</div>
<div class="fC pB10"></div>

@forelse ($emailList as $i => $email)
    <div class="row pT10 pB10 @if ($i%2 == 0) row2 @endif ">
        <div class="col-1 taR">
            <a href="/dashboard/email/{{ $email->EmailID }}"
                ><i class="fa fa-pencil fa-flip-horizontal" aria-hidden="true"></i></a>
        </div>
        <div class="col-10">
            <h4 class="m0"><a class="emailLnk" id="showEmail{{ $email->EmailID }}" href="javascript:;">
            {{ $email->EmailSubject }}</a></h4>
            @if ($email->EmailType == 'Blurb')
                [{ <a class="emailLnk" id="showEmail{{ $email->EmailID }}" href="javascript:;"
                    ><i>{{ $email->EmailName }}</i></a> }]
            @else
                {{ $email->EmailName }}
            @endif
            ({{ $email->EmailType }})
            <div id="emailBody{{ $email->EmailID }}" class="emailBody mB20 @if ($i%2 == 0) row2 @endif 
                @if ($isAll) disBlo @else disNon @endif ">
                <div class="slCard">{!! 
                    view('vendor.survloop.emails.master', [ 
                        "emaTitle"   => $email->EmailName,
                        "emaContent" => $email->EmailBody,
                        "cssColors"  => $cssColors
                    ])->render()
                !!}</div>
            </div>
        </div>
        <div class="col-1 taC">
            @if ($email->EmailType != 'Blurb')
                <nobr>{{ number_format($email->EmailTotSent, 0) }} 
                <a href="#"><i class="fa fa-paper-plane" aria-hidden="true"></i></a></nobr>
            @endif
        </div>
    </div>
@empty
    <i>No emails found!?!</i>
@endforelse

<div class="adminFootBuff"></div>

@endsection