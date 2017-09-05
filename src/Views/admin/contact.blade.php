<!-- Stored in resources/views/vendor/survloop/admin/contact.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2><i class="fa fa-envelope-o"></i> {{ $currPageTitle }}</h2>

<div class="p5"></div>

<ul id="pageTabs" class="nav nav-tabs">
{!! view('vendor.survloop.admin.contact-tabs', [ "filtStatus" => $filtStatus, "recTots" => $recTots ])->render() !!}
</ul>
<div id="myTabContent" class="tab-content">
    <table class="table table-striped">
    @if (isset($recs) && sizeof($recs) > 0)
        @foreach ($recs as $contact)
            <tr><td class="pB20"><div id="wrapItem{{ $contact->ContID }}" class="row">
                {!! view('vendor.survloop.admin.contact-row', [ "contact" => $contact ])->render() !!}
            </div></td></tr>
        @endforeach
    @else
        <tr><td><div class="p20"><i>Nothing found in this filter.</i></div></td></tr>
    @endif
    </table>
</div>

<div class="adminFootBuff"></div>
@endsection