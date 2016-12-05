<!-- resources/views/vendor/survloop/admin/db/inc-getTblsFldVals.blade.php -->

<div class="p5"></div>

@if ($fldID != 'EXISTS' && $fldID != 'EXISTS>1')
    <label>
        <input type="radio" name="equals" value="equals" CHECKED > Condition clears if user selects one of these responses
    </label>
    <br />
    <label>
        <input type="radio" name="equals" value="notequals" > Condition clears if user doesn't select any of these
    </label>
    <div class="slBlueDark f26 pT10"><i>{{ $values["prompt"] }}</i></div>
    <div class="pL20 mT0">
        <div class="row">
            <div class="col-md-6">
                @if (sizeof($values["vals"]) > 0)
                    @for ($i = 0; $i < ceil(sizeof($values["vals"])/2); $i++)
                        <label>
                            <input type="checkbox" name="vals[]" value="{{ $values['vals'][$i][0] }}" > {{ $values['vals'][$i][1] }}
                        </label><br />
                    @endfor
                    </div>
                    <div class="col-md-6">
                    @for ($i = ceil(sizeof($values["vals"])/2); $i < sizeof($values["vals"]); $i++)
                        <label>
                            <input type="checkbox" name="vals[]" value="{{ $values['vals'][$i][0] }}" > {{ $values['vals'][$i][1] }}
                        </label><br />
                    @endfor
                @else
                    @for ($i = 0; $i < 10; $i+=2)
                        <div id="valsOpenDiv{{ $i }}" class="mB10 @if ($i > 0) disNon @endif ">
                            <input type="checkbox" name="valsOpen[]" value="{{ $i }}" checked disabled >
                            <input type="text" name="valsOpen{{ $i }}" value="" >
                        </div>
                    @endfor
                    </div>
                    <div class="col-md-6">
                    @for ($i = 1; $i < 10; $i+=2)
                        <div id="valsOpenDiv{{ $i }}" class="mB10 disNon">
                            <input type="checkbox" name="valsOpen[]" value="{{ $i }}" checked disabled >
                            <input type="text" name="valsOpen{{ $i }}" value="" >
                        </div>
                    @endfor
                @endif
            </div>
        </div>
        <a href="javascript:void(0)" id="addValResponse" class="disBlo" >Add A Response Option</a>
        <script type="text/javascript">
        $(document).ready(function(){
            var openResponses = 0;
            $("#addValResponse").click(function() { 
                openResponses++;
                $("#valsOpenDiv"+openResponses+"").slideDown('fast');
                if (openResponses == 9) $("#addValResponse").slideUp('fast');
            });
        });
        </script>
    </div>
@endif

