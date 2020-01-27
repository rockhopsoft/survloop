<!-- resources/views/vendor/survloop/admin/db/ruleSpecifications.blade.php -->

<?php
$formURL = (($ruleID <= 0) ? '/dashboard/db/bus-rules/add' : '/dashboard/db/bus-rules/edit/' . $rule->rule_id );
$chkDis = (($dbAllowEdits) ? '' : ' DISABLED ');
?>

@extends('vendor.survloop.master')

@section('content')

@if ($dbAllowEdits)
    <form name="mainPageForm" action="{{ $formURL }}" method="post" autocomplete="off">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="ruleEditForm" value="YES">
@endif

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
    @if ($ruleID > 0)
        Editing Rule #{{ $ruleID }}
        <?php /* <div class="pB10 mTn10"><i>{{ $rule->rule_statement }}<br />{!! $tblTxt !!}</i></div> */ ?>
    @else
        Adding New Rule
    @endif
</h1>

<a href="/dashboard/db/bus-rules" class="btn btn-secondary mR10">All Business Rules</a>
{!! $saveBtn !!}
<div class="p5"></div>

<div class="card">
    <div class="card-header"><h3>Rule Information</h3></div>
    <div class="card-body">
        <fieldset class="form-group">
            <label for="RuleStatementID" data-toggle="tooltip" data-placement="top" 
                title="{{ $GLOBALS['SL']->fldAbouts['FldStatement'] }}">
                Statement <span class="fPerc80 slGrey">?</span>
            </label>
            <textarea class="form-control" id="RuleStatementID" name="RuleStatement" 
                rows="2" {!! $chkDis !!} >{{ $rule->rule_statement }}</textarea>
        </fieldset>
        <fieldset class="form-group">
            <label for="RuleConstraintID" data-toggle="tooltip" data-placement="top" 
                title="{{ $GLOBALS['SL']->fldAbouts['FldConstraint'] }}">
                Constraint <span class="fPerc80 slGrey">?</span>
            </label>
            <textarea class="form-control" id="RuleConstraintID" name="RuleConstraint" 
                rows="2" {!! $chkDis !!} >{{ $rule->rule_constraint }}</textarea>
        </fieldset>
        <div class="row">
            <div class="col-4">
                <span data-toggle="tooltip" data-placement="top" title="{{ 
                    $GLOBALS['SL']->fldAbouts['FldIsAppOrient'] }}">
                    <b>Type</b> <span class="fPerc80 slGrey">?</span></span>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType" id="rT2" value="2" {{ $chkDis }} 
                        @if ($rule->rule_is_app_orient == 0) CHECKED @endif
                        > Database Oriented
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType" id="rT3" value="3" {{ $chkDis }} 
                        @if ($rule->rule_is_app_orient == 1) CHECKED @endif
                        > Application Oriented
                    </label>
                </div>
            </div>
            <div class="col-4">
                <span data-toggle="tooltip" data-placement="top" 
                    title="{{ $GLOBALS['SL']->fldAbouts['FldIsRelation'] }}"
                    ><b>Category</b> <span class="fPerc80 slGrey">?</span></span>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType57" id="rT5" value="5" {{ $chkDis }} 
                        @if ($rule->rule_is_relation == 0) CHECKED @endif
                        > Field Specific
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="RuleType57" id="rT7" value="7" {{ $chkDis }} 
                        @if ($rule->rule_is_relation == 1) CHECKED @endif
                        > Relationship Specific
                    </label>
                </div>
            </div>
            <div class="col-4">
                <span data-toggle="tooltip" data-placement="top" 
                    title="{{ $GLOBALS['SL']->fldAbouts['FldTestOn'] }}">
                    <b>Test On</b> <span class="fPerc80 slGrey">?</span></span>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT11" value="11" {{ $chkDis }} 
                        @if ($rule->rule_test_on%11 == 0) CHECKED @endif
                        > Insert
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT13" value="13" {{ $chkDis }} 
                        @if ($rule->rule_test_on%13 == 0) CHECKED @endif
                        > Update
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleTestOn[]" id="rT17" value="17" {{ $chkDis }} 
                        @if ($rule->rule_test_on%17 == 0) CHECKED @endif
                        > Delete
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" data-toggle="tooltip" data-placement="top" 
        title="{{ $GLOBALS['SL']->fldAbouts['FldFields'] }}">
        <h3>Structures Affected <span class="fPerc80 slGrey">?</span></h3>
    </div>
    <div class="card-body">
        <div class="row pB20">
            <div class="col-3"><b>Table Names</b></div>
            <div class="col-9">
            @if ($dbAllowEdits) 
                <div id="tblSelect"><input type="hidden" name="RuleTables" 
                    id="RuleTablesID" value="{{ $rule->rule_tables }}"></div>
            @else
                {{ $tblTxt }}
            @endif
            </div>
        </div>
        <div class="row pT20">
            <div class="col-3"><b>Field Names</b></div>
            <div class="col-9">
            @if ($dbAllowEdits) 
                <div id="fldSelect">
                    <input type="hidden" name="RuleFields" id="RuleFieldsID" 
                        value="{{ $rule->rule_fields }}">
                </div>
            @else
                {{ $fldTxt }}
            @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Field Elements Affected</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-3">
                <b>Physical Elements<b>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH2" value="2" {{ $chkDis }} 
                        @if ($rule->rule_phys%2 == 0) CHECKED @endif
                        > Data Type
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH3" value="3" {{ $chkDis }} 
                        @if ($rule->rule_phys%3 == 0) CHECKED @endif
                        > Length
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH5" value="5" {{ $chkDis }} 
                        @if ($rule->rule_phys%5 == 0) CHECKED @endif
                        > Decimal Places
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH7" value="7" {{ $chkDis }} 
                        @if ($rule->rule_phys%7 == 0) CHECKED @endif
                        > Character Support
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH11" value="11" {{ $chkDis }} 
                        @if ($rule->rule_phys%11 == 0) CHECKED @endif
                        > Input Mask
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RulePhys[]" id="rH13" value="13" {{ $chkDis }} 
                        @if ($rule->rule_phys%13 == 0) CHECKED @endif
                        > Display Format
                    </label>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-3">
                <b>Logical Elements<b>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL2" value="2" {{ $chkDis }} 
                        @if ($rule->rule_logic%2 == 0) CHECKED @endif
                        > Key Type
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL3" value="3" {{ $chkDis }} 
                        @if ($rule->rule_logic%3 == 0) CHECKED @endif
                        > Key Structure
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL5" value="5" {{ $chkDis }} 
                        @if ($rule->rule_logic%5 == 0) CHECKED @endif
                        > Uniqueness
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL7" value="7" {{ $chkDis }} 
                        @if ($rule->rule_logic%7 == 0) CHECKED @endif
                        > Null Support
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL11" value="11" {{ $chkDis }} 
                        @if ($rule->rule_logic%11 == 0) CHECKED @endif
                        > Values Entered By
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL13" value="13" {{ $chkDis }} 
                        @if ($rule->rule_logic%13 == 0) CHECKED @endif
                        > Required Value
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL17" value="17" {{ $chkDis }} 
                        @if ($rule->rule_logic%17 == 0) CHECKED @endif
                        > Default Value
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL19" value="19" {{ $chkDis }} 
                        @if ($rule->rule_logic%19 == 0) CHECKED @endif
                        > Range of Values
                    </label>
                </div>
            </div>
            <div class="col-3">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL23" value="23" {{ $chkDis }} 
                        @if ($rule->rule_logic%23 == 0) CHECKED @endif
                        > Comparisons Allowed
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL29" value="29" {{ $chkDis }} 
                        @if ($rule->rule_logic%29 == 0) CHECKED @endif
                        > Operations Allowed
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleLogic[]" id="rL31" value="31" {{ $chkDis }} 
                        @if ($rule->rule_logic%31 == 0) CHECKED @endif
                        > Edit Rule
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Relationship Characteristics Affected</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR2" value="2" {{ $chkDis }} 
                        @if ($rule->rule_rel%2 == 0) CHECKED @endif
                        > Deletion Rule
                    </label>
                </div>
            </div>
            <div class="col-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR3" value="3" {{ $chkDis }} 
                        @if ($rule->rule_rel%3 == 0) CHECKED @endif
                        > Type of Participation
                    </label>
                </div>
            </div>
            <div class="col-4">
                <div class="radio">
                    <label>
                        <input type="checkbox" name="RuleRel[]" id="rR5" value="5" {{ $chkDis }} 
                        @if ($rule->rule_rel%5 == 0) CHECKED @endif
                        > Degree of Participation
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" data-toggle="tooltip" data-placement="top" 
        title="{{ $GLOBALS['SL']->fldAbouts['FldAction'] }}">
        <h3>Action Taken</h3>
    </div>
    <div class="card-body">
    @if ($dbAllowEdits) 
        <fieldset class="form-group">
            <label for="RuleActionID" class="sr-only" >Action Taken</label>
            <textarea class="form-control" id="RuleActionID" name="RuleAction" 
                rows="4" {!! $chkDis !!} >{{ $rule->rule_action }}</textarea>
        </fieldset>
    @else 
        {{ $rule->rule_action }}
    @endif
    </div>
</div>

@if ($dbAllowEdits) 
    <center>
    <br />
    {!! str_replace('btn-primary', 'btn-lg btn-primary', $saveBtn) !!}
    </form>
    </center>
@endif

<div class="adminFootBuff"></div>

@endsection