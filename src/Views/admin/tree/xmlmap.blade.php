<!-- resources/views/vendor/survloop/admin/tree/xmlmap.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>
    <span class="slBlueDark"><i class="fa fa-snowflake-o"></i>
    {{ $GLOBALS['SL']->treeName }}:</span> XML Map
</h2>
    
<div class="row mT30">
    <div class="col-6">
        <h3>XML Schema</h3>
        <a href="/{{ $GLOBALS['SL']->xmlTree['slug'] }}-schema-xml" target="_blank"
            ><i class="fa fa-file-code-o" aria-hidden="true"></i> 
            /{{ $GLOBALS['SL']->xmlTree['slug'] }}-schema-xml</a>
    </div>
    <div class="col-6">
        <h3>XML Example</h3>
        <a href="/{{ $GLOBALS['SL']->xmlTree['slug'] }}-example-xml" target="_blank"
            ><i class="fa fa-file-code-o" aria-hidden="true"></i> 
            /{{ $GLOBALS['SL']->xmlTree['slug'] }}-example-xml</a>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <iframe src="/{{ $GLOBALS['SL']->xmlTree['slug'] }}-schema-xml" 
            class="w100" style="height: 1000px;"></iframe>
    </div>
    <div class="col-6">
        <iframe src="/{{ $GLOBALS['SL']->xmlTree['slug'] }}-example-xml" 
            class="w100" style="height: 1000px;"></iframe>
    </div>
</div>

<div class="p20"></div>

<div class="row">
    <div class="col-6">
        <h3>Data Table XML Map</h3>
        <a href="javascript:;" id="editXmlMap"
            ><i class="fa fa-pencil" aria-hidden="true"></i> Edit Map</a>
        <div id="xmlFullTree">
            {!! $adminPrintFullTree !!}
        </div>
    </div>
</div>
<style> .editXml { display: none; } </style>

<div class="p20"></div>

@endsection