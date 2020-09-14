<!-- resources/views/vendor/survloop/elements/inc-save-ico-anim.blade.php -->

<div class="relDiv" style="width: 30px;">
    <div class="absDiv" style="left: 0px; top: -10px;">
        <div id="saveIco{{ $rand }}" class="disNon">
            <span class="fa-stack fa-lg">
              <i class="fa fa-floppy-o fa-stack-1x slGreenDark"></i>
              <i class="fa fa-circle-o-notch fa-spin fa-stack-2x slGreenDark"></i>
            </span>
        </div>
    </div>
</div>

<script type="text/javascript"> $(document).ready(function(){

setTimeout(function() { $("#saveIco{{ $rand }}").fadeIn(300); }, 1);
setTimeout(function() { $("#saveIco{{ $rand }}").fadeOut(4000); }, 400);

}); </script>
