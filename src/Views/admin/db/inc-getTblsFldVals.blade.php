<!-- resources/views/vendor/survloop/admin/db/inc-getTblsFldVals.blade.php -->

<div class="p5"></div>

@if (!isset($cond) || $cond->CondField != 'EXISTS' && $cond->CondField != 'EXISTS>1')
    <div class="row">
        <div class="col-6">
            <h4 class="mT5"><label class="disBlo">
                <input type="radio" name="equals" value="equals" autocomplete=off 
                    @if (!isset($cond) || !isset($cond->cond_operator) 
                        || $cond->cond_operator == '{') 
                        CHECKED
                    @endif > True if user selects one of these responses:
            </label><label class="disBlo">
                <input type="radio" name="equals" value="notequals" autocomplete=off 
                    @if (isset($cond) && isset($cond->cond_operator) 
                        && $cond->cond_operator == '}')
                        CHECKED
                    @endif > True if user doesn't select any of these responses:
            </label></h4>
        </div>
        <div class="col-6">
            <div class="slBlueDark pT5">
                <i class="mR10">Question:</i> {{ $values["prompt"] }}
            </div>
            <i class="disIn">Responses:</i>
            @forelse ($values["vals"] as $i => $response)
                <label class="disIn mL10 mR10"><nobr>
                    <input type="checkbox" name="vals[]" 
                        value="{{ $response[0] }}" autocomplete=off 
                        @if (isset($cond) && sizeof($cond->condVals) > 0)
                            @foreach ($cond->condVals as $v => $val)
                                @if ($val == $response[0]) CHECKED @endif 
                            @endforeach
                        @endif > {{ $response[1] }}
                </nobr></label>
            @empty
            @endforelse
            <?php /* <a href="javascript:;" id="addValResponse" class="disIn mL20 fPerc80" 
                ><nobr>Add A Response Option</nobr></a>
            @for ($i = 0; $i < 10; $i+=2)
                <div id="valsOpenDiv{{ $i }}" class="mB10 disNon">
                    <input type="text" name="valsOpen{{ $i }}" 
                        class="form-control w100" autocomplete=off >
                </div>
            @endfor */ ?>
        </div>
    </div>
@endif
