<!-- resources/views/vendor/survloop/admin/db/inc-addCondition.blade.php -->

<input type="hidden" name="condID" 
    @if (isset($cond) && isset($cond->cond_id)) value="{{ $cond->cond_id }}" @else value="-3" @endif >

@if (isset($cond) && isset($cond->cond_tag))
    <h4 class="slBlueDark mT0 mB20"><i class="fa fa-pencil" aria-hidden="true"></i> 
        Edit Condition: {{ $cond->cond_tag }}</h4>
@else
    <?php $cond = []; ?>
    <h4 class="slBlueDark mT0">Add New Condition:</h4>
    @if (!isset($newOnly) || !$newOnly)
        <select id="oldCondsID" name="oldConds" 
            class="form-control form-control-lg mT5" autocomplete="off" >
            <option value="0">Select a condition</option>
            <option value="-37">Create a new condition</option>
            <option value="0" disabled ></option>
            @forelse ($GLOBALS['SL']->getCondList() as $c)
                <option value="{{ $c->cond_id }}">{{ $c->cond_tag }} - {{ $c->cond_desc }}</option>
            @empty
            @endforelse
        </select>
        <label class="mT5">
            <input type="checkbox" name="oldCondInverse" value="1" autocomplete=off >
            ...Not (opposite of this condition)
        </label>
    @endif
@endif

@if (!isset($cond->cond_tag))
<div id="createNewCond" class=" @if (!isset($newOnly) || !$newOnly) disNon @else disBlo @endif ">
@endif
    <div id="nameIt" class="disBlo">
        <div class="row mT10">
            <div class="col-3 nPrompt">
                <h4 class="mT10">Hashtag:</h4>
            </div>
            <div class="col-9">
                <input type="text" id="condHashID" name="condHash" 
                    class="form-control form-control-lg" autocomplete=off
                    @if (isset($cond) && isset($cond->cond_tag)) value="{{ $cond->cond_tag }}" 
                    @else value="#" 
                    @endif >
            </div>
        </div>
        <div class="row mT10">
            <div class="col-3 nPrompt">
                <h4 class="mT10">Description:</h4>
            </div>
            <div class="col-9">
                <input type="text" id="condDescID" name="condDesc" 
                    class="form-control form-control-lg" autocomplete=off 
                    @if (isset($cond) && isset($cond->cond_desc)) value="{{ $cond->cond_desc }}" 
                    @else value="" 
                    @endif >
            </div>
        </div>
    </div>
    <div class="row mT10">
        <div class="col-3 nPrompt">
            <h4 class="mT10">Condition Type:</h4>
        </div>
        <div class="col-9">
            <select id="condTypeID" name="condType" class="form-control form-control-lg" autocomplete=off >
                <option @if (!isset($cond) || !isset($cond->cond_operator) || $cond->cond_operator != 'COMPLEX') 
                    SELECTED @endif value="simple" >Simple Condition</option>
                <option @if (isset($cond) && isset($cond->cond_operator) && $cond->cond_operator == 'COMPLEX') 
                    SELECTED @endif value="complex" >Multiple Conditions Combined</option>
            </select>
        </div>
    </div>
    <div id="createNewCondSimple" class=" 
        @if (isset($cond) && isset($cond->cond_operator) && $cond->cond_operator == 'COMPLEX') disNon 
        @else disBlo @endif ">
        <div class="row mT10">
            <div class="col-3 nPrompt">
                <h4 class="mT10">Data Set:</h4>
            </div>
            <div class="col-9">
                <select id="setSelectID" name="setSelect" class="form-control form-control-lg" autocomplete=off >
                    <option value="" @if (!isset($cond)) SELECTED @endif ></option>
                    <option value="" DISABLED >Survloops:</option>
                    @forelse ($GLOBALS['SL']->dataLoops as $loopName => $loopRow)
                        <option @if (isset($cond) && isset($cond->cond_operator) 
                            && $cond->CondLoop == $loopRow->data_loop_id) SELECTED @endif 
                            value="loop-{{ $loopRow->data_loop_id }}"> - {{ $loopName }}</option>
                    @empty
                    @endforelse
                    <option value="" DISABLED ></option>
                    {!! $GLOBALS['SL']->tablesDropdown(
                        ((isset($cond) && isset($cond->cond_table) && intVal($cond->cond_table) > 0) 
                            ? $cond->cond_table : '12345'), 'Database Tables:', ' - ', true) !!}
                    <option value="" DISABLED ></option>
                    <option value="url-parameters" @if (isset($cond) && isset($cond->cond_operator) 
                        && $cond->cond_operator == 'URL-PARAM') SELECTED @endif >
                        Custom URL Parameters (eg. /url/?parameter=value)</option>
                    <option value="" DISABLED ></option>
                </select>
            </div>
        </div>
        <div id="fldSelect">
            @if (isset($cond) && isset($cond->cond_table) && intVal($cond->cond_table) > 0)
                {!! view(
                    'vendor.survloop.admin.db.inc-getTblsFldsDropOpts', 
                    [
                        "cond"       => $cond,
                        "setOptions" => $GLOBALS["SL"]->getAllSetTblFldDrops($cond->cond_table, 
                            ((isset($cond->cond_field)) ? $cond->cond_field : ''))
                    ]
                )->render() !!}
            @endif
        </div>
        <div id="valSelect">
            @if (isset($cond) && isset($cond->cond_field) && intVal($cond->cond_field) > 0)
                {!! view(
                    'vendor.survloop.admin.db.inc-getTblsFldVals', 
                    [ 
                        "cond"   => $cond,
                        "values" => $GLOBALS["SL"]->getFldResponsesByID($cond->cond_field)
                    ]
                ) !!}
            @endif
        </div>
        <div id="urlParams" class=" @if (isset($cond) && isset($cond->cond_operator) 
            && $cond->cond_operator == 'URL-PARAM') disBlo @else disNon @endif " >
            <div class="row mT10">
                <div class="col-3"></div>
                <div class="col-9">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="m0">Parameter:</h4>
                            <input type="text" id="paramNameID" name="paramName" 
                                class="form-control form-control-lg" autocomplete=off 
                                @if (isset($cond) && isset($cond->cond_oper_deet)) 
                                    value="{{ $cond->cond_oper_deet }}"
                                @endif >
                        </div>
                        <div class="col-6">
                            <h4 class="m0">Value:</h4>
                            <input type="text" id="paramValID" name="paramVal" 
                                class="form-control form-control-lg" autocomplete=off 
                                @if (isset($cond) && isset($cond->condVals) && sizeof($cond->condVals) > 0) 
                                    value="{{ $cond->condVals[0] }}" 
                                @endif >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php $condList = $GLOBALS["SL"]->getCondList(); ?>
    <div id="createNewCondComplex" class="mT20 mB20 
        @if (isset($cond) && isset($cond->cond_operator) && $cond->cond_operator == 'COMPLEX') disBlo 
        @else disNon @endif ">
        <div class="row"><div class="col-4">
            @forelse ($condList as $i => $c)
                @if ($i == ceil(sizeof($condList)/3) || $i == ceil(sizeof($condList)*2/3))
                    </div><div class="col-4">
                @endif
                <?php
                $hasMultCond = [false, false];
                if (isset($cond) && isset($cond->cond_operator) && $cond->cond_operator == 'COMPLEX' 
                    && isset($cond->condVals) && sizeof($cond->condVals) > 0) {
                    foreach ($cond->condVals as $j => $val) {
                        if ($val == $c->cond_id) {
                            $hasMultCond[0] = true;
                        } elseif (($val*(-1)) == $c->cond_id) {
                            $hasMultCond[1] = true;
                        }
                    }
                }
                ?>
                <div class="pT5 pB5">
                    <label class="disIn"><nobr>
                        <input type="checkbox" id="multConds{{ $c->cond_id }}" name="multConds[]" autocomplete="off"
                            value="{{ $c->cond_id }}" class="multConds mR10" 
                            @if ($hasMultCond[0] || $hasMultCond[1]) CHECKED @endif
                            > {{ $c->cond_tag }}
                    </nobr></label>
                    <div id="multConds{{ $c->cond_id }}not" class="mL20 
                        @if ($hasMultCond[0] || $hasMultCond[1]) disIn @else disNon @endif ">
                        <label class="disIn"><nobr>
                            <input type="checkbox" id="multConds{{ $c->cond_id }}n" name="multCondsNot[]" 
                            autocomplete="off" value="{{ $c->cond_id }}" class="mR10"
                            @if ($hasMultCond[1]) CHECKED @endif
                            > ...Not <span class="fPerc66 slGrey">(opposite)</span>
                        </nobr></label>
                    </div>
                    <div id="multConds{{ $c->cond_id }}desc" class="w100 slGrey 
                        @if ($hasMultCond[0] || $hasMultCond[1]) disBlo @else disNon @endif "><i>
                        {{ $c->cond_desc }}</i></div>
                </div>
            @empty
            @endforelse
        </div></div>
    </div>
    
    <div class="p5"></div>
    <label class="disBlo pB5 fPerc125">
        <input type="checkbox" name="CondPublicFilter" id="CondPublicFilterID" value="1" autocomplete="off"
            @if (isset($cond) && isset($cond->cond_opts) && $cond->cond_opts%2 == 0) CHECKED @endif 
            > Public Use: Condition is a default option when filtering search results
    </label>
    <a id="addArt" class="addArtBtn fPerc125" href="javascript:;">+ Add related article</a>
    <div id="condArticle" class=" @if (isset($cond) && isset($cond->cond_opts) && intVal($cond->cond_opts) > 0 
        && $cond->cond_opts%3 == 0) disBlo @else disNon @endif ">
        <div class="row mT5 fPerc80">
            <div class="col-6"><i>Article Title</i></div>
            <div class="col-6"><i>Article URL</i></div>
        </div>
        @for ($j=0; $j < 10; $j++)
            <div id="arti{{ $j }}" class="row @if (isset($condArticles) && isset($condArticles[$cond->cond_id]) 
                && isset($condArticles[$cond->cond_id][$j]) && isset($condArticles[$cond->cond_id][$j][1])) 
                disBlo @else disNon @endif ">
                <div class="col-6">
                    <input name="condArtTitle{{ $j }}" id="condArtTitle{{ $j }}ID" 
                        type="text" class="form-control mB5" autocomplete=off 
                        @if (isset($condArticles) 
                            && isset($condArticles[$cond->cond_id]) 
                            && isset($condArticles[$cond->cond_id]) && isset($condArticles[$cond->cond_id][$j])
                            && isset($condArticles[$cond->cond_id][$j][0]))
                            value="{{ $condArticles[$cond->cond_id][$j][0] }}" 
                        @endif >
                </div>
                <div class="col-6">
                    <input name="condArtUrl{{ $j }}" id="condArtUrl{{ $j }}ID" 
                        type="text" class="form-control mB5" autocomplete=off 
                        @if (isset($condArticles) 
                            && isset($condArticles[$cond->cond_id]) 
                            && isset($condArticles[$cond->cond_id][$j])
                            && isset($condArticles[$cond->cond_id][$j][1]))
                            value="{{ $condArticles[$cond->cond_id][$j][1] }}" 
                        @endif >
                </div>
            </div>
        @endfor
    </div>
    
    <div class="pT20 taC">
        <input type="submit" value="Add Condition" class="btn btn-lg btn-primary" >
    </div>

@if (!isset($cond->cond_tag)) </div> @endif
