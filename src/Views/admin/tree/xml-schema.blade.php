<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!--
With an API, the exact structure of request and response is documented upfront by the related XML Schema (schematic). 
This computer-friendly output and is likely to remain constant, regardless of whether the website changes its 
look and feel for human visitors. This document defines the schematic by which each report can be understood.

All specifications for database designs and user experience (form tree map) made available by 
{{ $GLOBALS['SL']->sysOpts['site-name'] }} under the Creative Commons Attribution-ShareAlike License, {{ date("Y") }}.
https://creativecommons.org/licenses/by-sa/3.0/

{{ $GLOBALS['SL']->sysOpts['logo-url'] }}

XML Example: {{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->tree_slug }}-xml-example

This XML was auto-generated from the Survloop open data engine,
resources/views/survloop/admin/tree/xml-schema.blade.php
-->

<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    targetNamespace="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->tree_slug }}-xml-schema"
    xmlns="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->tree_slug }}-xml-schema"
    elementFormDefault="qualified" >
    
    @if (isset($nestedNodes)) {!! $nestedNodes !!} @endif
    
    <?php /*
    <!-- Start of definition of some re-used elements -->
    
    <xs:element name="state" type="xs:string">
        <xs:simpleType>
            <xs:restriction base="xs:string">
                <xs:enumeration value="AL"/>
                <xs:enumeration value="AK"/>
            </xs:restriction>
        </xs:simpleType>
    </xs:element>
    */ ?>    
</xs:schema>