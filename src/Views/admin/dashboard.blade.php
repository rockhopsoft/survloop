<!-- Stored in resources/views/survloop/admin/dashboard.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<div class="row">
	<div class="col-md-4">
		<h1 class="page-header">Dashboard</h1>
		<i>Overview of recent activity and statistics coming soon!</i>
		<br /><br />
		<div class="f18 mB20">For now, the most interesting things in this admin area are...</div>
		<ul>
		<li class="mB20"><a href="/dashboard/subs" class="f22">New Submissions</a></li>
		<li class="mB20"><a href="/dashboard/tree/map?all=1" class="f22">Map of Entire Complaint Submission Process</a></li>
		<li class="mB20"><a href="/dashboard/db/all" class="f22">Entire Database Design</a></li>
		</ul>
	</div>
	<div class="col-md-8">
		<div class="jumbotron taC">
			<h2>{{ $GLOBALS["DB"]->dbRow->DbName }} Mission</h2>
			<p class="pB20">{!! $orgMission !!}</p>
		</div>
	</div>
</div>

<h1>Full Admin Menu...</h1>
<div class="row">
	@forelse ($adminNav as $i => $nav)
		@if ($i > 0)
			<div class="col-md-2 pB20">
				<b class="f20">{!! str_replace('pull-right', 'pull-left mR5', $nav[1]) !!}</b>
				<ul>
				@if (sizeof($nav[3]) > 0)
					@foreach ($nav[3] as $link)
						<li><a href="{{ $link[0] }}" class="f16">{!! $link[1] !!}</a></li>
					@endforeach
				@endif
				</ul>
			</div>
		@endif
	@empty
	@endforelse
</div>


<?php /*

<div class="row">
	<div class="col-md-6">
		<h1 class="page-header">Dashboard</h1>
		<div class="row placeholders">
			<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
			</div>
			<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
			</div>
			<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
			</div>
			<div class="col-xs-6 col-sm-3 placeholder">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-responsive" alt="Generic placeholder thumbnail">
				<h4>Label</h4>
				<span class="text-muted">Something else</span>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="jumbotron taC">
			<h2>Mission</h2>
			<p>{!! $orgMission !!}</p>
		</div>
	</div>
</div>

<h2 class="sub-header">Section title</h2>
<div class="table-responsive">
<table class="table table-striped">
<thead>
<tr>
<th>#</th>
<th>Header</th>
<th>Header</th>
<th>Header</th>
<th>Header</th>
</tr>
</thead>
<tbody>
<tr>
<td>1,001</td>
<td>Lorem</td>
<td>ipsum</td>
<td>dolor</td>
<td>sit</td>
</tr>
<tr>
<td>1,002</td>
<td>amet</td>
<td>consectetur</td>
<td>adipiscing</td>
<td>elit</td>
</tr>
<tr>
<td>1,003</td>
<td>Integer</td>
<td>nec</td>
<td>odio</td>
<td>Praesent</td>
</tr>
<tr>
<td>1,003</td>
<td>libero</td>
<td>Sed</td>
<td>cursus</td>
<td>ante</td>
</tr>
<tr>
<td>1,004</td>
<td>dapibus</td>
<td>diam</td>
<td>Sed</td>
<td>nisi</td>
</tr>
<tr>
<td>1,005</td>
<td>Nulla</td>
<td>quis</td>
<td>sem</td>
<td>at</td>
</tr>
<tr>
<td>1,006</td>
<td>nibh</td>
<td>elementum</td>
<td>imperdiet</td>
<td>Duis</td>
</tr>
<tr>
<td>1,007</td>
<td>sagittis</td>
<td>ipsum</td>
<td>Praesent</td>
<td>mauris</td>
</tr>
<tr>
<td>1,008</td>
<td>Fusce</td>
<td>nec</td>
<td>tellus</td>
<td>sed</td>
</tr>
<tr>
<td>1,009</td>
<td>augue</td>
<td>semper</td>
<td>porta</td>
<td>Mauris</td>
</tr>
<tr>
<td>1,010</td>
<td>massa</td>
<td>Vestibulum</td>
<td>lacinia</td>
<td>arcu</td>
</tr>
<tr>
<td>1,011</td>
<td>eget</td>
<td>nulla</td>
<td>Class</td>
<td>aptent</td>
</tr>
<tr>
<td>1,012</td>
<td>taciti</td>
<td>sociosqu</td>
<td>ad</td>
<td>litora</td>
</tr>
<tr>
<td>1,013</td>
<td>torquent</td>
<td>per</td>
<td>conubia</td>
<td>nostra</td>
</tr>
<tr>
<td>1,014</td>
<td>per</td>
<td>inceptos</td>
<td>himenaeos</td>
<td>Curabitur</td>
</tr>
<tr>
<td>1,015</td>
<td>sodales</td>
<td>ligula</td>
<td>in</td>
<td>libero</td>
</tr>
</tbody>
</table>
</div>

*/ ?>

@endsection