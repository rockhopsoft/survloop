
<xs:element name="{{ $tblPrefix . $label }}" @if (sizeof($enums) == 0) type="{{ $elemType }}" @endif >
	<xs:annotation>
		<xs:appinfo>{!! $labelEng !!}</xs:appinfo>
		<xs:documentation xml:lang="en">
			{!! $desc !!}
		</xs:documentation>
	</xs:annotation>
@if (sizeof($enums) > 0)
	<xs:simpleType>
		<xs:restriction base="{{ $GLOBALS['SL']->fld2SchemaEnumsType($enums) }}">
		@foreach ($enums as $enum) 
			<xs:enumeration value="{{ $enum }}"/>
		@endforeach
		</xs:restriction>
	</xs:simpleType>
@endif
</xs:element>
