<!-- resources/views/vendor/survloop/admin/db/inc-indicate-saved.blade.php -->
@if ($iframe)
    <link href="/survloop/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
@endif
<div id="savedSpin{{ $spot }}" style="display: block;">
    <i class="fa-li fa fa-spinner fa-spin"></i>
</div>
<div id="savedConfirm{{ $spot }}" style="display: none;">
    Saved <i class="fa fa-check" aria-hidden="true"></i>
</div>
<script type="text/javascript">
    function showConfirmation{{ $spot }}() {
        document.getElementById("savedSpin{{ $spot }}").style.display = "none";
        document.getElementById("savedConfirm{{ $spot }}").style.display = "block";
    }
    setTimeout("showConfirmation{{ $spot }}()", 1000);
</script>