@if (intVal($tblID) <= 0)
    <xs:element name="{{ $tbl }}">
        <xs:complexType mixed="true">
            <xs:sequence>
                {!! $kids !!}
            </xs:sequence>
        </xs:complexType>
    </xs:element>
@else
    @if ($tblOpts%11 > 0)
        <xs:element name="{{ $tbl }}">
            <xs:complexType mixed="true">
                <xs:sequence>
    @endif
    @if ($tblOpts%5 > 0)
        <xs:element name="{{ $tblAbbr }}">
            <xs:complexType mixed="true">
                <xs:sequence>
    @endif
    
    @forelse ($tblFlds as $i => $fld)
        @if ($fld->fld_opts%13 > 0)
            <xs:element name="{{ $tblAbbr . $fld->fld_name }}" 
                @if (!$tblFldEnum[$fld->fld_id]) 
                    type="{{ $GLOBALS['SL']->fld2SchemaType($fld) }}" 
                @endif >
                <xs:annotation>
                    <xs:appinfo>{{ htmlspecialchars($fld->fld_eng) }}</xs:appinfo>
                    <xs:documentation xml:lang="en"><![CDATA[{{ 
                        htmlspecialchars($fld->fld_desc) }}]]></xs:documentation>
                </xs:annotation>
                @if ($tblFldEnum[$fld->fld_id])
                    <xs:simpleType>
                        <xs:restriction base="xs:string">
                            @foreach ($tblFldDefs[$fld->fld_id] as $i => $def)
                                <xs:enumeration value="{{ 
                                    htmlspecialchars($def) }}"/>
                            @endforeach
                        </xs:restriction>
                    </xs:simpleType>
                @endif
            </xs:element>
        @endif
    @empty
    @endforelse
    
    {!! $kids !!}
    
    @if ($tblOpts%5 > 0)
                </xs:sequence>
                <xs:attribute name="id" type="xs:integer"/>
            </xs:complexType>
        </xs:element>
    @endif
    @if ($tblOpts%11 > 0)
                </xs:sequence>
            </xs:complexType>
        </xs:element>
    @endif
@endif