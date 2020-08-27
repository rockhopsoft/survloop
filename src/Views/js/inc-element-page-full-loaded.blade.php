/* generated from resources/views/vendor/survloop/js/inc-element-page-full-loaded.blade.php */

setTimeout(function() {
    if (!("tree{{ $treeID }}" in pageFullLoaded)) {
        pageFullLoaded["tree{{ $treeID }}"] = 0;
    }
    pageFullLoaded["tree{{ $treeID }}"] = 1+pageFullLoaded["tree{{ $treeID }}"]; 
}, {{ $delay }});
