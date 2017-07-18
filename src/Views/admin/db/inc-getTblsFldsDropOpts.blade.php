<div class="row mT10">
    <div class="col-md-4 nPrompt">
        Data Field:
    </div>
    <div class="col-md-8">
        <select id="setFldID" name="setFld" class="form-control">
            <option value="" SELECTED ></option>
            <option value="EXISTS=0" > - If zeros records exist in this data set, then this condition clears.</option>
            <option value="EXISTS=1" > - If exactly one record exists in this data set, then this condition clears.</option>
            <option value="EXISTS>0" > - If one or more records exist in this data set, then this condition clears.</option>
            <option value="EXISTS>1" > - If more than one record exists in this data set, then this condition clears.</option>
            <option value="" DISABLED ></option>
            <option value="" DISABLED >------------------</option>
            <option value="" DISABLED >OR select a field below to clear this condition based on the user's response</option>
            <option value="" DISABLED >------------------</option>
            @if (isset($setOptions)) {!! $setOptions !!} @endif
        </select>
    </div>
</div>