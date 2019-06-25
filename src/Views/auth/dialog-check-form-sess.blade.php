<!-- resources/views/auth/dialog-check-form-sess.blade.php -->
<div class="p10"></div>
<center>
@if ($req->has('login'))
    <h3>To protect your privacy, we need to reload this page...</h3>
    <div id="reloadSpinner"></div>
    <script type="text/javascript">
        document.getElementById("reloadSpinner").innerHTML=getSpinner(); 
        setTimeout("location.reload()", 3000);
    </script>
@else
    <h3>To protect your privacy, we need to save your changes and reload this page.</h3>
    <p>It will be reloaded automatically in <span id="reloadCntDwn">60</span> seconds...</p>
    <div class="p10"></div>
    <div class="row">
        <div class="col-6 taC">
            <a class="dialogClose btn btn-lg btn-secondary" href="javascript:;">Cancel Reload</a>
        </div>
        <div class="col-6 taC">
            <a class="nFormSaveReload btn btn-lg btn-primary" href="javascript:;">Save & Reload</a>
        </div>
    </div>
    <script type="text/javascript"> startCountdown('reloadCntDwn', 60, 1); </script>
@endif
</center>
<div class="p10"></div>