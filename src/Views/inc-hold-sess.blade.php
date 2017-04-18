<!-- Stored in resources/views/survloop/inc-hold-sess.blade.php -->


<script type="text/javascript">
    holdSess = 1;
    $(function() {
        // handling of back button for ajax calls
        $(window).on("popstate", function(e) {
            if (e.originalEvent.state !== null) {
                window.location=location.href;
            }
        });
    });
</script>
