<!-- resources/views/survloop/admin/dashboard.blade.php -->
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
    <h3>{{ $GLOBALS['SL']->dbRow->db_name }} Mission</h3>
    <p>{!! $orgMission !!}</p>
</div>

</div> <!-- end dashFullAdmMenu -->

@endsection