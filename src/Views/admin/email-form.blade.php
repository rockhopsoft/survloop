<!-- resources/views/vendor/survloop/admin/email-form.blade.php -->

@extends('vendor.survloop.master')

@section('content')

@if ($currEmailID > 0) 
    <h2 class="mB0">Editing Email Template: {{ $currEmail->EmailName }}</h2> 
@else
    <h2>Create New Email Template</h2>
@endif
<div class="p5"></div>

<form name="mainPageForm" action="/dashboard/email/{{ $currEmailID }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="emailID" value="{{ $currEmailID }}" >

<div class="row pB20">
    <div class="col-md-3">
        <h3 class="m0 slGrey">Auto-Email Type</h3>
    </div>
    <div class="col-md-9">
        <select name="emailType" class="form-control input-lg" 
            onChange="if (this.value == 'Blurb') { document.getElementById('subj').style.display='none'; } else { document.getElementById('subj').style.display='block'; }" >
            <option value="To Complainant" @if ($currEmail->EmailType == 'To Complainant' || trim($currEmail->EmailType) == '') SELECTED @endif >Sent To Complainant</option>
            <option value="To Oversight" @if ($currEmail->EmailType == 'To Oversight') SELECTED @endif >Sent To Oversight Agency</option>
            <option value="Blurb" @if ($currEmail->EmailType == 'Blurb') SELECTED @endif >Excerpt used within other emails</option>
        </select>
    </div>
</div>

<div class="row pB20">
    <div class="col-md-3">
        <h3 class="m0 slGrey">Internal Name</h3>
    </div>
    <div class="col-md-9">
        <input type="text" name="emailName" value="{{ $currEmail->EmailName }}" class="form-control input-lg" >
    </div>     
</div>

<div id="subj" class="row pB20 @if ($currEmail->EmailType == 'Blurb') disNon @else disBlo @endif ">
    <div class="col-md-3">
        <h3 class="m0 slGrey">Email Subject Line</h3>
    </div>
    <div class="col-md-9">
        <input type="text" name="emailSubject" value="{{ $currEmail->EmailSubject }}" class="form-control input-lg" >
    </div>
</div>

<div class="row pB20">
    <div class="col-md-3">
        <h3 class="m0 slGrey">Email Body</h3>
        <div class="p20"></div>
        <input type="submit" class="btn btn-xl btn-primary w100" value="Save Email Template">
    </div>
    <div class="col-md-9">
        <textarea name="emailBody" id="emailBodyID" class="form-control input-lg" style="height: 500px;">{{ $currEmail->EmailBody }}</textarea>
    </div>
</div>

</form>

<!--- {{ $currEmail->EmailOpts }} --->

<div class="adminFootBuff"></div>

@endsection