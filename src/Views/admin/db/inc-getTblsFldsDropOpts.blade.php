<div class="row mT10">
    <div class="col-3 nPrompt">
        <h4 class="mT10">Data Field:</h4>
    </div>
    <div class="col-9">
        <select id="setFldID" name="setFld" class="form-control form-control-lg" autocomplete=off >
            <option value="" @if (!isset($cond)) SELECTED @endif ></option>
            <option value="EXISTS=0" @if (isset($cond) && isset($cond->cond_operator) && isset($cond->cond_oper_deet) 
                && $cond->cond_operator == 'EXISTS=' && intVal($cond->cond_oper_deet) == 0) SELECTED @endif 
                > - If zeros records exist in this data set, then this condition clears.</option>
            <option value="EXISTS=1" @if (isset($cond) && isset($cond->cond_operator) && isset($cond->cond_oper_deet) 
                && $cond->cond_operator == 'EXISTS=' && intVal($cond->cond_oper_deet) == 1) SELECTED @endif 
                > - If exactly one record exists in this data set, then this condition clears.</option>
            <option value="EXISTS>0" @if (isset($cond) && isset($cond->cond_operator) && isset($cond->cond_oper_deet) 
                && $cond->cond_operator == 'EXISTS>' && intVal($cond->cond_oper_deet) == 0) SELECTED @endif 
                > - If one or more records exist in this data set, then this condition clears.</option>
            <option value="EXISTS>1" @if (isset($cond) && isset($cond->cond_operator) && isset($cond->cond_oper_deet) 
                && $cond->cond_operator == 'EXISTS>' && intVal($cond->cond_oper_deet) == 1) SELECTED @endif 
                > - If more than one record exists in this data set, then this condition clears.</option>
            <option value="" DISABLED ></option>
            <option value="" DISABLED >------------------</option>
            <option value="" DISABLED 
                >OR select a field below to clear this condition based on the user's response</option>
            <option value="" DISABLED >------------------</option>
            @if (isset($setOptions)) {!! $setOptions !!} @endif
        </select>
    </div>
</div>