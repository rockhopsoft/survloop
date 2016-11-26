<!-- Stored in resources/views/survloop/inc-nav-public.blade.php -->

<ul class="nav navbar-nav navbar-right">
@if (isset($user) && isset($user->id) && $user->id > 0)
	<li><a href="/logout">Logout</a></li>
@else
	<li><a href="/login" title="Login to pick up where you left off.">Login</a></li>
@endif
</ul>