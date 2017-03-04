<!-- Stored in resources/views/vendor/survloop/admin/submissions-list.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>
    <i class="fa fa-star"></i> 
    @if ($currPage == '/dashboard/subs/incomplete')
        All Incomplete Submissions
    @else
        All Completed Submissions
    @endif
</h1>

<div class="p5"></div>

<table class="table table-striped">
<tr>
    <th><i>ID#</i></th>
    <th>Date</th>
    @foreach ($coreFlds as $i => $fld)
        <th>{{ $fld->FldName }}</th>
    @endforeach
</tr>
@forelse ($subsList as $sub)
    <tr>
        <td>
            <a href="/dashboard/subs/{{ $com->ComID }}/review" 
                class="btn btn btn-default round20 p5 f22 slBlueDark"
                >#{{ number_format( $sub->{ $coreAbbr.'ID' } ) }}</a>
        </td>
        <td>
            {{ date('n/j/y', strtotime( $sub->created_at )) }}
        </td>
        @foreach ($coreFlds as $i => $fld)
            <td>
                @if ($fld->FldForeignTable > 0)
                    {{ $GLOBALS['SL']->tbl[$fld->FldForeignTable] }} #
                @endif
                {{ $sub->{ $coreAbbr.$fld->FldName } }}
            </td>
        @endforeach
    </tr>
@empty
    <tr><td colspan=6 ><i>No experience submissions found in this filter</i></td></tr>
@endforelse
</table>


<div class="adminFootBuff"></div>

@endsection