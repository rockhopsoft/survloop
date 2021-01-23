<!-- resources/views/js/inc-load-menu.blade.php -->
<script defer type="text/javascript">

function loadTopSideNavs() {
@if (isset($username) && trim($username) != '')
    if (!document.getElementById('userMenuBtn')) {
        addTopUserBurger('{{ $username }}');
        <?php /*
        addTopNavItem('<i class="fa fa-search mT3" aria-hidden="true"></i>', 'javascript:;" id="topNavSearchBtn');
        */ ?>
    }
@else
    if (!document.getElementById('loginLnk')) {
        addTopNavItem('Sign Up',  '/register{!! $previousUrl !!}" id="signupLnk');
        addTopNavItem('Login',    '/login{!! $previousUrl !!}" id="loginLnk');
        <?php /*
        addTopNavItem('<i class="fa fa-search mT3" aria-hidden="true"></i>', 'javascript:;" id="topNavSearchBtn');
        */ ?>
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


@if ($userLoadTweaks !== null
    && isset($userLoadTweaks->tweaks)
    && sizeof($userLoadTweaks->tweaks) > 0)
    function tweakRun() {
    @foreach ($userLoadTweaks->tweaks as $tweak)
        @if (isset($tweak->link) && trim($tweak->link) != '')
            tweakNavLink({{ $tweak->posA }}, {{ $tweak->posB }}, {{ $tweak->posC }}, "{{ $tweak->link }}");
        @endif
    @endforeach
    }
    setTimeout("tweakRun()", 1);
@endif

</script>