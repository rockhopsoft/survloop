<!-- resources/views/vendor/survloop/forms/inc-progress-top-tabs.blade.php -->

@if (sizeof($majorSections) > 0)
    <div id="navMobileTopTabs">
        <table id="navMobileTopTabsUl" border="0"
            cellspacing="0" cellpadding="0"><tr>
        @foreach ($majorSections as $maj => $majSect)
            <td>
                <a  @if (isset($allNodes[$majSect[0]])
                        && $allNodes[$majSect[0]]->nodeRow
                        && isset($allNodes[$majSect[0]]->nodeRow->node_prompt_notes))
                        @if ($GLOBALS['SL']->treeIsAdmin)
                            href="/dash/{{ $GLOBALS['SL']->treeRow->tree_slug }}/{{
                            $allNodes[$majSect[0]]->nodeRow->node_prompt_notes }}"
                        @else
                            href="/u/{{ $GLOBALS['SL']->treeRow->tree_slug }}/{{
                            $allNodes[$majSect[0]]->nodeRow->node_prompt_notes }}"
                        @endif
                    @elseif (isset($majSect[3])
                        && trim($majSect[3]) != '')
                        @if ($GLOBALS['SL']->treeIsAdmin)
                            href="/dash/{{ $GLOBALS['SL']->treeRow->tree_slug
                            }}/{{ $majSect[3] }}"
                        @else
                            href="/u/{{ $GLOBALS['SL']->treeRow->tree_slug
                            }}/{{ $majSect[3] }}"
                        @endif
                    @endif
                    @if ($maj == $currMajorSection)
                        class="navTopTab active"
                    @elseif ($majSect[2] == 'disabled')
                        class="navTopTab disabled"
                        tabindex="-1" aria-disabled="true"
                    @else
                        class="navTopTab"
                    @endif ><div class="disBlo pT10">
                        {{ $majSect[1] }}
                    </div></a>
            </td>
        @endforeach
        </tr></table>
    </div> <!-- end navMobileTopTabs -->

    <style>
    #navMobileTopTabs {
        width: 100%;
        height: 39px;
        min-height: 39px;
        padding-left: 30px;
        padding-right: 30px;
    }
    #navMobileTopTabsUl {
        width: 100%;
        margin-top: -12px;
        margin-left: -1px;
    }
    #navMobileTopTabs tr td {
        padding-left: 2px;
        padding-right: 1px;
        width: {{ (100/sizeof($majorSections)) }}%;
    }
    #navMobileTopTabs tr td .navTopTab,
    #navMobileTopTabs tr td .navTopTab.active {
        display: block;
        width: 100%;
        min-height: 49px;
        overflow: visible;
        text-align: center;
        -moz-border-radius: 10px; border-radius: 10px;
        padding: .5rem 1rem;
        border: 1px #8DC63F solid;
        background: #F9FFF0;
        color: #373835;
    }
    #navMobileTopTabs tr td .navTopTab.active {
        border: 1px #726659 solid;
        background: #8DC63F;
        color: #FFF;
    }

    </style>

@endif
