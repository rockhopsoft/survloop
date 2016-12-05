<!-- resources/views/vendor/survloop/admin/db/ruleSpecifications.blade.php -->

<?php
$formURL = (($ruleID <= 0) ? '/dashboard/db/bus-rules/add' : '/dashboard/db/bus-rules/edit/' . $rule->RuleID );
$chkDis = (($dbAllowEdits) ? '' : ' DISABLED ');
?>

@extends('vendor.survloop.admin.admin')

@section('content')

@if ($dbAllowEdits)
    <form name="defEdit" action="{{ $formURL }}" method="post" autocomplete="off">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="ruleEditForm" value="YES">
@endif

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    @if ($ruleID > 0)
        Editing Rule #{{ $ruleID }}
        <?php /* <div class="pB10 mTn10"><i>{{ $rule->RuleStatement }}<br />{!! $tblTxt !!}</i></div> */ ?>
    @else
        Adding New Rule
    @endif
</h1>

<a href="/dashboard/db/bus-rules" class="btn btn-default mR10">All Business Rules</a>
{!! $saveBtn !!}
<div class="p5"></div>

<div class="panel panel-info">
    <div class="panel-heading"><h3 class="panel-title">Rule Information</h3></div>
    <div class="panel-body">
        <fieldset class="form-group">
            <label for="RuleStatementID" data-toggle="tooltip" data-placement="top" 
                title="{{ $GLOBALS['DB']->fldAbouts['FldStatement'] }}">Statement <span class="f10 gry9">?</span></label>
            <textarea class="form-control" id="RuleStatementID" name="RuleStatement" 
                rows="2" ' . $chkDis . '>{{ $rule->RuleStatement }}</textarea>
        </fieldset>
        <fieldset class="form-group">
            <label for="RuleConstraintID" data-toggle="tooltip" data-placement="top" 
                title="{{ $GLOBALS['DB']->fldAbouts['FldConstraint'] }}">Constraint <span class="f10 gry9">?</span></label>
            <textarea class="form-control" id="RuleConstraintID" name="RuleConstraint" 
                rows="2" ' . $chkDis . '>{{ $rule->RuleConstraint }}</textarea>
        </fieldset>
        <div class="row">
            <div class="col-md-4">
                <span data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['DB']->fldAbouts['FldIsAppOrient'] }}"
                    ><b>Type</b> <span class="f10 gry9">?</span></span>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType" id="rT2" value="2" {{ $chkDis }} 
                        @if ($rule->RuleIsAppOrient == 0) CHECKED @endif
                        > Database Oriented
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType" id="rT3" value="3" {{ $chkDis }} 
                        @if ($rule->RuleIsAppOrient == 1) CHECKED @endif
                        > Application Oriented
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <span data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['DB']->fldAbouts['FldIsRelation'] }}"
                    ><b>Category</b> <span class="f10 gry9">?</span></span>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType57" id="rT5" value="5" {{ $chkDis }} 
                        @if ($rule->RuleIsRelation == 0) CHECKED @endif
                        > Field Specific
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType57" id="rT7" value="7" {{ $chkDis }} 
                        @if ($rule->RuleIsRelation == 1) CHECKED @endif
                        > Relationship Specific
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <span data-toggle="tooltip" data-placement="top" title="{{ $GLOBALS['DB']->fldAbouts['FldTestOn'] }}"
                    ><b>Test On</b> <span class="f10 gry9">?</span></span>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT11" value="11" {{ $chkDis }} 
                        @if ($rule->RuleTestOn%11 == 0) CHECKED @endif
                        > Insert
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT13" value="13" {{ $chkDis }} 
                        @if ($rule->RuleTestOn%13 == 0) CHECKED @endif
                        > Update
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT17" value="17" {{ $chkDis }} 
                        @if ($rule->RuleTestOn%17 == 0) CHECKED @endif
                        > Delete
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading" data-toggle="tooltip" data-placement="top" 
        title="{{ $GLOBALS['DB']->fldAbouts['FldFields'] }}">
        <h3 class="panel-title">Structures Affected <span class="f10 gry9">?</span></h3>
    </div>
    <div class="panel-body">
        <div class="row pB20">
            <div class="col-md-3"><b>Table Names</b></div>
            <div class="col-md-9">
            @if ($dbAllowEdits) 
                <div id="tblSelect"><input type="hidden" name="RuleTables" 
                    id="RuleTablesID" value="{{ $rule->RuleTables }}"></div>
            @else
                {{ $tblTxt }}
            @endif
            </div>
        </div>
        <div class="row pT20">
            <div class="col-md-3"><b>Field Names</b></div>
            <div class="col-md-9">
            @if ($dbAllowEdits) 
                <div id="fldSelect"><input type="hidden" name="RuleFields" id="RuleFieldsID" value="{{ $rule->RuleFields }}"></div>
                <script type="text/javascript"> 
                //alert("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+" - /dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
                $(document).ready(function(){
                $("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+"");
                $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
                }); 
                </script>
            @else
                {{ $fldTxt }}
            @endif
            </div>
        </div>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><h3 class="panel-title">Field Elements Affected</h3></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <b>Physical Elements<b>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH2" value="2" {{ $chkDis }} 
                        @if ($rule->RulePhys%2 == 0) CHECKED @endif
                        > Data Type
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH3" value="3" {{ $chkDis }} 
                        @if ($rule->RulePhys%3 == 0) CHECKED @endif
                        > Length
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH5" value="5" {{ $chkDis }} 
                        @if ($rule->RulePhys%5 == 0) CHECKED @endif
                        > Decimal Places
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH7" value="7" {{ $chkDis }} 
                        @if ($rule->RulePhys%7 == 0) CHECKED @endif
                        > Character Support
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH11" value="11" {{ $chkDis }} 
                        @if ($rule->RulePhys%11 == 0) CHECKED @endif
                        > Input Mask
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH13" value="13" {{ $chkDis }} 
                        @if ($rule->RulePhys%13 == 0) CHECKED @endif
                        > Display Format
                    </label>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <b>Logical Elements<b>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL2" value="2" {{ $chkDis }} 
                        @if ($rule->RuleLogic%2 == 0) CHECKED @endif
                        > Key Type
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL3" value="3" {{ $chkDis }} 
                        @if ($rule->RuleLogic%3 == 0) CHECKED @endif
                        > Key Structure
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL5" value="5" {{ $chkDis }} 
                        @if ($rule->RuleLogic%5 == 0) CHECKED @endif
                        > Uniqueness
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL7" value="7" {{ $chkDis }} 
                        @if ($rule->RuleLogic%7 == 0) CHECKED @endif
                        > Null Support
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL11" value="11" {{ $chkDis }} 
                        @if ($rule->RuleLogic%11 == 0) CHECKED @endif
                        > Values Entered By
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL13" value="13" {{ $chkDis }} 
                        @if ($rule->RuleLogic%13 == 0) CHECKED @endif
                        > Required Value
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL17" value="17" {{ $chkDis }} 
                        @if ($rule->RuleLogic%17 == 0) CHECKED @endif
                        > Default Value
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL19" value="19" {{ $chkDis }} 
                        @if ($rule->RuleLogic%19 == 0) CHECKED @endif
                        > Range of Values
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL23" value="23" {{ $chkDis }} 
                        @if ($rule->RuleLogic%23 == 0) CHECKED @endif
                        > Comparisons Allowed
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL29" value="29" {{ $chkDis }} 
                        @if ($rule->RuleLogic%29 == 0) CHECKED @endif
                        > Operations Allowed
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL31" value="31" {{ $chkDis }} 
                        @if ($rule->RuleLogic%31 == 0) CHECKED @endif
                        > Edit Rule
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><h3 class="panel-title">Relationship Characteristics Affected</h3></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR2" value="2" {{ $chkDis }} 
                        @if ($rule->RuleRel%2 == 0) CHECKED @endif
                        > Deletion Rule
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR3" value="3" {{ $chkDis }} 
                        @if ($rule->RuleRel%3 == 0) CHECKED @endif
                        > Type of Participation
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR5" value="5" {{ $chkDis }} 
                        @if ($rule->RuleRel%5 == 0) CHECKED @endif
                        > Degree of Participation
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading" data-toggle="tooltip" data-placement="top" 
        title="{{ $GLOBALS['DB']->fldAbouts['FldAction'] }}">
        <h3 class="panel-title">Action Taken</h3>
    </div>
    <div class="panel-body">
    @if ($dbAllowEdits) 
        <fieldset class="form-group">
            <label for="RuleActionID" class="sr-only" >Action Taken</label>
            <textarea class="form-control" id="RuleActionID" name="RuleAction" 
                rows="4" ' . $chkDis . '>{{ $rule->RuleAction }}</textarea>
        </fieldset>
    @else 
        {{ $rule->RuleAction }}
    @endif
    </div>
</div>

@if ($dbAllowEdits) 
    <center>
    <br />
    {!! str_replace('btn-primary', 'btn-lg btn-primary f26', $saveBtn) !!}
    </form>
    </center>
@endif

<div class="adminFootBuff"></div>

@endsection