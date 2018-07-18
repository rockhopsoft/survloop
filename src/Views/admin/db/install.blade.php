<!-- resources/views/vendor/survloop/admin/db/install.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<nobr><span class="pull-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> {{ $GLOBALS['SL']->dbRow->DbName 
        }}</span>: Database Installation Process 
</h1>

{!! view('vendor.survloop.admin.db.export-tabs', [ "curr" => 'install' ])->render() !!}
<div id="myTabContent" class="tab-content">

@if ($dbAllowEdits)
    <form name="mainPageForm" action="/dashboard/db/install" method="post" onSubmit="if (confirm('ARE YOU SURE?! Did you ask Morgan?')) { return true; } else { return false; }">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="dbConfirm" value="install">
@endif

    <div class="row mT10">
        <div class="col-md-6">
            <table class="table table-striped taC">
            <tr>
                <th class="taC">Table Name</th>
                <th class="taC">Create Table</th>
                <th class="taC">Transfer Old Data</th>
            </tr>
            @forelse ($tbls as $i => $tbl)
                @if (strtolower($tbl->TblName) != 'users')
                    <tr>
                        <td>{{ $GLOBALS['SL']->dbRow->DbPrefix }}{{ $tbl->TblName }}</td>
                        <td><input type="checkbox" name="createTable[]" value="{{ $tbl->TblID }}" CHECKED ></td>
                        <td><input type="checkbox" name="copyData[]" value="{{ $tbl->TblID }}" CHECKED ></td>
                    </tr>
                @endif
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
    
</div>
    
<div class="adminFootBuff"></div>

@endsection