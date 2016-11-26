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

<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
	targetNamespace="{{ $GLOBALS['DB']->sysOpts['app-url'] }}/xml-schema"
	xmlns="{{ $GLOBALS['DB']->sysOpts['app-url'] }}/xml-schema"
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