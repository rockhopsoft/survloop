@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/vendor/survloop/admin/send-email.blade.php -->
<div class="container">
    <div class="slCard nodeWrap">
        <div class="mB20">
            <h2 class="mB0">Send Email</h2>
        </div>

        <form name="mainPageForm" action="?send=1" method="post">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="redir" 
            @if ($GLOBALS["SL"]->REQ->has('redir'))
                value="{{ trim($GLOBALS["SL"]->REQ->get('redir')) }}"
            @endif >

        <div class="row mB20">
            <div class="col-3">
                <h4 class="slGrey">Email Template</h4>
            </div>
            <div class="col-9">
                <select name="emaTemplate" class="form-control form-control-lg" autocomplete=off 
                    onChange="window.location='?emaTemplate='+this.value;">
                <option value="">Select email template...</option>
                @forelse ($emailList as $i => $template)
                    <option value="{{ $template->email_id }}" 
                        @if ($GLOBALS["SL"]->REQ->has('emaTemplate') 
                            && intVal($GLOBALS["SL"]->REQ->get('emaTemplate')) == $template->email_id)
                            SELECTED
                        @endif
                        >{{ $template->email_name }}, "{{ $template->email_subject }}"</option>
                @empty
                @endforelse
                </select>
            </div>
        </div>

    @foreach (['To', 'CC', 'BCC'] as $type)
        <div class="row mB20">
            <div class="col-3">
                <h4 class="slGrey">Email {{ $type }}</h4>
            </div>
            <div class="col-6">
                <input name="ema{{ $type }}" id="ema{{ $type }}ID" 
                    type="text" class="form-control form-control-lg" autocomplete=off
                    @if ($GLOBALS["SL"]->REQ->has('ema' . $type))
                        value="{{ trim($GLOBALS["SL"]->REQ->get('ema' . $type)) }}"
                    @endif >
            </div>
            <div class="col-3">
                <select name="ema{{ $type }}User"
                    class="form-control form-control-lg" autocomplete=off 
                    onChange="document.getElementById('ema{{ $type }}ID').value=this.value;">
                <option value="">Select user's email address...</option>
                @forelse ($userList as $i => $user)
                    <option value="{{ $user->email }}">
                        {{ $user->name }}, {{ $user->email }}
                    </option>
                @empty
                @endforelse
                </select>
            </div>
        </div>
    @endforeach

        <div class="row mB20">
            <div class="col-3">
                <h4 class="slGrey">Email Subject</h4>
            </div>
            <div class="col-9">
                <input name="emaSubject" type="text" class="form-control form-control-lg" 
                    value="{{ trim($email['subject']) }}" autocomplete=off >
            </div>
        </div>

        <div class="row mB20">
            <div class="col-3">
                <h4 class="slGrey">Email Body</h4>
            </div>
            <div class="col-9">
                <textarea name="emaBody" id="emaBodyID" class="form-control" 
                    autocomplete=off >{{ trim($email["body"]) }}</textarea>
            </div>
        </div>

        <center>
        <input type="submit" class="btn btn-lg btn-primary" value="Send Email">
        </center>

        </form>

    </div>
</div>
<div class="adminFootBuff"></div>
@endsection