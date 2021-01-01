<!-- resources/views/vendor/survloop/elements/inc-load-anim-btn.blade.php -->

<div id="loadAnim{{ $animID }}" class="disBlo">
    <input id="loadAnimBtn{{ $animID }}" data-anim-id="{{ $animID }}"
        type="submit" class="loadAnimBtn btn {{ $class }}"
        value="{{ $title }}">
</div>
<div id="loadAnimClicked{{ $animID }}" class="disNon">
    <button class="btn {{ $class }}" type="button" disabled >
        <table border=0 cellpadding=0 cellspacing=0 ><tr>
            <td><span class="spinner-border spinner-border-sm" 
                role="status" aria-hidden="true"></span></td>
            <td class="pL5"><div class="disIn pT5">Loading...</div></td>
        </tr></table>
    </button>
</div>
