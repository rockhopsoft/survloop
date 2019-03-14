<!-- resources/views/vendor/survloop/admin/tree/conditions.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="slCard nodeWrap">
<h2 class="slGreenDark"><i class="fa fa-filter" aria-hidden="true"></i> Conditions / Filters</h2>
<table class="table table-striped w100">
@forelse ($condSplits as $i => $cond)
    <input type="hidden" name="CondID{{ $i }}" value="{{ $cond->CondID }}">
    <tr><td class="w100">
        <div class="row">
            <div class="col-6">
                <h4 class="m0">{{ $cond->CondTag }}</h4>
                {{ $cond->CondDesc }}
            </div>
            <div class="col-5">
                <div class="mB5">{!! view('vendor.survloop.admin.db.inc-describeCondition', [
                    "cond" => $cond, 
                    "i" => $i 
                ])->render() !!}</div>
                @if ($cond->CondOpts%2 == 0) For public use @endif
                @if (isset($condArticles[$cond->CondID]))
                    , <span class="mL10 mR5">Related articles:</span>
                    @foreach ($condArticles[$cond->CondID] as $j => $art)
                        @if ($j > 0) , @endif <a href="{{ $art[1] }}" target="_blank" class="mL5 mR5">
                        @if (strpos($art[1], 'youtube.com') !== false)
                            <i class="fa fa-youtube-play" aria-hidden="true"></i>
                        @endif {{ $art[0] }}</a>
                    @endforeach
                @endif
            </div>
            <div class="col-1">
                <a href="/dashboard/db/conds/edit/{{ $cond->CondID }}" class="fPerc133 mL5 mR5"
                    ><i class="fa fa-pencil" aria-hidden="true"></i></a><br />
                <a href="javascript:;" id="condDelBtn{{ $i }}" class="condDelBtn txtDanger fPerc133 mT20 mL5 mR5"
                    ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                <div id="condDel{{ $i }}" class="disNon red fPerc80">
                    <label>
                        <input type="checkbox" name="CondDelete{{ $i }}" value="1" autocomplete="off"> Yes, Delete
                    </label>
                </div>
            </div>
        </div>
    </td></tr>
@empty
@endforelse
</table>
</div></div>
<div class="adminFootBuff"></div>
@endsection