<!-- resources/views/vendor/survloop/forms/inc-progress-bar.blade.php -->

@if (sizeof($majorSections) > 0)

    <div id="navDesktop">
    
        <div class="row">
        @if ($majTot == 5) <div class="col-1"></div> @endif
        <?php $cnt = 0; ?>
        @foreach ($majorSections as $maj => $majSect)
            @if ($majSect[2] != 'disabled')
                <?php $cnt++; ?>
                <div class="col-{{ floor(12/$majTot) }}">
                    <a id="maj{{ $maj }}" class="navDeskMaj @if ($maj == $currMajorSection) active 
                        @elseif (in_array($maj, $sessMajorsTouched)) completed @endif " href="javascript:;"
                        @if (!isset($minorSections[$maj]) || sizeof($minorSections[$maj]) == 0)
                            data-jumpnode="{{ $majSect[0] }}" @endif >
                        <center><div class="stepNum">
                        @if ($maj == $currMajorSection) <i class="fa fa-hand-o-down" aria-hidden="true"></i>
                        @elseif (in_array($maj, $sessMajorsTouched)) <i class="fa fa-check"></i> @else {{ $cnt }} @endif
                        </div><div class="navVertLine"></div>{{ $majSect[1] }}
                        @if (sizeof($minorSections[$maj]) > 0)
                            <div id="majSect{{ $maj }}Vert2" class="navVertLine2
                                @if ($maj == $currMajorSection) disBlo @else disNon @endif ">
                            </div>
                        @endif
                        </center>
                    </a>
                </div>
            @endif
        @endforeach
        @if ($majTot == 5) <div class="col-1"></div> @endif
        </div>
        <?php $cnt = 0; ?>
        @foreach ($majorSections as $maj => $majSect)
            @if (sizeof($minorSections[$maj]) > 0)
                <?php $cnt++; ?>
                <div id="minorNav{{ $maj }}" class="minorNavWrap
                    @if ($maj == $currMajorSection) disBlo @endif ">
                    <div class="row">
                        @if (sizeof($minorSections[$maj]) == 5) <div class="col-1"></div> @endif
                        @forelse ($minorSections[$maj] as $min => $minSect)
                            <div class="col-{{ floor(12/sizeof($minorSections[$maj])) }}">
                                <a id="maj{{ $maj }}" class="navDeskMin
                                    @if ($maj == $currMajorSection && $min == $currMinorSection) active 
                                    @elseif (in_array($min, $sessMinorsTouched[$maj])) completed @endif "
                                    @if ((!isset($sessMinorsTouched[$maj]) || !in_array($min, $sessMinorsTouched[$maj])) 
                                        && ($maj != $currMajorSection || $min != $currMinorSection))
                                        href="javascript:;"
                                    @elseif (isset($allNodes[$minSect[0]]))
                                        @if ($GLOBALS['SL']->treeIsAdmin)
                                            href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @else
                                            href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @endif
                                    @endif >
                                    <center><div class="stepNum">
                                    @if ($maj == $currMajorSection && $min == $currMinorSection) 
                                        <i class="fa fa-hand-o-down" aria-hidden="true"></i>
                                    @elseif (in_array($min, $sessMinorsTouched[$maj]) && ($maj != $currMajorSection 
                                        || $min != $currMinorSection)) <i class="fa fa-check"></i>
                                    @else {{ $cnt }}.{{ (1+$min) }}
                                    @endif
                                    </div><div class="navVertLine"></div>{{ $minSect[1] }}<center>
                                </a>
                            </div>
                        @empty
                        @endforelse
                        @if (sizeof($minorSections[$maj]) == 5) <div class="col-1"></div> @endif
                    </div>
                </div>
            @endif
        @endforeach
        
    </div> <!-- end of desktop navigation -->
    <div id="navMobile">
        
        <a id="navMobToggle" class="btn btn-secondary btn-sm w100 taL ovrNo" href="javascript:;">
            <div id="navMobTogInr">
                <div id="navMobBurger1" class="disIn"><i class="fa fa-caret-right" aria-hidden="true"></i></div>
                <div id="navMobBurger2" class="disNon"><i class="fa fa-caret-down" aria-hidden="true"></i></div>
            @if (isset($majorSections[$currMajorSection]))
                {{ $majorSections[$currMajorSection][1] }}
                @if (sizeof($minorSections[$currMajorSection]) > 0 
                    && isset($minorSections[$currMajorSection][$currMinorSection]))
                    : {{ $minorSections[$currMajorSection][$currMinorSection][1] }}
                @endif
            @endif
            </div>
            <div id="navMobPercWrap"><div id="navMobPercProg" style="width: {{ $rawPerc }}%;"> </div></div>
        </a>
        
        <div id="navMobFull" class="disNon">
            @foreach ($majorSections as $maj => $majSect)
                <div class="brdBot pT5 pB5">
                    <b>{{ (1+$maj) }}. {{ $majorSections[$maj][1] }}</b>
                    <div class="pL20">
                    @forelse ($minorSections[$maj] as $min => $minSect)
                        @if (in_array($min, $sessMinorsTouched[$maj]))
                            <a @if (isset($allNodes[$minSect[0]]))
                                @if ($GLOBALS['SL']->treeIsAdmin)
                                    href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                    $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                @else
                                    href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                    $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                @endif
                            @endif class=" @if ($maj == $currMajorSection && $min == $currMinorSection) navMobActive 
                                @else navMobDone @endif " >
                            @if ($maj != $currMajorSection || $min != $currMinorSection) <i class="fa fa-check"></i> @endif 
                        @else
                            <a href="javascript:;" class="navMobOff" >
                        @endif
                        {{ $minSect[1] }}</a>
                    @empty
                    @endforelse
                    </div>
                </div>
            @endforeach
        </div>
        
    </div> <!-- end of mobile navigation -->

@endif