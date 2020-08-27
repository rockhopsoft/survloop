<!-- resources/views/vendor/survloop/admin/systems-update.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
    <h2><i class="fa fa-heartbeat"></i> System Updates</h2>
    <div class="row">
        <div class="col-6">
            <div class="slCard nodeWrap">
            @if (isset($msgs) && trim($msgs) != '')
                <div class="jumbotron">{!! $msgs !!}</div>
            @endif
            @if (isset($needUpdate) && $needUpdate)
                <h3 class="slBlueDark">The database needs to be backed up before running updates...</h3>
                <a href="?sub=1" class="btn btn-lg btn-primary m20">&nbsp;&nbsp;Install Updates&nbsp;&nbsp;</a>
            @else
                <h3 class="slGrey"><i>There are no new updates needing installation.</i></h3>
            @endif
            </div>
        </div>
        <div class="col-6">
            <div class="slCard nodeWrap">
                All System Updates:<br /><br />
                @forelse ($updateList as $i => $up)
                    <div class="p20 @if ($i%2 == 0) row2 @endif @if ($up[1]) slBlueDark @else txtDanger @endif ">
                        <div class="row">
                            <div class="col-6"><b>{!! $up[0] !!}</b></div>
                            <div class="col-6">
                                @if ($up[1]) <nobr><i class="fa fa-check slGreenDark"></i> Installed</nobr> @else Needed @endif
                            </div>
                        </div>
                        {!! $up[2] !!}
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection