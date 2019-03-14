<!-- resources/views/vendor/survloop/admin/tree/cond-add-page.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="slCard nodeWrap">
<h2 class="slGreenDark"><i class="fa fa-filter" aria-hidden="true"></i> Add Condition / Filter</h2>
<form name="mainPageForm" method="post" action="/dashboard/db/conds/add" >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="addNewCond" value="1">

{!! view('vendor.survloop.admin.db.inc-addCondition', [ "newOnly" => true ])->render() !!}

</form>
</div></div>
@endsection