<!-- resources/views/vendor/survloop/admin/submissions-list.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="slCard nodeWrap">
<h1><i class="fa fa-star"></i> {{ $currPageTitle }}</h1>

<div class="p5"></div>

<table class="table table-striped">
<tr>
    <th><i>ID#</i></th>
    <th>Date</th>
    @foreach ($coreFlds as $i => $fld)
        <th>{{ $fld->fld_name }}</th>
    @endforeach
</tr>
@forelse ($subsList as $sub)
    <tr>
        <td>
            <a href="/dashboard/subs/{{ $sub->getKey() }}/review" 
                class="btn btn-lg btn-secondary slBlueDark"
                >#{{ number_format( $sub->getKey() ) }}</a>
        </td>
        <td>
            {{ date('n/j/y', strtotime( $sub->created_at )) }}
        </td>
        @foreach ($coreFlds as $i => $fld)
            <td>
                @if ($fld->fld_foreign_table > 0)
                    {{ $GLOBALS['SL']->tbl[$fld->fld_foreign_table] }} #
                @endif
                {{ $sub->{ $coreAbbr.$fld->fld_name } }}
            </td>
        @endforeach
    </tr>
@empty
    <tr><td colspan=6 ><i>No submissions found in this filter</i></td></tr>
@endforelse
</table>

</div>

</div>
@endsection