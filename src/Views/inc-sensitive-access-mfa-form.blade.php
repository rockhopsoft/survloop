<!-- resources/views/vendor/survloop/inc-sensitive-access-mfa-form.blade.php -->
<div class="row row2 p10">
    <div class="col-md-4 p20">
        <p>
        You have used a special URL to access the full details of this record via <b>{{ $user->email }}</b>. 
        To finished gaining full access, either enter the Access Code sent to you, or 
        <a href="/login">login</a> using {{ $user->email }}.
        </p><p>
        If the account for {{ $user->email }} has not really been setup yet, you can use the 
        <a href="/password/reset">reset password tool</a> to gain access to it by email.
        This will also make it easier for you to access full records in the future.
        </p>
    </div>
    <div class="col-md-4 taC">
        <div class="round20 brd taC p20 mT10"><center>
            <form method="post" name="accessCode" action="?sub=1">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <h3 class="mT10 mB5">Provide Access Code:</h3>
            <input type="text" class="form-control input-lg taC slGrey" style="width: 190px;"
                name="t2" id="t2ID" value="XXXX-XXXX-XXXX"
                onFocus="if (this.value=='XXXX-XXXX-XXXX') { this.value=''; this.className='form-control input-lg taC'; }"
                onBlur="if (this.value=='') { this.value='XXXX-XXXX-XXXX'; this.className='form-control input-lg taC slGrey'; }">
            <input type="submit" value="Access Full Details" class="btn btn-primary m10">
            </form>
        </center></div>
    </div>
    <div class="col-md-4 p20">
        <p>Only the most recently emailed access code will work, only for the week after it is sent.
        For security, your access code expires after 15 minutes. 
        If your access code has expired or does not work, please click the following button 
        to have a fresh access code quickly sent to <b>{{ $user->email }}</b>: <br />
        <a href="?resend=access" class="btn btn-sm btn-default mL10 mT20 fR">Send Fresh Access Code</a>
        <div class="fC"></div>
    </div>
</div>