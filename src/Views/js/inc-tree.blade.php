<?php /* 
// treeMajorSects[major-index] = [nodeID, 'Section Title', 'status']
// treeMinorSects[major-index][minor-index] = [nodeID, 'Section Title', 'status', 'url']
*/ ?>
unloadTree();
currTree = {{ $GLOBALS["SL"]->treeID }};
@forelse ($majorSections as $maj => $majSect)
treeMajorSects[{{ $maj }}] = new Array({{ $majSect[0] }}, "{{ $majSect[1] }}", "/", "disabled");
treeMinorSects[{{ $maj }}] = new Array();
    @if (sizeof($minorSections[$maj]) > 0 
        && isset($GLOBALS['SL']->treeRow->tree_slug))
        @forelse ($minorSections[$maj] as $min => $minSect)
            @if (isset($allNodes[$minSect[0]]))
treeMinorSects[{{ $maj }}][{{ $min }}] = new Array({{ $minSect[0] }}, "{{ $minSect[1] }}", "/", "disabled");
                @if ($GLOBALS['SL']->treeIsAdmin)
treeMinorSects[{{ $maj }}][{{ $min }}][2] = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ 
                    $GLOBALS['SL']->treeRow->tree_slug }}/{{ 
                    $allNodes[$minSect[0]]->nodeRow->node_prompt_notes }}";
                @else
treeMinorSects[{{ $maj }}][{{ $min }}][2] = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/u/{{ 
                    $GLOBALS['SL']->treeRow->tree_slug }}/{{ 
                    $allNodes[$minSect[0]]->nodeRow->node_prompt_notes }}";
                @endif
            @endif
        @empty
        @endforelse
    @endif
@empty
@endforelse

@forelse ($GLOBALS["SL"]->proTips as $i => $tip)
treeProTips[{{ $i }}] = '{{ $tip }}';
treeProTipsImg[{{ $i }}] = @if (isset($GLOBALS["SL"]->proTipsImg[$i])) '{{ 
    $GLOBALS["SL"]->proTipsImg[$i] }}'; @else ''; @endif
@empty
@endforelse
