<!-- resources/views/errors/csrftoken.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<center><h2>
<br /><br />
<h2>Sorry, your session ended.</h2>
<p>But we did our best to try to save your changes before the session timed out. 
You will probably need to <a href="/login">log back in</a> before picking up where you left off.</p> 
<br /><br />
<a href="javascript:history.back()">Go Back</a>
<br /><br /><br /><br />
</h2>

@endsection
