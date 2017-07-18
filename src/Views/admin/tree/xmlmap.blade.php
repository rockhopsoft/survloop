<!-- resources/views/vendor/survloop/admin/tree/xmlmap.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<ul class="nav nav-tabs mT10" role="tablist">
    <li role="presentation">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/map?all=1">Full Map of Tree</a></li>
    <li role="presentation">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/data">Tree Data Structures</a></li>
    <li role="presentation" class="active">
        <a href="/dashboard/tree-{{ $GLOBALS['SL']->treeID }}/xmlmap">Data Export XML Map</a></li>
</ul>

<h2 class="slBlueDark"><i class="fa fa-snowflake-o"></i> Form Tree: {{ $GLOBALS['SL']->treeName }}</h2>
    
<div class="row mTn20">
    <div class="col-md-4">
        <h3>Data Table XML Map</h3>
        <a href="javascript:void:;" id="editXmlMap" class="f14"
            ><i class="fa fa-pencil" aria-hidden="true"></i> Edit Map</a></nobr>
        <style> .editXml { display: none; } </style>
    </div>
    <div class="col-md-4">
        <h3>XML Schema</h3>
        <a href="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-schema" target="_blank"
            ><i class="fa fa-file-code-o" aria-hidden="true"></i> 
            /{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-schema</a>
    </div>
    <div class="col-md-4">
        <h3>XML Example</h3>
        <a href="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-example" target="_blank"
            ><i class="fa fa-file-code-o" aria-hidden="true"></i> 
            /{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-example</a>
    </div>
</div>
<div class="row">
    <div class="col-md-4" id="xmlFullTree">
        {!! $adminPrintFullTree !!}
    </div>
    <div class="col-md-4">
        <iframe src="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-schema" class="w100" style="height: 1000px;"></iframe>
    </div>
    <div class="col-md-4">
        <iframe src="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-example" class="w100" style="height: 1000px;"></iframe>
    </div>
</div>

<div class="p20"></div><div class="p20"></div>

@endsection