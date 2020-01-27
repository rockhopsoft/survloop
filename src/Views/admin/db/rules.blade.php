<!-- resources/views/vendor/survloop/admin/db/rules.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="slCard nodeWrap">
@if ($dbAllowEdits)
    <a href="/dashboard/db/bus-rules/add" class="btn btn-secondary pull-right"
        ><i class="fa fa-plus-circle"></i> Add New Rule</a>
@endif
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> {{ $GLOBALS['SL']->dbRow->db_name }}</span>: Business Rules 
    <nobr>({!! strip_tags($dbStats) !!})</nobr>
</h1>

<div class="row">
<div class="col-1"> </div>
<div class="col-7"><i>Statement, Constraint</i></div><div class="col-4"><i>Tables Affected</i></div>
</div>
@forelse ($rules as $cnt => $rule)
    <div class="row">
        <div class="col-1 p20"><a href="/dashboard/db/bus-rules/edit/{{ $rule->rule_id }}" class="btn btn-primary">
        @if ($dbAllowEdits) Edit @else View @endif
        </a></div>
        <div class="col-7 p10"><h2 class="mT0">{!! $rule->rule_statement !!}</h2>
            <div class="p5 slGrey">{{ $rule->rule_constraint }}</div></div>
        <div class="col-3 p10">
        @if (isset($ruleTbls[$cnt]) != '' && trim($ruleTbls[$cnt]) != ',')
            {!! $ruleTbls[$cnt] !!}
        @endif
        </div>
        <div class="col-1 p10">
        @if ($dbAllowEdits) 
            <a class="red" href="javascript:;" 
                onClick="if (confirm('Are you sure you want to delete this rule?')) window.location='?delRule={{ 
                    $rule->rule_id }}';" >Delete</a>
        @endif
        </div>
    </div>
    <div class="p20"><hr></div>
@empty
    <div class="alert alert-warning" role="alert" >No rules established yet.</div>
@endforelse
</div>

</div>
@endsection
