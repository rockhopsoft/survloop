<!-- resources/views/vendor/survloop/admin/db/rules.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    Business Rules 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

@if ($dbAllowEdits)
    <div class="p10">
        <a href="/dashboard/db/bus-rules/add" class="btn btn-default"
            ><i class="fa fa-plus-circle"></i> Add New Rule</a>
    </div>
@endif

<div class="row">
<div class="col-md-1"> </div>
<div class="col-md-7"><i>Statement, Constraint</i></div><div class="col-md-4"><i>Tables Affected</i></div>
</div>
@forelse ($rules as $cnt => $rule)
    <div class="row">
        <div class="col-md-1 p20"><a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" class="btn btn-primary">
        @if ($dbAllowEdits) Edit @else View @endif
        </a></div>
        <div class="col-md-7 p10"><h2 class="mT0">{!! $rule->RuleStatement !!}</h2>
            <div class="p5 gry9">{{ $rule->RuleConstraint }}</div></div>
        <div class="col-md-3 p10">
        @if (isset($ruleTbls[$cnt]) != '' && trim($ruleTbls[$cnt]) != ',')
            {!! $ruleTbls[$cnt] !!}
        @endif
        </div>
        <div class="col-md-1 p10">
        @if ($dbAllowEdits) 
            <a class="red" href="javascript:void(0)" 
                onClick="if (confirm('Are you sure you want to delete this rule?')) window.location='?delRule={{ $rule->RuleID }}';" 
                >Delete</a>
        @endif
        </div>
    </div>
    <div class="p20"><hr></div>
@empty
    <div class="alert alert-warning" role="alert" >No rules established yet.</div>
@endforelse

<div class="adminFootBuff"></div>

@endsection
