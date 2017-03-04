<div class="row mT10">
    <div class="col-md-4 f24">
        Data Field:
    </div>
    <div class="col-md-8">
        <select id="setFldID" name="setFld" class="form-control f20">
            <option value="" SELECTED ></option>
            <option value="EXISTS=0" > - If zeros records exist in this data set, then this condition clears.</option>
            <option value="EXISTS=1" > - If exactly one record exists in this data set, then this condition clears.</option>
            <option value="EXISTS>0" > - If one or more records exist in this data set, then this condition clears.</option>
            <option value="EXISTS>1" > - If more than one record exists in this data set, then this condition clears.</option>
            <option value="" DISABLED ></option>
            <option value="" DISABLED >------------------</option>
            <option value="" DISABLED >OR select a field below to clear this condition based on the user's response</option>
            <option value="" DISABLED >------------------</option>
            {!! $GLOBALS['SL']->getAllSetTblFldDrops($rSet) !!}
        </select>
    </div>
</div>
<script type="text/javascript"> 
$(document).ready(function(){
    $("#setFldID").change(function() {
        if (document.getElementById("setFldID").value == '') {
            document.getElementById("valSelect").innerHTML = '';
            document.getElementById("nameIt").style.display = 'none';
        }
        else if (document.getElementById("setFldID").value == 'EXISTS' || document.getElementById("setFldID").value == 'EXISTS>1') {
            document.getElementById("nameIt").style.display = 'block';
        }
        else {    
            //alert("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(document.getElementById("setFldID").value)+"");
            $("#valSelect").load("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(document.getElementById("setFldID").value)+"");
            document.getElementById("nameIt").style.display = 'block';
        }
    });
});
</script>
