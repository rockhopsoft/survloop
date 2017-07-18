<!-- resources/views/vendor/survloop/inc-progress-bar.blade.php -->

@if (sizeof($majorSections) > 0)

    <div id="navDesktop">
    
        <div class="row">
        @if ($majTot == 5) <div class="col-md-1"></div> @endif
        <?php $cnt = 0; ?>
        @foreach ($majorSections as $maj => $majSect)
            @if ($majSect[2] != 'disabled')
                <?php $cnt++; ?>
                <div class="col-md-{{ floor(12/$majTot) }}">
                    <a data-toggle="tab" href="javascript:void(0)" id="maj{{ $maj }}" class="navDeskMaj
                        @if ($maj == $currMajorSection) active 
                        @elseif (in_array($maj, $sessMajorsTouched)) completed 
                        @endif " >
                        <center><div class="stepNum">
                        @if ($maj == $currMajorSection) <i class="fa fa-hand-o-down" aria-hidden="true"></i>
                        @elseif (in_array($maj, $sessMajorsTouched)) <i class="fa fa-check"></i>
                        @else {{ $cnt }}
                        @endif
                        </div><div class="navVertLine"></div>{{ $majSect[1] }}
                        <div id="majSect{{ $maj }}Vert2" class="navVertLine2 disNon"></div><center>
                    </a>
                </div>
            @endif
        @endforeach
        @if ($majTot == 5) <div class="col-md-1"></div> @endif
        </div>
        @foreach ($majorSections as $maj => $majSect)
            @if (sizeof($minorSections[$maj]) > 0)
                <div id="minorNav{{ $maj }}" class="minorNavWrap">
                    <div class="row">
                        @if (sizeof($minorSections[$maj]) == 5) <div class="col-md-1"></div> @endif
                        @forelse ($minorSections[$maj] as $min => $minSect)
                            <div class="col-md-{{ floor(12/sizeof($minorSections[$maj])) }}">
                                <a data-toggle="tab" href="javascript:void(0)" id="maj{{ $maj }}" class="navDeskMaj
                                    @if ($maj == $currMajorSection && $min == $currMinorSection) active 
                                    @elseif (in_array($min, $sessMinorsTouched[$maj])) completed 
                                    @endif "
                                    @if ((!isset($sessMinorsTouched[$maj]) || !in_array($min, $sessMinorsTouched[$maj])) 
                                        && ($maj != $currMajorSection || $min != $currMinorSection))
                                        href="javascript:void(0)"
                                    @else
                                        @if ($GLOBALS['SL']->treeIsAdmin)
                                            href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @else
                                            href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ 
                                                $allNodes[$minSect[0]]->nodeRow->NodePromptNotes }}" 
                                        @endif
                                    @endif
                                    >
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
                        @if (sizeof($minorSections[$maj]) == 5) <div class="col-md-1"></div> @endif
                    </div>
                </div>
            @endif
        @endforeach
        
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
            @foreach ($majorSections as $maj => $majSect)
                <div class="brdBotBluL pT5 pB5">
                    <div class="f16">
                        <b>{{ (1+$maj) }}. {{ $majorSections[$maj][1] }}</b>
                    </div>
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
                            <a href="javascript:void(0)" class="navMobOff" >
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