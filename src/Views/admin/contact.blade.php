<!-- resources/views/vendor/survloop/admin/contact.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
    <h2><i class="fa fa-envelope-o mR15"></i> {{ $currPage[1] }}</h2>
    @if ($recs && $recs->isNotEmpty())
        @foreach ($recs as $contact)
            <div id="wrapItem{{ $contact->cont_id }}" class="disBlo">
                <div class="pT30 pB30 brdBot">
                    <div class="row mB30">
                        {!! view(
                            'vendor.survloop.admin.contact-row', 
                            [ "contact" => $contact ]
                        )->render() !!}
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="p20">Nothing found in this filter.</div>
    @endif
</div>
<div class="adminFootBuff"></div>
@endsection