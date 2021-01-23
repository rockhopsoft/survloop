<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!--
@if (isset($tot) && intVal($tot) > 0)
{{ number_format($tot) }} Total Records Found
@endif
@if (isset($apiLoadLinks) && trim($apiLoadLinks) != '')
{!! $apiLoadLinks !!}
@else
{{ $GLOBALS['SL']->sysOpts['logo-url'] }}
@endif

XML Schema: {{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->tree_slug }}-xml-schema

With an API, the exact structure of request and response is documented upfront 
by the related XML Schema (schematic). This computer-friendly output and is 
likely to remain constant, regardless of whether the website changes its 
look and feel for human visitors. The structure of this report is 
defined by the XML Schema linked below.

All specifications for database designs and user experience (form tree map) 
made available by {{ $GLOBALS['SL']->sysOpts['site-name'] }} under the 
Creative Commons Attribution-ShareAlike License, {{ date("Y") }}.
https://creativecommons.org/licenses/by-sa/3.0/

This XML was auto-generated from the Survloop open data engine,
resources/views/survloop/admin/tree/xml-schema.blade.php
-->

<{{ $GLOBALS["SL"]->coreTbl }} xmlns="{{ $GLOBALS['SL']->sysOpts['app-url'] 
    }}/{{ $GLOBALS['SL']->treeRow->tree_slug }}-xml-schema">
@if (isset($nestedNodes)) {!! $nestedNodes !!} @endif
</{{ $GLOBALS["SL"]->coreTbl }}>
