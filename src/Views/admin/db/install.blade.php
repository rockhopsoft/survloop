<!-- resources/views/vendor/survloop/admin/db/install.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>:
    Database Installation Process 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="/dashboard/db/export" class="btn btn-default mR10">MySQL Export</a>
<a href="/dashboard/db/export/laravel" class="btn btn-default mR10">Export for Laravel</a>

<div class="clearfix p20"></div>

@if ($dbAllowEdits)
    <form name="runInstall" action="/dashboard/db/install" method="post" onSubmit="if (confirm('ARE YOU SURE?! Did you ask Morgan?')) { return true; } else { return false; }">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="dbConfirm" value="install">
@endif

<div class="row">
    <div class="col-md-6">
        <table class="table table-striped taC">
        <tr>
            <th class="taC">Table Name</th>
            <th class="taC">Create Table</th>
            <th class="taC">Transfer Old Data</th>
        </tr>
        @forelse ($tbls as $i => $tbl)
            <tr>
                <td>{{ $GLOBALS['SL']->dbRow->DbPrefix }}{{ $tbl->TblName }}</td>
                <td>
                    @if ($tbl->TblName != 'Users')
                        <input type="checkbox" name="createTable[]" value="{{ $tbl->TblID }}" CHECKED >
                    @endif
                </td>
                <td>
                    @if ($tbl->TblName != 'Users')
                        <input type="checkbox" name="copyData[]" value="{{ $tbl->TblID }}" CHECKED >
                    @endif
                </td>
            </tr>
        @empty
        @endforelse
        </table>
    </div>
    <div class="col-md-6">
        <div class="well mB20">
        This process will directly install the database to this Laravel Installation.
        You can optionally preserve the data currently stored in selected tables, 
        then it should delete all old tables and create new ones matching current database specifications.
        </div>
        <div class="p20 m20"></div>
        <input type="submit" class="btn btn-lg btn-primary pull-right" value="Yes, Re-Install Database"
        @if ($dbAllowEdits) ></form> @else DISABLED > @endif
        {!! $log !!}
    </div>
</div>
    
<div class="adminFootBuff"></div>

@endsection