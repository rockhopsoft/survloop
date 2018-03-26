<!-- resources/views/vendor/survloop/admin/logs-overview.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1 class="slBlueDark"><i class="fa fa-eye"></i> System Logs</h1>

<h2>Logs of Session Stuff</h2>
<div class="p20">{!! $logs["session"] !!}</div>

@endsection