<!-- resources/views/vendor/survloop/admin/db/inc-addCondition.blade.php -->

<input type="hidden" name="condID" 
    @if (isset($cond) && isset($cond->CondID)) value="{{ $cond->CondID }}" @else value="-3" @endif >

@if (isset($cond) && isset($cond->CondTag))
    <h3 class="slBlueDark mT0 mB20"><i class="fa fa-pencil" aria-hidden="true"></i> 
        Edit Condition: {{ $cond->CondTag }}</h3>
@else
    <?php $cond = []; ?>
    <h3 class="slBlueDark mT0"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add New Condition:</h3>
    @if (!isset($newOnly) || !$newOnly)
        <select id="oldCondsID" name="oldConds" class="form-control input-lg mT5" autocomplete="off" >
            <option value="0">Select a condition</option>
            <option value="-37">Create a new condition</option>
            <option value="0" disabled ></option>
            @forelse ($GLOBALS['SL']->getCondList() as $c)
                <option value="{{ $c->CondID }}">{{ $c->CondTag }} - {{ $c->CondDesc }}</option>
            @empty
            @endforelse
        </select>
        <label class="mT5">
            <input type="checkbox" name="oldCondInverse" value="1" autocomplete=off 
                > ...Not (opposite of this condition)
        </label>
    @endif
@endif

@if (!isset($cond->CondTag))
<div id="createNewCond" class=" @if (!isset($newOnly) || !$newOnly) disNon @else disBlo @endif ">
@endif
    <div id="nameIt" class="disBlo">
        <div class="row mT10">
            <div class="col-md-3 nPrompt">
                <h4 class="mT10">Hashtag:</h4>
            </div>
            <div class="col-md-9">
                <input type="text" id="condHashID" name="condHash" class="form-control input-lg" autocomplete=off
                    @if (isset($cond) && isset($cond->CondTag)) value="{{ $cond->CondTag }}" @else value="#" @endif >
            </div>
        </div>
        <div class="row mT10">
            <div class="col-md-3 nPrompt">
                <h4 class="mT10">Description:</h4>
            </div>
            <div class="col-md-9">
                <input type="text" id="condDescID" name="condDesc" class="form-control input-lg" autocomplete=off 
                    @if (isset($cond) && isset($cond->CondDesc)) value="{{ $cond->CondDesc }}" @else value="" @endif >
            </div>
        </div>
    </div>
    <div class="row mT10">
        <div class="col-md-3 nPrompt">
            <h4 class="mT10">Condition Type:</h4>
        </div>
        <div class="col-md-9">
            <select id="condTypeID" name="condType" class="form-control input-lg" autocomplete=off >
                <option @if (!isset($cond) || !isset($cond->CondOperator) || $cond->CondOperator != 'COMPLEX') 
                    SELECTED @endif value="simple" >Simple Condition</option>
                <option @if (isset($cond) && isset($cond->CondOperator) && $cond->CondOperator == 'COMPLEX') 
                    SELECTED @endif value="complex" >Multiple Conditions Combined</option>
            </select>
        </div>
    </div>
    <div id="createNewCondSimple" class=" 
        @if (isset($cond) && isset($cond->CondOperator) && $cond->CondOperator == 'COMPLEX') disNon 
        @else disBlo @endif ">
        <div class="row mT10">
            <div class="col-md-3 nPrompt">
                <h4 class="mT10">Data Set:</h4>
            </div>
            <div class="col-md-9">
                <select id="setSelectID" name="setSelect" class="form-control input-lg" autocomplete=off >
                    <option value="" @if (!isset($cond)) SELECTED @endif ></option>
                    <option value="" DISABLED >SurvLoops:</option>
                    @forelse ($GLOBALS['SL']->dataLoops as $loopName => $loopRow)
                        <option @if (isset($cond) && isset($cond->CondOperator) 
                            && $cond->CondLoop == $loopRow->DataLoopID) SELECTED @endif 
                            value="loop-{{ $loopRow->DataLoopID }}"> - {{ $loopName }}</option>
                    @empty
                    @endforelse
                    <option value="" DISABLED ></option>
                    {!! $GLOBALS['SL']->tablesDropdown(
                        ((isset($cond) && isset($cond->CondTable) && intVal($cond->CondTable) > 0) 
                            ? $cond->CondTable : '12345'), 'Database Tables:', ' - ', true) !!}
                    <option value="" DISABLED ></option>
                    <option value="url-parameters" @if (isset($cond) && isset($cond->CondOperator) 
                        && $cond->CondOperator == 'URL-PARAM') SELECTED @endif >
                        Custom URL Parameters (eg. /url/?parameter=value)</option>
                    <option value="" DISABLED ></option>
                </select>
            </div>
        </div>
        <div id="fldSelect">
            @if (isset($cond) && isset($cond->CondTable) && intVal($cond->CondTable) > 0)
                {!! view('vendor.survloop.admin.db.inc-getTblsFldsDropOpts', [
                    "cond"       => $cond,
                    "setOptions" => $GLOBALS["SL"]->getAllSetTblFldDrops($cond->CondTable, 
                        ((isset($cond->CondField)) ? $cond->CondField : ''))
                ])->render() !!}
            @endif
        </div>
        <div id="valSelect">
            @if (isset($cond) && isset($cond->CondField) && intVal($cond->CondField) > 0)
                {!! view('vendor.survloop.admin.db.inc-getTblsFldVals', [ 
                    "cond"   => $cond,
                    "values" => $GLOBALS["SL"]->getFldResponsesByID($cond->CondField)
                ]) !!}
            @endif
        </div>
        <div id="urlParams" class=" @if (isset($cond) && isset($cond->CondOperator) 
            && $cond->CondOperator == 'URL-PARAM') disBlo @else disNon @endif " >
            <div class="row mT10">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="m0">Parameter:</h4>
                            <input type="text" id="paramNameID" name="paramName" class="form-control input-lg" 
                                autocomplete=off @if (isset($cond) && isset($cond->CondOperDeet)) 
                                    value="{{ $cond->CondOperDeet }}" @endif >
                        </div>
                        <div class="col-md-6">
                            <h4 class="m0">Value:</h4>
                            <input type="text" id="paramValID" name="paramVal" class="form-control input-lg" 
                                autocomplete=off @if (isset($cond) && isset($cond->condVals) 
                                    && sizeof($cond->condVals) > 0) value="{{ $cond->condVals[0] }}" @endif >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php $condList = $GLOBALS["SL"]->getCondList(); ?>
    <div id="createNewCondComplex" class="mT20 mB20 
        @if (isset($cond) && isset($cond->CondOperator) && $cond->CondOperator == 'COMPLEX') disBlo 
        @else disNon @endif ">
        <div class="row"><div class="col-md-4">
            @forelse ($condList as $i => $c)
                @if ($i == ceil(sizeof($condList)/3) || $i == ceil(sizeof($condList)*2/3))
                    </div><div class="col-md-4">
                @endif
                <?php
                $hasMultCond = [false, false];
                if (isset($cond) && isset($cond->CondOperator) && $cond->CondOperator == 'COMPLEX' 
                    && isset($cond->condVals) && sizeof($cond->condVals) > 0) {
                    foreach ($cond->condVals as $j => $val) {
                        if ($val == $c->CondID) $hasMultCond[0] = true;
                        elseif (($val*(-1)) == $c->CondID) $hasMultCond[1] = true;
                    }
                }
                ?>
                <div class="pT5 pB5">
                    <label class="disIn"><nobr>
                        <input type="checkbox" id="multConds{{ $c->CondID }}" name="multConds[]" autocomplete="off"
                            value="{{ $c->CondID }}" class="multConds mR10" 
                            @if ($hasMultCond[0] || $hasMultCond[1]) CHECKED @endif
                            > {{ $c->CondTag }}
                    </nobr></label>
                    <div id="multConds{{ $c->CondID }}not" class="mL20 
                        @if ($hasMultCond[0] || $hasMultCond[1]) disIn @else disNon @endif ">
                        <label class="disIn"><nobr>
                            <input type="checkbox" id="multConds{{ $c->CondID }}n" name="multCondsNot[]" 
                            autocomplete="off" value="{{ $c->CondID }}" class="mR10"
                            @if ($hasMultCond[1]) CHECKED @endif
                            > ...Not <span class="fPerc66 slGrey">(opposite)</span>
                        </nobr></label>
                    </div>
                    <div id="multConds{{ $c->CondID }}desc" class="w100 slGrey 
                        @if ($hasMultCond[0] || $hasMultCond[1]) disBlo @else disNon @endif "><i>
                        {{ $c->CondDesc }}</i></div>
                </div>
            @empty
            @endforelse
        </div></div>
    </div>
    
    <div class="p5"></div>
    <label class="disBlo pB5 fPerc125">
        <input type="checkbox" name="CondPublicFilter" id="CondPublicFilterID" value="1" autocomplete="off"
            @if (isset($cond) && isset($cond->CondOpts) && $cond->CondOpts%2 == 0) CHECKED @endif 
            > Public Use: Condition is a default option when filtering search results
    </label>
    <a id="addArt" class="addArtBtn fPerc125" href="javascript:;">+ Add related article</a>
    <div id="condArticle" class=" @if (isset($cond) && isset($cond->CondOpts) && intVal($cond->CondOpts) > 0 
        && $cond->CondOpts%3 == 0) disBlo @else disNon @endif ">
        <div class="row mT5 fPerc80">
            <div class="col-md-6"><i>Article Title</i></div>
            <div class="col-md-6"><i>Article URL</i></div>
        </div>
        @for ($j=0; $j < 10; $j++)
            <div id="arti{{ $j }}" class="row @if (isset($condArticles) && isset($condArticles[$cond->CondID]) 
                && isset($condArticles[$cond->CondID][$j]) && isset($condArticles[$cond->CondID][$j][1])) 
                disBlo @else disNon @endif ">
                <div class="col-md-6">
                    <input name="condArtTitle{{ $j }}" id="condArtTitle{{ $j }}ID" type="text" class="form-control mB5" 
                        @if (isset($condArticles) && isset($condArticles[$cond->CondID]) 
                            && isset($condArticles[$cond->CondID]) && isset($condArticles[$cond->CondID][$j])
                            && isset($condArticles[$cond->CondID][$j][0]))
                            value="{{ $condArticles[$cond->CondID][$j][0] }}" 
                        @endif autocomplete=off >
                </div>
                <div class="col-md-6">
                    <input name="condArtUrl{{ $j }}" id="condArtUrl{{ $j }}ID" type="text" class="form-control mB5" 
                        @if (isset($condArticles) && isset($condArticles[$cond->CondID]) 
                            && isset($condArticles[$cond->CondID][$j])
                            && isset($condArticles[$cond->CondID][$j][1]))
                            value="{{ $condArticles[$cond->CondID][$j][1] }}" 
                        @endif autocomplete=off >
                </div>
            </div>
        @endfor
    </div>
    
    <div class="pT20 taC">
        <input type="submit" value="Add Condition" class="btn btn-lg btn-primary" >
    </div>

@if (!isset($cond->CondTag)) </div> @endif
