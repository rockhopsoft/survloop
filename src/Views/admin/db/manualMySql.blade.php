<!-- resources/views/vendor/survloop/admin/db/manualMySql.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h2>Access Granted!</h2>

@if (isset($lastSql) && trim($lastSql) != '')
	<div class="brd round20 p20">
	    <h2 class="mT0">Full Query...</h2>
	    <textarea class="w100 gry9 f10" style="height: 80px;">{!! $lastSql !!}</textarea>
	    <h2>Query Results...</h2>
	    @forelse ($lastResults as $sql)
	        @if (trim($sql[1]) == '')
	            <h3 class="m0 slGreenDark">Success</h3>
	        @else
	            <h3 class="m0 red">Fail - {!! $sql[1] !!}</h3>
	        @endif
	        <textarea class="w100 f12 gry6" style="height: 50px;"
	            >{!! str_replace('</textarea>', '(end text area)', $sql[0]) !!}</textarea>
	        @if (trim($sql[2]) != '')
                <textarea class="w100 f10" style="height: 100px;"
                    >{!! str_replace('</textarea>', '(end text area)', $sql[2]) !!}</textarea>
                <div class="p20"></div>
            @endif
	    @empty
	    @endforelse
	</div>
@endif

<div class="p20"></div>

<form name="runMysql" method="post" action="/dashboard/db/db/db">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<textarea name="mys" class="w100 f12" style="height: 250px;"></textarea>

<input type="submit" class="btn btn-primary mT20" value="RUN MYSQL">

</form>


@endsection
