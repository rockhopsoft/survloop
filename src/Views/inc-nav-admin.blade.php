<!-- resources/views/vendor/survloop/inc-nav-admin.blade.php -->

@if (isset($admTopMenu))

    {!! $admTopMenu !!}
    <ul class="nav navbar-nav navbar-right">
    <li><a href="/dashboard">Dashboard</a></li>
    <li><a href="/auth/logout">Logout</a></li>
    </ul>
    
@else
    
    <ul class="nav navbar-nav navbar-right">
    <li><a href="/dashboard">Dashboard</a></li>
    @if (isset($user)) <li><a href="/dashboard/user/{{ $user->id }}">Profile</a></li> @endif
    <li><a href="/auth/logout">Logout</a></li>
    </ul>
    <form class="navbar-form navbar-right">
    <input type="text" class="form-control" placeholder="Search..." style="height: 33px;">
    </form>
        
@endif