<!-- resources/views/vendor/survloop/auth/enable-mfa.blade.php -->
<form method="post" action="{{ url('user/two-factor-authentication') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<div class="pT15 pB30">

	<p>
		Two-factor authentication (2FA) is also known as 
		two-step verification or multifactor authentication. 
		It is widely used to add a layer of security when using 
		online accounts like this website.
	</p>
	<input type="submit" class="btn btn-primary"
		value="Enable Two-Factor Authentication">

</div>
</form>