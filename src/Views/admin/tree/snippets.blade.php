<!-- Stored in resources/views/vender/survloop/admin/tree/snippets.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="row">
    <div class="col-7">
        
        <h2><i class="fa fa-newspaper-o"></i> Content Snippets</h2>
        <div class="slGrey pB10">
            Snippets are little chunks of content for your site. They can be small or large, control the website's
            footer, are great for storing instructions which need to appear the same way in multiple places throughout 
            the system. These snippets can be included in any node via "<?= '{'.'{Snippet Name}'.'}' ?>".
        </div>
        <table class="table table-striped">
        @forelse($blurbRows as $blurb)
            <tr><td class="fPerc133">{{ $blurb->DefSubset }}</td><td>
                <a href="/dashboard/pages/snippets/{{ $blurb->DefID }}" class="btn btn-primary"
                    ><i class="fa fa-pencil" aria-hidden="true"></i></a>
            </td></tr>
        @empty
            <tr><td><i>No snippets found.</i></td></tr>
        @endforelse
        </table>
        
    </div>
    <div class="col-1"></div>
    <div class="col-4">
        
        <div class="nodeAnchor"><a id="new" name="new"></a></div>
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="sub" value="1">
        <div id="newSnipForm" class="row2 p20 mT20 mB20">
            <a id="hidivBtnNewSnip" class="hidivBtn" href="javascript:;"
                ><h3 class="m0"><i class="fa fa-plus mR5" aria-hidden="true"></i> Create New Snippet</h3></a>
            <div id="hidivNewSnip" class="disNon mT20">
                <form name="newBlurbForm" method="post" action="/dashboard/pages/snippets">
                <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="sublurb" value="1">
                <hr>
                <label for="newSnipNameID" class="mT0"><b>New Snippet Name:</b></label>
                <input type="text" name="newBlurbName" id="newBlurbNameID" class="form-control" value="">
                <div class="p10"></div>
                <input type="submit" class="btn btn-lg btn-primary" value="Create Snippet">
                </form>
            </div>
        </div>
        <div class="p10"></div>
        
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection