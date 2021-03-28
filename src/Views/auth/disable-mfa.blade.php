<!-- resources/views/vendor/survloop/auth/disable-mfa.blade.php -->
<div class="pT15 pB30">
<form method="post" action="{{ url('user/two-factor-authentication') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
@method('DELETE')

	<p>
		Are you sure you want to make your account less secure?
	</p>
	<input type="submit" class="btn btn-danger"
		value="Yes, Disable Two-Factor Authentication">

</form>
</div>