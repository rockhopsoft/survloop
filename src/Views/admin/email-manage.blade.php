<!-- resources/views/vendor/survloop/admin/email-manage.blade.php -->

@extends('vendor.survloop.master')

@section('content')

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
                ><i class="fa fa-pencil fa-flip-horizontal mT10 f18" aria-hidden="true"></i></a>
        </div>
        <div class="col-10 pT0">
            <h3 class="m0 p0"><a class="emailLnk" id="showEmail{{ $email->EmailID }}" href="javascript:;"
                >{{ $email->EmailSubject }}</a></h3>
            <span class="slGrey">
                @if ($email->EmailType == 'Blurb')
                    [{ <a class="emailLnk fPerc133" id="showEmail{{ $email->EmailID }}" href="javascript:;"
                        ><i>{{ $email->EmailName }}</i></a> }]
                @else
                    <span class="fPerc133">{{ $email->EmailName }}</span>
                @endif
                <span class="mL10 fPerc133">[{{ $email->EmailType }}]</span>
            </span>
        </div>
        <div class="col-1 taC f18">
            @if ($email->EmailType != 'Blurb')
                {{ number_format($email->EmailTotSent, 0) }} 
                <a href="#"><i class="fa fa-paper-plane" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>
    <div id="emailBody{{ $email->EmailID }}" class="emailBody row pB20 fPerc133 @if ($i%2 == 0) row2 @endif 
        @if ($isAll) disBlo @else disNon @endif ">
        <div class="col-1"></div>
        <div class="col-10 pB20">{!! 
            view('vendor.survloop.emails.master', [ 
                "emaTitle"   => $email->EmailName,
                "emaContent" => $email->EmailBody,
                "cssColors"  => $cssColors
            ])->render()
        !!}</div>
        <div class="col-1"></div>
    </div>
@empty
    <i>No emails found!?!</i>
@endforelse

<div class="adminFootBuff"></div>

@endsection