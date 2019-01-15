<!-- resources/views/inc-java-load-menu.blade.php -->
<script defer type="text/javascript">
function loadTopSideNavs() {
@if (isset($username) && trim($username) != '')
    addTopNavItem('{{ $username }}',      '/my-profile" id="loginLnk'); 
    addSideNavItem('Logout',              '/logout');
    addSideNavItem('My Profile',          '/my-profile');
    @if (Auth::user()->hasRole('administrator'))
        addTopNavItem('Dashboard',        '/dashboard');
        addSideNavItem('Admin Dashboard', '/dashboard');
    @endif
@else
    addTopNavItem('Sign Up',  '/register" id="loginLnk');
    addTopNavItem('Login',    '/login');
    addSideNavItem('Login',   '/login');
    addSideNavItem('Sign Up', '/register');
@endif
    return true;
}
function tryLoadingTopSideNav() {
    if (typeof addTopNavItem === "function") {
        loadTopSideNavs();
    } else {
        setTimeout("tryLoadingTopSideNav()", 300);
    }
    return true;
}
setTimeout("tryLoadingTopSideNav()", 100);
</script>