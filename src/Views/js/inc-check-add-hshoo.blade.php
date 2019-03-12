/* resources/views/vendor/survloop/js/inc-check-add-hshoo.blade.php */
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