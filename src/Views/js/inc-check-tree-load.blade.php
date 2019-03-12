/* resources/views/vendor/survloop/js/inc-check-tree-load.blade.php */
function tryTreeLoad{{ $treeID }}() {
    if (typeof treeLoad{{ $treeID }} === "function") treeLoad{{ $treeID }}();
    else setTimeout("tryTreeLoad{{ $treeID }}()", 500);
    return true;
}
setTimeout("tryTreeLoad{{ $treeID }}()", 100);