<!-- resources/views/vendor/survloop/js/redir.blade.php -->
@if (isset($redir) && trim($redir) != '')
    <script type="text/javascript">
    setTimeout("top.location.href='{!! $redir !!}'", 3);
    </script>
@endif