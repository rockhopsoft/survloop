/* generated from resources/views/vendor/survloop/js/inc-element-tree-page-fade-in.blade.php */

pageFadeInDelay = {{ $delay }}; 
pageFadeInSpeed = {{ $speed }}; 
setTimeout(function() { 
    setTimeout(function() {
        $("#pageAnimWrap{{ $treeID }}").fadeIn(pageFadeInSpeed); 
    }, pageFadeInDelay);
}, 150);
