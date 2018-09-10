<!-- Stored in resources/views/survloop/admin/dashboard.blade.php -->

@extends('vendor.survloop.master')

@section('content')

@if (isset($dashpage) && trim($dashpage) != '') {!! $dashpage !!} @endif

<div id="dashFullAdmMenu" class="disBlo">
<hr>
<h2 class="mT20 mB0">Full Admin Menu</h3>
@for ($i=0; $i < sizeof($adminNav); $i++)
    <div class="fL pR20">
        <h3>{!! str_replace('float-right', 'float-left mR5', $adminNav[$i][1]) !!}</h3>
        @if (sizeof($adminNav[$i][4]) > 0)
            <ul class="m0">
            @foreach ($adminNav[$i][4] as $link)
                <li><a href="{{ $link[0] }}">{!! $link[1] !!}</a>
                    @if (sizeof($link[4]) > 0)
                        <ul>
                        @foreach ($link[4] as $link2)
                            <li><a href="{{ $link2[0] }}">{!! $link2[1] !!}</a></li>
                        @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
            </ul>
        @endif
    </div>
@endfor
<div class="fC"></div>
        
<div class="jumbotron mT20">
    <h3>{{ $GLOBALS['SL']->dbRow->DbName }} Mission</h3>
    <p>{!! $orgMission !!}</p>
</div>

</div> <!-- end dashFullAdmMenu -->


<?php /*

<div class="row">
    <div class="col-md-6">
        <h1 class="page-header">Dashboard</h1>
        <div class="row placeholders">
            <div class="col-xs-6 col-sm-3 placeholder">
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-fluid" alt="Generic placeholder thumbnail">
                <h4>Label</h4>
                <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-fluid" alt="Generic placeholder thumbnail">
                <h4>Label</h4>
                <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-fluid" alt="Generic placeholder thumbnail">
                <h4>Label</h4>
                <span class="text-muted">Something else</span>
            </div>
            <div class="col-xs-6 col-sm-3 placeholder">
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" width="200" height="200" class="img-fluid" alt="Generic placeholder thumbnail">
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
<table class="table table-striped table-responsive">
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

*/ ?>

@endsection