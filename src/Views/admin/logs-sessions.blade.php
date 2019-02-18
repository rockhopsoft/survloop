<!-- resources/views/vendor/survloop/admin/logs-sessions.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
    <div class="slCard">
        <h2><i class="fa fa-eye"></i> Logs of Session Stuff</h2>
        <div class="p20">{!! $logs["session"] !!}</div>
    </div>
</div>
<div class="adminFootBuff"></div>
@endsection