@if (intVal($tblID) <= 0)
    <xs:element name="{{ $tbl }}" minOccurs="0">
        <xs:complexType mixed="true">
            <xs:sequence>
                {!! $kids !!}
            </xs:sequence>
        </xs:complexType>
    </xs:element>
@else
    @if ($TblOpts%11 > 0)
        <xs:element name="{{ $tbl }}" minOccurs="0" >
            <xs:complexType mixed="true">
                <xs:sequence>
    @endif
    @if ($TblOpts%5 > 0)
        <xs:element name="{{ $tblAbbr }}" @if ($TblOpts%7 > 0) minOccurs="0" @endif @if ($TblOpts%11 > 0) maxOccurs="unbounded" @endif >
            <xs:complexType mixed="true">
                <xs:sequence>
    @endif
    @forelse ($tblFlds as $i => $fld)
        @if ($fld->FldOpts%13 > 0)
            <xs:element name="{{ $tblAbbr . $fld->FldName }}" 
                @if (!$tblFldEnum[$fld->FldID]) type="{{ $GLOBALS['SL']->fld2SchemaType($fld) }}" @endif
                @if (!isset($fld->FldRequired) || intVal($fld->FldRequired) == 0) minOccurs="0" @endif >
                <xs:annotation>
                    <xs:appinfo>{{ htmlspecialchars($fld->FldEng) }}</xs:appinfo>
                    <xs:documentation xml:lang="en"><![CDATA[{{ htmlspecialchars($fld->FldDesc) }}]]></xs:documentation>
                </xs:annotation>
                @if ($tblFldEnum[$fld->FldID])
                    <xs:simpleType>
                        <xs:restriction base="xs:string">
                            @foreach ($tblFldDefs[$fld->FldID] as $i => $def)
                                <xs:enumeration value="{{ htmlspecialchars($def) }}"/>
                            @endforeach
                        </xs:restriction>
                    </xs:simpleType>
                @endif
            </xs:element>
        @endif
    @empty
    @endforelse
    
    {!! $kids !!}
    
    @if ($TblOpts%5 > 0)
                </xs:sequence>
                <xs:attribute name="id" type="xs:integer"/>
            </xs:complexType>
        </xs:element>
    @endif
    @if ($TblOpts%11 > 0)
                </xs:sequence>
            </xs:complexType>
        </xs:element>
    @endif
@endif