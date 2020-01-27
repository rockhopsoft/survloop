/* resources/views/survloop/forms/formtree-show-kids-ajax.blade.php */

if ({!! $if !!}) {
    showKids = true;
@forelse ($grankids as $grandNode)
    styBlock("node{{ $grandNode . $nSffx }}");
    styBlock("node{{ $grandNode . $nSffx }}kids");
    setNodeVisib("{{ $grandNode }}", "{{ $nSffx }}", true);
@empty
@endforelse
    styBlock("node{{ $nKid . $nSffx }}");
    styBlock("node{{ $nKid . $nSffx }}kids"); 
    setNodeVisib("{{ $nKid }}", "{{ $nSffx }}", true);
    setSubResponses({{ $nID }}, "{{ $nSffx }}", true, new Array({{ implode(', ', $grankids) }}));
} else {
    setNodeVisib("{{ $nKid }}", "{{ $nSffx }}", false);
    $("#node{{ $nKid . $nSffx }}").slideUp("50");
@forelse ($grankids as $grandNode)
    $("#node{{ $grandNode . $nSffx }}").slideUp("50"); 
    setNodeVisib("{{ $grandNode }}", "{{ $nSffx }}", false);
    setSubResponses({{ $nID }}, "{{ $nSffx }}", false, new Array({{ implode(', ', $grankids) }}));
@empty
@endforelse
}
