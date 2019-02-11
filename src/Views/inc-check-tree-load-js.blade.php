/* resources/views/vendor/survloop/inc-check-tree-load-js.blade.php */
function tryTreeLoad{{ $treeID }}() {
    if (typeof treeLoad{{ $treeID }} === "function") treeLoad{{ $treeID }}();
    else setTimeout("tryTreeLoad{{ $treeID }}()", 500);
    return true;
}
setTimeout("tryTreeLoad{{ $treeID }}()", 100);