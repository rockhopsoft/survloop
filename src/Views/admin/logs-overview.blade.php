<!-- resources/views/vendor/survloop/admin/logs-overview.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container"><div class="slCard">
@if ($phpInfo)
    {!! phpinfo() !!}
@else
    <h2 class="slBlueDark"><i class="fa fa-eye"></i> System Logs</h2>
    <h3>Logs of Session Stuff</h3>
    <div class="p20">{!! $logs["session"] !!}</div>
@endif
</div></div>

<div class="adminFootBuff"></div>
@endsection