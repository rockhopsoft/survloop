<!-- resources/views/vendor/survloop/admin/tree/xmlmap.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<div class="row pT20 pB20">
    <div class="col-md-4">
        <h2><i class="fa fa-snowflake-o"></i> Data Table XML Map 
        <a href="javascript:void:;" id="editXmlMap" class="f14"><i class="fa fa-pencil" aria-hidden="true"></i></a></nobr></h2>
        <style> .editXml { display: none; } </style>
        <script type="text/javascript"> $(function() { $(document).on("click", "#editXmlMap", function() { $(".editXml").css('display',"inline"); }); }); </script>
    </div>
    <div class="col-md-4">
        <a href="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-schema" target="_blank"
            ><h2><i class="fa fa-file-code-o" aria-hidden="true"></i> XML Schema</h2></a>
    </div>
    <div class="col-md-4">
        <a href="/{{ $GLOBALS['SL']->treeRow->TreeSlug }}-xml-example" target="_blank"
            ><h2><i class="fa fa-file-code-o" aria-hidden="true"></i> XML Example</h2></a>
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