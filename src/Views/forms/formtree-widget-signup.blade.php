<!-- resources/views/survloop/forms/formtree-widget-signup.blade.php -->

{!! $GLOBALS["SL"]->spinner() !!}
<script type="text/javascript">
setTimeout("window.location='/register?nd={{ $curr->nID }}'", 100);
</script>
<style>
    /* #navDesktop, */
#pageBtns, #navMobile, #sessMgmtWrap {
    display: none;
}
</style>
