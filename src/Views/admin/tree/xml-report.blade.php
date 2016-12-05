<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>

<!--
All specifications for database designs and user experience (form tree map) 
are made available by {{ $GLOBALS['DB']->sysOpts['site-name'] }} under the 
Creative Commons Attribution-ShareAlike License, 2016.

{{ $GLOBALS['DB']->sysOpts['logo-url'] }}

https://creativecommons.org/licenses/by-sa/3.0/
    
XML generated from the SurvLoop engine, built on Laravel,
resources/views/survloop/admin/tree/xml-schema.blade.php
-->

@if (isset($nestedNodes)) {!! $nestedNodes !!} @endif
