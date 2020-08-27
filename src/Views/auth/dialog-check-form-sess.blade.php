<!-- resources/views/auth/dialog-check-form-sess.blade.php -->
<div class="p10"></div>
<center>
@if ($req->has('login'))
    <h3>
        This page has timed out.<br />
        Please reload this page, and login if neccessary.
    </h3>
    <div id="reloadSpinner"></div>
    <script type="text/javascript">
        document.getElementById("reloadSpinner").innerHTML=getSpinner(); 
        setTimeout("location.reload()", 3000);
    </script>
@else
    <h3>
        The form on this page has timed out.<br />
        Please reload this page, and login if neccessary.
    </h3>
    <p id="reloadDesc"></p>
    <div class="p10"></div>
    <a id="dialogCloseID" class="dialogClose btn btn-lg btn-primary" 
        onClick="setTimeout('location.reload()', 1);"
        href="javascript:;">Reload</a>
    <script type="text/javascript"> 
        function checkTreeTypeWarning() {
            if (treeType == 'Survey') {
                document.getElementById('reloadDesc').innerHTML="Don't worry, we were auto-saving all your responses once per minute.";
            }
            return true;
        }
        setTimeout("checkTreeTypeWarning()", 1);
    </script>
@endif
</center>
<div class="p10"></div>