/* resources/views/vendor/survloop/inc-check-add-hshoo-js.blade.php */
function addPageHshoo() {
    @forelse ($hshoos as $h) addHshoo("{{ $h }}"); @empty @endforelse
    chkHshooTopTabs();
    return true;
}
function tryPageHshoo() {
    if (typeof addHshoo === "function") addPageHshoo();
    else setTimeout("tryPageHshoo()", 500);
    return true;
}
setTimeout("tryPageHshoo()", 100);