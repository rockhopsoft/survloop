@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/vendor/survloop/admin/email-manage.blade.php -->
<div class="container">
<div class="slCard nodeWrap">
<div class="mB20">
    <h2 class="mB0">Manage Email Templates</h2>
    <a href="/dashboard/email/-3" class="btn btn-secondary btn-sm"
        >Create New Email Template</a>
    <a class="btn btn-secondary btn-sm mL20" id="showAll"
        href="javascript:;">Show/Hide All Email Bodies</a>
</div>

<div class="fL slGrey pL20 mL20">
    <h5 class="m0">Email Subject Line</h5>
    Internal Name (Email Type)
</div>
<div class="fR slGrey pR20">Emails Sent</div>
<div class="fC pB10"></div>

@forelse ($emailList as $i => $email)
    <div class="row pT10 pB10 @if ($i%2 == 0) row2 @endif ">
        <div class="col-1 taR">
            <a href="/dashboard/email/{{ $email->EmailID }}"
                ><i class="fa fa-pencil fa-flip-horizontal" aria-hidden="true"></i></a>
        </div>
        <div class="col-10">
            <h5><a class="emailLnk" id="showEmail{{ $email->EmailID }}" 
                href="javascript:;"><b>{{ $email->EmailSubject }}</b></a></h5>
            @if ($email->EmailType == 'Blurb')
                [{ <a class="emailLnk" id="showEmail{{ $email->EmailID }}" 
                    href="javascript:;"><i>{{ $email->EmailName }}</i></a> }]
            @else
                {{ $email->EmailName }}
            @endif
            ({{ $email->EmailType }})
            <div id="emailBody{{ $email->EmailID }}" 
                class="emailBody mB20 @if ($i%2 == 0) row2 @endif 
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
</div>
</div>
<div class="adminFootBuff"></div>
@endsection