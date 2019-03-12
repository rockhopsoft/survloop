/* resources/views/survloop/forms/upload-slide-to-previous-ajax.blade.php */
function slideToUpload(nIDtxt) {
    if (document.getElementById(nIDtxt)) {
        var newTop = (1+getAnchorOffset()+$("#upPrev{{ $nIDtxt }}").offset().top);
        $(\'html, body\').animate({ scrollTop: newTop }, 800, \'swing\', function(){ });
    }
    return true;
}
setTimeout(function() { slideToUpload({{ $nIDtxt }}); }, 1000);