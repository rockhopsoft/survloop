<!-- resources/views/vendor/survloop/admin/db/fieldSpecifications.blade.php -->
    
<div class="slCard nodeWrap"><div class="fieldSpecs">
    <h3>
        @if (isset($fld->FldName))
            <b>{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] }}{{ $fld->FldName }}:</b> 
        @else <b>New Field</b> 
        @endif
        @if ($GLOBALS['SL']->dbFullSpecs()) General Elements @endif
    </h3>
        <div class="row">
            <div class="col-6">
            
                <fieldset class="form-group">
                    <label for="FldNameID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldName'] }}">Field Name {!! $help !!}</label>
                    {!! $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] !!}
                    @if ($edit) <input type="text" id="FldNameID" name="FldName" value="{{ $fld->FldName }}" class="form-control" > 
                    @else {{ $fld->FldName }} 
                    @endif
                </fieldset>
                <fieldset class="form-group">
                    <label for="FldEngID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldEng'] }}">Label {!! $help !!}</label>
                    @if ($edit) <input type="text" id="FldEngID" name="FldEng" value="{{ $fld->FldEng }}" class="form-control" > 
                    @else {{ $fld->FldEng }} 
                    @endif
                </fieldset>
                
@if ($GLOBALS['SL']->dbFullSpecs())
                <fieldset class="form-group">
                    <label for="FldAliasID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldAlias'] }}">Alias(es) {!! $help !!}</label>
                    @if ($edit) <input type="text" id="FldAliasID" name="FldAlias" value="{{ $fld->FldAlias }}" class="form-control" > 
                    @else {{ $fld->FldAlias }} 
                    @endif
                </fieldset>
@endif
        
            </div>
            <div class="col-6">
            
                <fieldset class="form-group">
                    <label for="FldTableID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldTable'] }}">Parent Table {!! $help !!}</label>
                    @if ($edit)
                        <select id="FldTableID" name="FldTable" autocomplete="off" class="form-control" >
                        {!! view('vendor.survloop.admin.db.inc-getTblDropOpts', [ "presel" => $fld->FldTable ]) !!}
                        </select>
                    @else
                        {!! view('vendor.survloop.admin.db.inc-getTblName', [ 'id' => $fld->FldTable, 'link' => 0 ]) !!}
                    @endif
                </fieldset>
                
@if ($GLOBALS['SL']->dbFullSpecs())
            
                <fieldset class="form-group">
                    <label for="FldSpecTypeID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldSpecType'] }}">Specification Type {!! $help !!}</label>
                    @if ($edit)
                        <select name="FldSpecType" id="FldSpecTypeID" class="form-control" >
                            <option value="Unique" @if ($fld->FldSpecType == 'Unique' || $fld->FldSpecType == '') SELECTED @endif {{ $chkDis }} >Unique</option>
                            <option value="Generic" @if ($fld->FldSpecType == 'Generic') SELECTED @endif {{ $chkDis }} >Generic</option>
                            <option value="Replica" @if ($fld->FldSpecType == 'Replica') SELECTED @endif {{ $chkDis }} >Replica</option>
                        </select>
                    @else
                        {{ $fld->FldSpecType }}
                    @endif
                </fieldset>
                <fieldset class="formFldSpecSourceIDgroup">
                    <label for="FldSpecSourceID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldSpecSource'] }}">Source Specification {!! $help !!}</label>
                    @if (!$edit) 
                        @if ($fld->FldSpecSource > 0) <span class="fPerc80"><i>#{{ $fld->FldSpecSource }}</i></span> @endif
                    @else
                        <nobr><select name="FldSpecSource" id="FldSpecSourceID" class="form-control" >
                            <option value="">(load generic field)</option>
                            {!! view('vendor.survloop.admin.db.inc-getFldGenericOpts', [
                                "presel" => $fld->FldSpecSource, 
                                "dbFldGenerics" => ((isset($dbFldGenerics)) ? $dbFldGenerics : []) 
                            ]) !!}
                        </select>
                        <a onClick="if (document.getElementById('FldSpecSourceID').value != '') window.location='{{ $FldSpecSourceJSlnk }}&loadGeneric='+document.getElementById('FldSpecSourceID').value+'';" 
                            href="javascript:;" class="fPerc80"><span class="fPerc66"><i class="fa fa-upload"></i></span>Load</a></nobr><br /><br />
                        <input type="hidden" id="saveGenericID" name="saveGeneric" value="0">
                        @if ($fld->FldSpecType != 'Generic' || $fld->FldTable > 0)
                            <a href="javascript:;" onClick="saveGeneric();" class="fPerc80"><i class="fa fa-floppy-o"></i> Save Copy As Generic Field</a>
                        @endif
                        <div id="generState" class="disIn"></div>
                    @endif
                </fieldset>
                
@else

                <fieldset class="form-group">
                    <label for="FldForeignTableID">Is Foreign Key?</label>
                    @if ($edit)
                        <select id="FldForeignTableID" name="FldForeignTable" style="width: 300px;" class="form-control" 
                            onChange="if (this.value != '') { document.getElementById('FldTypeID').value='INT'; }"  >
                        {!! view('vendor.survloop.admin.db.inc-getTblDropOpts', [
                            "presel" => $fld->FldForeignTable, 
                            "blankDefTxt" => '(foreign table)'
                        ]) !!}
                        </select>
                    @else
                        {!! view('vendor.survloop.admin.db.inc-getTblName', [
                            "id" => $fld->FldForeignTable, 
                            "link" => 1, 
                            "xtraTxt" => '<i class="fa fa-link"></i>'
                        ]) !!}
                    @endif
                </fieldset>

@endif

            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <fieldset class="form-group">
                    <label for="FldDescID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldDesc'] }}">Description {!! $help !!}</label>
                    @if ($edit) <textarea id="FldDescID" name="FldDesc" rows="2" class="form-control">{{ $fld->FldDesc }}</textarea> 
                    @else {{ $fld->FldDesc }} 
                    @endif
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <fieldset class="form-group">
                    <label for="FldNotesID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldNotes'] }}">Memo {!! $help !!}</label>
                    @if ($edit) <textarea id="FldNotesID" name="FldNotes" rows="2" class="form-control">{{ $fld->FldNotes }}</textarea>
                    @else {{ $fld->FldNotes }} 
                    @endif
                </fieldset>
            </div>
        </div>
            
@if ($GLOBALS['SL']->dbFullSpecs())
    
</div></div>

<div class="slCard nodeWrap">
    <h3>
        <b>{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] }}{{ $fld->FldName }}:</b> Physical Elements
    </h3>
    <div class="row">
        <div class="col-6">
        
            <fieldset class="form-group">
                <label for="FldDataTypeID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldDataType'] }}">Data Type {!! $help !!}</label>
                @if ($edit)
                    <select id="FldDataTypeID" name="FldDataType" class="form-control" >
                        <option value="Alphanumeric" @if ($fld->FldDataType == 'Alphanumeric') SELECTED @endif >Alphanumeric</option>
                        <option value="Numeric" @if ($fld->FldDataType == 'Numeric') SELECTED @endif >Numeric</option>
                        <option value="DateTime" @if ($fld->FldDataType == 'DateTime') SELECTED @endif >DateTime</option>
                    </select>
                @else
                    {{ $fld->FldDataType }}
                @endif
            </fieldset>
            <fieldset class="form-group">
                <label for="FldDataLengthID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldDataLength'] }}">Length {!! $help !!}</label>
                @if ($edit) <input type="text" id="FldDataLengthID" name="FldDataLength" value="{{ $fld->FldDataLength }}" class="form-control" > 
                @else {{ $fld->FldDataLength }} 
                @endif
            </fieldset>
            <fieldset class="form-group">
                <label for="FldDataDecimalsID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldDataDecimals'] }}">Decimal Places {!! $help !!}</label>
                @if ($edit) <input type="text" id="FldDataDecimalsID" name="FldDataDecimals" value="{{ $fld->FldDataDecimals }}" class="form-control" > 
                @else {{ $fld->FldDataDecimals }} 
                @endif
            </fieldset>
        
        </div>
        <div class="col-6">
            
            <div class="row" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldCharSupport'] }}">
                <b>Character Support</b> {!! $help !!}
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="FldCharSupport[]" value="Letters" {{ $chkDis }} 
                            @if (strpos($fld->FldCharSupport, 'Letters') !== false) CHECKED @endif
                            > Letters (A-Z)
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="FldCharSupport[]" value="Numbers" {{ $chkDis }} 
                            @if (strpos($fld->FldCharSupport, 'Numbers') !== false) CHECKED @endif
                            > Numbers (0-9)
                        </label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="FldCharSupport[]" value="Keyboard" {{ $chkDis }} 
                            @if (strpos($fld->FldCharSupport, 'Keyboard') !== false) CHECKED @endif
                            > Keyboard (.,/$#%)
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="FldCharSupport[]" value="Special" {{ $chkDis }} 
                            @if (strpos($fld->FldCharSupport, 'Special') !== false) CHECKED @endif
                            > Special (&copy;&reg;&#8482;&sum;)
                        </label>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
        
    <div class="row">
        <div class="col-6">
            <fieldset class="form-group">
                <label for="FldInputMaskID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldInputMask'] }}">Input Mask {!! $help !!}</label>
                @if ($edit) <input type="text" id="FldInputMaskID" name="FldInputMask" value="{{ $fld->FldInputMask }}" class="form-control" > 
                @else {{ $fld->FldInputMask }} 
                @endif
            </fieldset>
        </div>
        <div class="col-6">
            <fieldset class="form-group">
                <label for="FldDisplayFormatID" data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['SL']->fldAbouts['FldDisplayFormat'] }}">Display Format {!! $help !!}</label>
                @if ($edit) <input type="text" id="FldDisplayFormatID" name="FldDisplayFormat" value="{{ $fld->FldDisplayFormat }}" class="form-control" > 
                @else {{ $fld->FldDisplayFormat }} 
                @endif
            </fieldset>
        </div>
    </div>
        
@endif
    
    <div class="row">
        <div class="col-12">
            <fieldset class="form-group">
                <label for="FldTypeID" data-toggle="tooltip" data-placement="top" 
                    title="{{ $GLOBALS['SL']->fldAbouts['FldType'] }}">MySQL Type {!! $help !!}</label>
                @if ($edit)
                    <select id="FldTypeID" name="FldType" class="form-control" >
                    @foreach ($FldDataTypes as $type => $eng) {
                        <option value="{{ $type }}" 
                        @if ($fld->FldType == $type || (trim($fld->FldType) == '' && $type == 'VARCHAR')) SELECTED @endif
                        >{{ $type }} - {{ $eng[0] }}</option>
                    @endforeach
                    </select>
                @else
                    {{ $fld->FldType }} - {{ $FldDataTypes[$fld->FldType][0] }}
                @endif
            </fieldset>
        </div>
    </div>
    
@if ($GLOBALS['SL']->dbFullSpecs())

</div>
    
<div class="slCard nodeWrap">
    <h3>
        <b>{{ $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] }}{{ $fld->FldName }}:</b> Logical Elements
    </h3>
    <div class="row">
        <div class="col-4">
            
            <div class="row">
                <div class="col-12">
                    <div data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldKeyType'] }}">
                        <b>Key Type</b> {!! $help !!}
                    </div>
                    <div class="checkbox-inline">
                        <label class="nobld">
                            <input type="checkbox" id="keyNon{{ $fldSfx }}" name="FldKeyType[]" value="Non" 
                            {{ $chkDis }} 
                            @if (strpos($fld->FldKeyType, 'Non') !== false) CHECKED @endif
                            onClick="checkKey('{{ $fldSfx }}', 0);" > Non
                        </label>
                    </div>
                    <div class="checkbox-inline">
                        <label class="nobld">
                            <input type="checkbox" id="keyPri{{ $fldSfx }}" name="FldKeyType[]" value="Primary" 
                            {{ $chkDis }} 
                            @if (strpos($fld->FldKeyType, 'Primary') !== false) CHECKED @endif
                            onClick="checkKey('{{ $fldSfx }}', 1);" > Primary
                        </label>
                    </div>
                    <div></div>
                    <div class="checkbox-inline">
                        <label class="nobld">
                            <input type="checkbox" id="keyFor{{ $fldSfx }}" name="FldKeyType[]" value="Foreign" 
                            {{ $chkDis }} id="keyFor{{ $fldSfx }}" 
                            @if (strpos($fld->FldKeyType, 'Foreign') !== false) CHECKED @endif
                            onClick="checkKey('{{ $fldSfx }}', 1);" > Foreign
                        </label>
                    </div>
                    <div class="checkbox-inline">
                        <label class="nobld">
                            <input type="checkbox" id="keyAlt{{ $fldSfx }}" name="FldKeyType[]" value="Alternate" 
                            @if (strpos($fld->FldKeyType, 'Alternate') !== false) CHECKED @endif 
                            {{ $chkDis }} onClick="checkKey('{{ $fldSfx }}', 1);" > Alternate
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldKeyStruct'] }}">
                        <b>Key Structure</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldKeyStruct{{ $fldSfx }}" value="Simple" {{ $chkDis }} 
                            @if ($fld->FldKeyStruct == 'Simple') CHECKED @endif
                            > Simple
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldKeyStruct{{ $fldSfx }}" value="Composite" {{ $chkDis }} 
                            @if ($fld->FldKeyStruct == 'Composite') CHECKED @endif 
                            > Composite
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div id="foreign{{ $fldSfx }}" 
                        class=" @if (strpos($fld->FldKeyType, 'Foreign') !== false) disBlo @else disNon @endif ">
                        <fieldset class="form-group">
                            <label for="FldForeignTableID">Foreign Table:</label>
                            @if ($edit)
                                <select id="FldForeignTableID" name="FldForeignTable" class="form-control" 
                                onChange="if (this.value != '') { document.getElementById('FldTypeID').value='INT'; }" >
                                {!! view('vendor.survloop.admin.db.inc-getTblDropOpts', [ 
                                    "presel" => $fld->FldForeignTable ]) !!}
                                </select>
                            @elseif ($fld->FldForeignTable > 0 
                                && isset($GLOBALS['SL']->tbl[$fld->FldForeignTable]))
                                {!! $GLOBALS['SL']->tblEng[$fld->FldForeignTable] !!}
                            @endif
                        </fieldset>
                        <div class="mTn5" data-toggle="tooltip" data-placement="top" 
                            title="Degree of Participation A-B: How many 
                            @if ($fld->FldForeignTable > 0 && isset($GLOBALS['SL']->tbl[$fld->FldForeignTable])) 
                                {{ $GLOBALS['SL']->tblEng[$fld->FldForeignTable] }} @else Other Table 
                            @endif
                            records can be associated with a single record from 
                            @if ($fld->FldTable > 0 && isset($GLOBALS['SL']->tbl[$fld->FldTable])) 
                                {{ $GLOBALS['SL']->tblEng[$fld->FldTable] }}? @else This Table? 
                            @endif ">
                            <i># of foreign records to one of these records:</i>
                        </div>
                        <div>
                            <nobr>( 
                            @if ($edit)
                                <label for="FldForeign2MinID">min:</label> 
                                <select id="FldForeign2MinID" name="FldForeign2Min" style="width: 70px;">
                                {!! view('vendor.survloop.admin.db.inc-getLinkCnt', [
                                    "presel" => trim($fld->FldForeign2Min)
                                    ])->render() !!}
                                </select>, <label for="FldForeign2MaxID">max:</label> 
                                <select id="FldForeign2MaxID" name="FldForeign2Max" style="width: 70px;">
                                {!! view('vendor.survloop.admin.db.inc-getLinkCnt', [
                                    "presel" => trim($fld->FldForeign2Max)
                                    ])->render() !!}
                                </select>
                            @else min: {{ $fld->FldForeign2Min }}, max: {{ $fld->FldForeign2Max }}
                            @endif
                             )</nobr>
                        </div>
                        <div data-toggle="tooltip" data-placement="top" 
                            title="Degree of Participation B-A: How many {{ 
                                view('vendor.survloop.admin.db.inc-getTblName', [
                                    'id' => $fld->FldTable,
                                    'link' => 0
                                ]) }} records can be associated with a single record from {{ 
                                view('vendor.survloop.admin.db.inc-getTblName', [
                                    'id' => $fld->FldForeignTable,
                                    'link' => 0
                                    ]) }} ?"><i># of these records to one foreign record:</i>
                        </div>
                        <div class="pB20">
                        @if ($edit)
                            <nobr>( <label for="FldForeignMin">min:</label> 
                                <select id="FldForeignMinID" name="FldForeignMin" style="width: 70px;">
                                {!! view('vendor.survloop.admin.db.inc-getLinkCnt', [
                                    "presel" => trim($fld->FldForeignMin) ])->render() !!}
                            </select>, 
                            <label for="FldForeignMax">max:</label> 
                            <select id="FldForeignMaxID" name="FldForeignMax" style="width: 70px;">
                            {!! view('vendor.survloop.admin.db.inc-getLinkCnt', [
                                "presel" => trim($fld->FldForeignMax) ])->render() !!}
                            </select></nobr>
                        @else
                            <nobr>( min: {{ $fld->FldForeignMin }}, max: {{ $fld->FldForeignMax }} )</span></nobr>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="col-4">
        
            <div class="row">
                <div class="col-12">
                    <div data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldRequired'] }}">
                        <b>Required Value</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="required{{ $fldSfx }}" value="0" {{ $chkDis }} 
                            @if ($fld->FldRequired == 0) CHECKED @endif
                            > No
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="required{{ $fldSfx }}" value="1" {{ $chkDis }} 
                            @if ($fld->FldRequired == 1) CHECKED @endif 
                            > Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="pT20" data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldNullSupport'] }}">
                        <b>Null Support</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldNullSupport{{ $fldSfx }}" value="1" {{ $chkDis }} 
                            @if (!isset($fld->FldNullSupport) || $fld->FldNullSupport == 1) CHECKED @endif
                            > Nulls Allowed
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldNullSupport{{ $fldSfx }}" value="0" {{ $chkDis }} 
                            @if (isset($fld->FldNullSupport) && $fld->FldNullSupport == 0) CHECKED @endif 
                            > No Nulls
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="pT20" data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldKeyStruct'] }}">
                        <b>FldUnique</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldUnique{{ $fldSfx }}" value="0" {{ $chkDis }} 
                            @if ($fld->FldUnique == 0) CHECKED @endif
                            > Non-unique
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldUnique{{ $fldSfx }}" value="1" {{ $chkDis }} 
                            @if ($fld->FldUnique == 1) CHECKED @endif 
                            > Unique
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="pT20" data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldIsIndex'] }}">
                        <b>Index Support</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldIsIndex{{ $fldSfx }}" value="1" {{ $chkDis }} 
                            @if ($fld->FldIsIndex == 1) CHECKED @endif
                            > Indexed
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldIsIndex{{ $fldSfx }}" value="0" {{ $chkDis }} 
                            @if ($fld->FldIsIndex == 0) CHECKED @endif 
                            > Not Indexed
                        </label>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="col-4">
        
            <div class="row">
                <div class="col-12">
                    <div data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldEditRule'] }}">
                        <b>Edit Rule</b> {!! $help !!}
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="FldEditRule{{ $fldSfx }}" value="NowAllowed" {{ $chkDis }} 
                            @if ($fld->FldEditRule == 'NowAllowed') CHECKED @endif
                            > Enter Now, Edits Allowed
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="FldEditRule{{ $fldSfx }}" value="LateAllow" {{ $chkDis }} 
                            @if ($fld->FldEditRule == 'LateAllow') CHECKED @endif 
                            > Enter Later, Edits Allowed
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="FldEditRule{{ $fldSfx }}" value="NowNot" {{ $chkDis }} 
                            @if ($fld->FldEditRule == 'NowNot') CHECKED @endif 
                            > Enter Now, Edits Not Allowed
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="FldEditRule{{ $fldSfx }}" value="LateNot" {{ $chkDis }} 
                            @if ($fld->FldEditRule == 'LateNot') CHECKED @endif 
                            > Enter Later, Edits Not Allowed
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="FldEditRule{{ $fldSfx }}" value="NotDeterm" {{ $chkDis }} 
                            @if ($fld->FldEditRule == 'NotDeterm') CHECKED @endif 
                            > Not Determined At This Time
                        </label>
                    </div>
                    
                    <div class="pT20" data-toggle="tooltip" data-placement="top" 
                        title="{{ $GLOBALS['SL']->fldAbouts['FldValuesEnteredBy'] }}">
                        <b>Values Entered By</b> {!! $help !!}
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldValuesEnteredBy{{ $fldSfx }}" value="User" {{ $chkDis }} 
                            @if ($fld->FldValuesEnteredBy == 'User') CHECKED @endif
                            > User
                        </label>
                    </div>
                    <div class="radio-inline">
                        <label class="nobld">
                            <input type="radio" name="FldValuesEnteredBy{{ $fldSfx }}" value="System" {{ $chkDis }} 
                            @if ($fld->FldValuesEnteredBy == 'System') CHECKED @endif 
                            > System
                        </label>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

@endif

    <div class="row">
        <div class="col-12">
            <fieldset class="form-group">
                <label for="FldDefaultID" data-toggle="tooltip" data-placement="top" 
                    title="{{ $GLOBALS['SL']->fldAbouts['FldDefault'] }}">Default Value {!! $help !!}</label>
                @if ($edit) <input type="text" id="FldDefaultID" name="FldDefault" value="{{ $fld->FldDefault }}" class="form-control" > 
                @else {{ $fld->FldDefault }} 
                @endif
            </fieldset>
        </div>
    </div>
    <div class="row">
    @if (!$edit)
        <div class="col-12" data-toggle="tooltip" data-placement="top" 
            title="{{ $GLOBALS['SL']->fldAbouts['FldValues'] }}">
            Range of Values {!! $help !!}
            @if ($defSet == '') {{ $fld->FldValues }} 
            @elseif (isset($dbDefOpts[$defSet])) {{ $dbDefOpts[$defSet][0] }} <i>({{ $fld->FldValues }})</i>
            @endif
        </div>
    @else
        <div class="col-8">
            <fieldset class="form-group">
                <label for="FldValuesID" data-toggle="tooltip" data-placement="top" 
                    title="{{ $GLOBALS['SL']->fldAbouts['FldValues'] }}">Range of Values {!! $help !!}</label>
                <input type="text" id="FldValuesID" name="FldValues" class="form-control" 
                    style="color: @if ($defSet == '') #000 @else #999 @endif ;" 
                    @if ($defSet == '') value="{{ $fld->FldValues }}" 
                    @elseif (isset($dbDefOpts[$defSet])) value="{{ $dbDefOpts[$defSet][0] }}" DISABLED @endif >
            </fieldset>
        </div>
        <div class="col-4">
            <fieldset class="form-group">
                <label for="FldValuesDefID">Use Definition: 
                    <a href="/dashboard/db/definitions" target="_blank" class="f12"><i class="fa fa-book"></i></a>
                </label> 
                <select id="FldValuesDefID" name="FldValuesDef" class="form-control" 
                    onChange="return loadDef(this.value);">
                    <option value="" @if ($defSet == '') SELECTED @endif >[load definition, generic values]</option>
                @forelse ($defDeets as $set => $def)
                    <option value="{{ $set }}" @if ($defSet == $set) SELECTED @endif 
                        >{{ $set }} ({{ substr($def[0], 1, 15) }}...)</option>
                @empty
                @endforelse
                </select>
            </fieldset>
        </div>
    @endif
    </div>
        
@if ($GLOBALS['SL']->dbFullSpecs())

    <div class="row mT20">
        <div class="col-12">
            <div data-toggle="tooltip" data-placement="top"  title="{{ $GLOBALS['SL']->fldAbouts['FldCompareSame'] }}">
                <b>Comparisons Allowed</b> {!! $help !!}
            </div>
        </div>
    </div>
    
    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="cs2">
                    <input type="checkbox" name="FldCompareSame[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%2 == 0) CHECKED @endif 
                    id="cs2" onClick="chkCom2('s');" > Same Field
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs3">
                    <input type="checkbox" name="FldCompareSame[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs3" onClick="chkCom3('s');" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs5">
                    <input type="checkbox" name="FldCompareSame[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%5 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs5" onClick="chkCom5('s');" > =
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs7">
                    <input type="checkbox" name="FldCompareSame[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%7 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs7" onClick="chkCom7('s');" > &gt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs11">
                    <input type="checkbox" name="FldCompareSame[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%11 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs11" onClick="chkCom11('s');" > &ge;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs13">
                    <input type="checkbox" name="FldCompareSame[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%13 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs13" onClick="chkCom13('s');" > &ne;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs17">
                    <input type="checkbox" name="FldCompareSame[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%17 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs17" onClick="chkComX('s');" > &lt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cs19">
                    <input type="checkbox" name="FldCompareSame[]" value="19" {{ $chkDis }} 
                    @if ($fld->FldCompareSame%19 == 0 || $fld->FldCompareSame%3 == 0) CHECKED @endif 
                    id="cs19" onClick="chkComX('s');" > &le;
                </label>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
    
    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="co2">
                    <input type="checkbox" name="FldCompareOther[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%2 == 0) CHECKED @endif 
                    id="co2" onClick="chkCom2('o');" > Other Fields
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co3">
                    <input type="checkbox" name="FldCompareOther[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co3" onClick="chkCom3('o');" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co5">
                    <input type="checkbox" name="FldCompareOther[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%5 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co5" onClick="chkCom5('o');" > =
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co7">
                    <input type="checkbox" name="FldCompareOther[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%7 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co7" onClick="chkCom7('o');" > &gt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co11">
                    <input type="checkbox" name="FldCompareOther[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%11 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co11" onClick="chkCom11('o');" > &ge;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co13">
                    <input type="checkbox" name="FldCompareOther[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%13 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co13" onClick="chkCom13('o');" > &ne;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co17">
                    <input type="checkbox" name="FldCompareOther[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%17 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co17" onClick="chkComX('o');" > &lt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="co19">
                    <input type="checkbox" name="FldCompareOther[]" value="19" {{ $chkDis }} 
                    @if ($fld->FldCompareOther%19 == 0 || $fld->FldCompareOther%3 == 0) CHECKED @endif 
                    id="co19" onClick="chkComX('o');" > &le;
                </label>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
    
    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="cv2">
                    <input type="checkbox" name="FldCompareValue[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%2 == 0) CHECKED @endif 
                    id="cv2" onClick="chkCom2('v');" > Value Expression
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv3">
                    <input type="checkbox" name="FldCompareValue[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv3" onClick="chkCom3('v');" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv5">
                    <input type="checkbox" name="FldCompareValue[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%5 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv5" onClick="chkCom5('v');" > =
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv7">
                    <input type="checkbox" name="FldCompareValue[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%7 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv7" onClick="chkCom7('v');" > &gt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv11">
                    <input type="checkbox" name="FldCompareValue[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%11 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv11" onClick="chkCom11('v');" > &ge;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv13">
                    <input type="checkbox" name="FldCompareValue[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%13 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv13" onClick="chkCom13('v');" > &ne;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv17">
                    <input type="checkbox" name="FldCompareValue[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%17 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv17" onClick="chkComX('v');" > &lt;
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="cv19">
                    <input type="checkbox" name="FldCompareValue[]" value="19" {{ $chkDis }} 
                    @if ($fld->FldCompareValue%19 == 0 || $fld->FldCompareValue%3 == 0) CHECKED @endif 
                    id="cv19" onClick="chkComX('v');" > &le;
                </label>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
    
    <div class="row mT20">
        <div class="col-12">
            <div data-toggle="tooltip" data-placement="top"  title="{{ $GLOBALS['SL']->fldAbouts['FldOperateSame'] }}">
                <b>Operations Allowed</b> {!! $help !!}
            </div>
        </div>
    </div>
    
    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="os2">
                    <input type="checkbox" name="FldOperateSame[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%2 == 0) CHECKED @endif 
                    id="os2" onClick="chkOp2('s', this.checked);" > Same Field
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="os3">
                    <input type="checkbox" name="FldOperateSame[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os3" onClick="chkOp3('s', this.checked);" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="os5">
                    <input type="checkbox" name="FldOperateSame[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%5 == 0 || $fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os5" onClick="chkOp5('s', this.checked);" > +
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="os7">
                    <input type="checkbox" name="FldOperateSame[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%7 == 0 || $fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os7" onClick="chkOp7('s', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="os11">
                    <input type="checkbox" name="FldOperateSame[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%11 == 0 || $fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os11" onClick="chkOpX('s', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="os13">
                    <input type="checkbox" name="FldOperateSame[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%13 == 0 || $fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os13" onClick="chkOpX('s', this.checked);" > &divide;
                </label>
            </div>
        </div>
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="os17">
                    <input type="checkbox" name="FldOperateSame[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldOperateSame%17 == 0 || $fld->FldOperateSame%3 == 0) CHECKED @endif 
                    id="os17" onClick="chkOpX('s', this.checked);" > Concatenation
                </label>
            </div>
        </div>
        <div class="col-2"></div>
    </div>

    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="oo2">
                    <input type="checkbox" name="FldOperateOther[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%2 == 0) CHECKED @endif 
                    id="oo2" onClick="chkOp2('o', this.checked);" > Other Fields
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="oo3">
                    <input type="checkbox" name="FldOperateOther[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo3" onClick="chkOp3('o', this.checked);" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="oo5">
                    <input type="checkbox" name="FldOperateOther[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%5 == 0 || $fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo5" onClick="chkOp5('o', this.checked);" > +
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="oo7">
                    <input type="checkbox" name="FldOperateOther[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%7 == 0 || $fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo7" onClick="chkOp7('o', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="oo11">
                    <input type="checkbox" name="FldOperateOther[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%11 == 0 || $fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo11" onClick="chkOpX('o', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="oo13">
                    <input type="checkbox" name="FldOperateOther[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%13 == 0 || $fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo13" onClick="chkOpX('o', this.checked);" > &divide;
                </label>
            </div>
        </div>
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="oo17">
                    <input type="checkbox" name="FldOperateOther[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldOperateOther%17 == 0 || $fld->FldOperateOther%3 == 0) CHECKED @endif 
                    id="oo17" onClick="chkOpX('o', this.checked);" > Concatenation
                </label>
            </div>
        </div>
        <div class="col-2"></div>
    </div>

    <div class="row mB10">
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="ov2">
                    <input type="checkbox" name="FldOperateValue[]" value="2" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%2 == 0) CHECKED @endif 
                    id="ov2" onClick="chkOp2('v', this.checked);" > Value Expression
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="ov3">
                    <input type="checkbox" name="FldOperateValue[]" value="3" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov3" onClick="chkOp3('v', this.checked);" > All
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="ov5">
                    <input type="checkbox" name="FldOperateValue[]" value="5" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%5 == 0 || $fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov5" onClick="chkOp5('v', this.checked);" > +
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="ov7">
                    <input type="checkbox" name="FldOperateValue[]" value="7" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%7 == 0 || $fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov7" onClick="chkOp7('v', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="ov11">
                    <input type="checkbox" name="FldOperateValue[]" value="11" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%11 == 0 || $fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov11" onClick="chkOpX('v', this.checked);" > -
                </label>
            </div>
        </div>
        <div class="col-1">
            <div class="radio-inline">
                <label class="nobld" for="ov13">
                    <input type="checkbox" name="FldOperateValue[]" value="13" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%13 == 0 || $fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov13" onClick="chkOpX('v', this.checked);" > &divide;
                </label>
            </div>
        </div>
        <div class="col-2">
            <div class="radio-inline">
                <label class="nobld" for="ov17">
                    <input type="checkbox" name="FldOperateValue[]" value="17" {{ $chkDis }} 
                    @if ($fld->FldOperateValue%17 == 0 || $fld->FldOperateValue%3 == 0) CHECKED @endif 
                    id="ov17" onClick="chkOpX('v', this.checked);" > Concatenation
                </label>
            </div>
        </div>
        <div class="col-2"></div>
    </div>
    
@endif
    
</div>

@if ($edit)
    <script type="text/javascript"> 
    var definitions = new Array();
    {!! $defDeetsJS !!}
    function loadDef(defVal) {
        if (defVal == "") {
            document.getElementById("FldValuesID").disabled = false;
            document.getElementById("FldValuesID").value = "";
            document.getElementById("FldValuesID").style.color = "#000";
        } else {
            for (var i=0; i<definitions.length; i++) {
                if (definitions[i][0] == defVal) {
                    document.getElementById("FldValuesID").disabled = true;
                    document.getElementById("FldValuesID").value = definitions[i][1];
                    document.getElementById("FldValuesID").style.color = "#999";
                }
            }
        }
        return true;
    }
    </script>
@endif

