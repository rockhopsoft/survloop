<!-- resources/views/vendor/survloop/admin/contact.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<h2><i class="fa fa-envelope-o"></i> {{ $currPage[1] }}</h2>
<?php /* <ul id="pageTabs" class="nav nav-tabs">
{!! view('vendor.survloop.admin.contact-tabs', [ "filtStatus" => $filtStatus, "recTots" => $recTots ])->render() !!}
</ul> */ ?>
@if ($recs->isNotEmpty())
    @foreach ($recs as $contact)
        <div class="slCard nodeWrap"><div id="wrapItem{{ $contact->ContID }}" class="row">
            {!! view('vendor.survloop.admin.contact-row', [ "contact" => $contact ])->render() !!}
        </div></div>
    @endforeach
@else
    <div class="p20">Nothing found in this filter.</div>
@endif
</div>
<div class="adminFootBuff"></div>
@endsection