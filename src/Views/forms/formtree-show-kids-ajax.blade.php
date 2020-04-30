/* resources/views/survloop/forms/formtree-show-kids-ajax.blade.php */

if ({!! $if !!}) {
    showKids = true;
@forelse ($grankids as $grandNode)
    styBlock("node{{ $grandNode . $curr->nSffx }}");
    styBlock("node{{ $grandNode . $curr->nSffx }}kids");
    setNodeVisib("{{ $grandNode }}", "{{ $curr->nSffx }}", true);
@empty
@endforelse
    styBlock("node{{ $nKid . $curr->nSffx }}");
    styBlock("node{{ $nKid . $curr->nSffx }}kids"); 
    setNodeVisib("{{ $nKid }}", "{{ $curr->nSffx }}", true);
    setSubResponses({{ $curr->nID }}, "{{ $curr->nSffx }}", true, new Array({{ implode(', ', $grankids) }}));
} else {
    setNodeVisib("{{ $nKid }}", "{{ $curr->nSffx }}", false);
    $("#node{{ $nKid . $curr->nSffx }}").slideUp("50");
@forelse ($grankids as $grandNode)
    $("#node{{ $grandNode . $curr->nSffx }}").slideUp("50"); 
    setNodeVisib("{{ $grandNode }}", "{{ $curr->nSffx }}", false);
    setSubResponses({{ $curr->nID }}, "{{ $curr->nSffx }}", false, new Array({{ implode(', ', $grankids) }}));
@empty
@endforelse
}
