<!-- resources/views/vendor/survloop/inc-progress-bar.blade.php -->

<div id="navDesktop">

    <div>
        <ul class="nav nav-pills nav-justified nav-wizard">
        @forelse ($majorSections as $maj => $majSect)
            <li class=" 
                @if ($maj == $currMajorSection) active @elseif (in_array($maj, $sessMajorsTouched)) completed @endif ">
                @if ($maj > 0) <div class="nav-wedge"></div> @endif
                <a data-toggle="tab" href="javascript:void(0)" id="maj{{ $maj }}" class="toggleSubNav navDeskMaj" >
                @if (!in_array($maj, $sessMajorsTouched) && $maj != $currMajorSection)
                    <div class="stepNum">{{ (1+$maj) }}</div> {{ $majSect[1] }}</a>
                @else 
                    @if (in_array($maj, $sessMajorsTouched) && $maj != $currMajorSection) 
                        <i class="fa fa-check"></i> @else <div class="stepNum">{{ (1+$maj) }}</div> 
                    @endif 
                    {{ $majSect[1] }}</a>
                @endif
                @if ($maj < (sizeof($majorSections)-1)) <div class="nav-arrow"></div> @endif
            </li>
        @empty
        @endforelse
        </ul>
    </div>
    @forelse ($majorSections as $maj => $majSect)
        @if (sizeof($minorSections[$maj]) > 0)
            <div id="minorNav{{ $maj }}" class="pT10 disNon">
                <div class="subNav">
                    <div class="row">
                        <div class="col-md-1 snLabel relDiv">
                            <div class=" @if ($maj == $currMajorSection) stepNum2active 
                                @elseif (in_array($maj, $sessMajorsTouched)) stepNum2complete @else stepNum2 @endif 
                                ">{{ (1+$maj) }}:</div>
                        </div>
                        <div class="col-md-11">
                            <ul class="nav nav-pills nav-justified nav-wizard">
                            @forelse ($minorSections[$maj] as $min => $minSect)
                                <li class=" @if ($maj == $currMajorSection && $min == $currMinorSection) active 
                                    @elseif (in_array($min, $sessMinorsTouched[$maj])) completed @endif ">
                                    @if ($min > 0) <div class="nav-wedge"></div> @endif
                                    @if ( (!isset($sessMinorsTouched[$maj]) 
                                        || !in_array($min, $sessMinorsTouched[$maj])) 
                                        && ($maj != $currMajorSection || $min != $currMinorSection) )
                                        <a href="javascript:void(0)" ><nobr>{{ $minSect[1] }}</nobr></a>
                                    @else 
                                        <a 
                                        @if ($GLOBALS['SL']->treeIsAdmin)
                                            href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @else
                                            href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @endif
                                        ><nobr>
                                        @if (in_array($min, $sessMinorsTouched[$maj]) && ($maj != $currMajorSection 
                                            || $min != $currMinorSection)) <i class="fa fa-check"></i> @endif 
                                        {{ $minSect[1] }}</nobr></a>
                                    @endif
                                    @if ($min < (sizeof($minorSections[$maj])-1)) <div class="nav-arrow"></div> @endif
                                </li>
                            @empty
                            @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @empty
    @endforelse
    
</div> <!-- end of desktop navigation -->

<div id="navMobile">
    
    <div class="f16">
        <a id="navMobBurger1" class="disIn mR10" href="javascript:void(0)"
            ><i class="fa fa-caret-right f16" aria-hidden="true"></i></a>
        <a id="navMobBurger2" class="disNon mR5" href="javascript:void(0)"
            ><i class="fa fa-caret-down f16" aria-hidden="true"></i></a>
        <b>{{ $majorSections[$currMajorSection][1] }}</b>
        @if (sizeof($minorSections[$currMajorSection]) > 0 
            && isset($minorSections[$currMajorSection][$currMinorSection]))
            : {{ $minorSections[$currMajorSection][$currMinorSection][1] }}
        @endif
    </div>
    <div class="brdBotBluL">
        <div class="brdBotBluL3" style="width: {{ $rawPerc }}%;"> </div>
    </div>
    
    <div id="navMobFull" class="disNon">
        @forelse ($majorSections as $maj => $majSect)
            <div class="brdBotBluL pT5 pB5">
                <div class="f16">
                    <b>{{ (1+$maj) }}. {{ $majorSections[$maj][1] }}</b>
                </div>
                <div class="pL20">
                @forelse ($minorSections[$maj] as $min => $minSect)
                    @if (in_array($min, $sessMinorsTouched[$maj]))
                        <a  @if ($GLOBALS['SL']->treeIsAdmin)
                                href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                            @else
                                href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                            @endif
                            class=" @if ($maj == $currMajorSection && $min == $currMinorSection) navMobActive 
                            @else navMobDone @endif " >
                        @if ($maj != $currMajorSection || $min != $currMinorSection) <i class="fa fa-check"></i> @endif 
                    @else
                        <a href="javascript:void(0)" class="navMobOff" >
                    @endif
                    {{ $minSect[1] }}</a>
                @empty
                @endforelse
                </div>
            </div>
        @empty
        @endforelse
    </div>
    
</div> <!-- end of mobile navigation -->
