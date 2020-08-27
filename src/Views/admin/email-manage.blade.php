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
    Internal Name<br />
    Email Type, Attachments
</div>
<div class="fR slGrey pR20">Emails Sent</div>
<div class="fC pB10"></div>

@forelse ($emailList as $i => $email)
    <div class="row pT10 pB10 @if ($i%2 == 0) row2 @endif ">
        <div class="col-1 taR">
            <a href="/dashboard/email/{{ $email->email_id }}"
                ><i class="fa fa-pencil fa-flip-horizontal" aria-hidden="true"></i></a>
        </div>
        <div class="col-10">
            <h5><a class="emailLnk" id="showEmail{{ $email->email_id }}" 
                href="javascript:;"><b>{{ $email->email_subject }}</b></a></h5>
            @if ($email->email_type == 'Blurb')
                [{ <a class="emailLnk" id="showEmail{{ $email->email_id }}" 
                    href="javascript:;"><i>{{ $email->email_name }}</i></a> }]
            @else
                {{ $email->email_name }}
            @endif
            <br />{{ $email->email_type }}
            @if (isset($email->email_attach) && $email->email_attach == 'sensitive')
                <i class="fa fa-paperclip mL5" aria-hidden="true"></i>
                <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Sensitive
            @elseif (isset($email->email_attach) && $email->email_attach == 'public')
                <i class="fa fa-paperclip mL5" aria-hidden="true"></i>
                <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Public
            @endif
            <div id="emailBody{{ $email->email_id }}" 
                class="emailBody mB20 @if ($i%2 == 0) row2 @endif 
                @if ($isAll) disBlo @else disNon @endif ">
                <div class="slCard">{!! 
                    view(
                        'vendor.survloop.emails.master', 
                        [ 
                            "emaTitle"   => $email->email_name,
                            "emaContent" => $email->email_body,
                            "cssColors"  => $cssColors
                        ]
                    )->render()
                !!}</div>
            </div>
        </div>
        <div class="col-1 taC">
            @if ($email->email_type != 'Blurb')
                <nobr>{{ number_format($email->email_tot_sent, 0) }} 
                <a href="#"><i class="fa fa-paper-plane" aria-hidden="true"></i></a></nobr>
            @endif
        </div>
    </div>
@empty
    <i>No emails found.</i>
@endforelse
</div>
</div>
<div class="adminFootBuff"></div>
@endsection