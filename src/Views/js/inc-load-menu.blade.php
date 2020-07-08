<!-- resources/views/js/inc-load-menu.blade.php -->
<script defer type="text/javascript">
function loadTopSideNavs() {
@if (isset($username) && trim($username) != '')
    if (!document.getElementById('userMenuBtn')) {
        addTopUserBurger('{{ $username }}');
    }
@else
    if (!document.getElementById('loginLnk')) {
        addTopNavItem('Sign Up',  '/register{!! $previousUrl !!}" id="signupLnk');
        addTopNavItem('Login',    '/login{!! $previousUrl !!}" id="loginLnk');
    }
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