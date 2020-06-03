<!-- resources/views/vendor/survloop/admin/email-form.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container"><div class="slCard nodeWrap">
@if ($currEmailID > 0) 
    <h2 class="mB0">Editing Email Template: {{ $currEmail->email_name }}</h2> 
@else
    <h2>Create New Email Template</h2>
@endif
<div class="p5"></div>

<form name="mainPageForm" action="/dashboard/email/{{ $currEmailID }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="emailID" value="{{ $currEmailID }}" >

<h4 class="m0 slGrey">Auto-Email Type</h4>
<select name="emailType" class="form-control form-control-lg mB20">
    <option value="To Complainant" 
        @if ($currEmail->email_type == 'To Complainant' || trim($currEmail->email_type) == '') SELECTED @endif
        >Sent To Complainant</option>
    <option value="To Oversight" @if ($currEmail->email_type == 'To Oversight') SELECTED @endif 
        >Sent To Oversight Agency</option>
    <option value="Blurb" @if ($currEmail->email_type == 'Blurb') SELECTED @endif 
        >Excerpt used within other emails</option>
</select>

<h4 class="m0 slGrey">Internal Name</h4>
<input type="text" name="emailName" value="{{ $currEmail->email_name }}" 
    class="form-control form-control-lg mB20" >

<h4 class="m0 slGrey">Email Subject Line</h4>
<input type="text" name="emailSubject" value="{{ $currEmail->email_subject }}" 
    class="form-control form-control-lg mB20" >

<h4 class="m0 slGrey">Email Body</h4>
<textarea name="emailBody" id="emailBodyID" 
    class="form-control form-control-lg" style="height: 500px;"
    >{{ $currEmail->email_body }}</textarea>

<input type="submit" value="Save Email Template" 
    class="btn btn-lg btn-xl btn-primary btn-block">
</form>
<!--- {{ $currEmail->email_opts }} --->
</div></div>
<div class="adminFootBuff"></div>
@endsection