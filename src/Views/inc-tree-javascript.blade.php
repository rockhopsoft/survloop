/* resources/views/vendor/survloop/inc-tree-javascript.blade.php */
<?php /* 
// treeMajorSects[major-index] = [nodeID, 'Section Title', 'status']
// treeMinorSects[major-index][minor-index] = [nodeID, 'Section Title', 'status', 'url']
*/ ?>
currTree = {{ $GLOBALS["SL"]->treeID }};
treeMajorSects = new Array();
treeMinorSects = new Array();
treeMajorSectsDisabled = new Array();
@forelse ($majorSections as $maj => $majSect)
treeMajorSects[{{ $maj }}] = new Array({{ $majSect[0] }}, "{{ $majSect[1] }}", "/", "disabled");
treeMinorSects[{{ $maj }}] = new Array();
    @if (sizeof($minorSections[$maj]) > 0 && isset($GLOBALS['SL']->treeRow->TreeSlug))
        @forelse ($minorSections[$maj] as $min => $minSect)
            @if (isset($allNodes[$minSect[0]]))
treeMinorSects[{{ $maj }}][{{ $min }}] = new Array({{ $minSect[0] }}, "{{ $minSect[1] }}", "/", "disabled");
                @if ($GLOBALS['SL']->treeIsAdmin)
treeMinorSects[{{ $maj }}][{{ $min }}][2] = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ 
                    $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}";
                @else
treeMinorSects[{{ $maj }}][{{ $min }}][2] = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/u/{{ 
                    $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}";
                @endif
            @endif
        @empty
        @endforelse
    @endif
@empty
@endforelse

treeProTips = new Array();
@forelse ($GLOBALS["SL"]->proTips as $i => $tip)
treeProTips[treeProTips.length] = '{{ str_replace("'", "&#39;", $tip) }}';
@empty
@endforelse
