{!! view(
    'vendor.survloop.admin.tree.xml-header-custom',
    [ "apiDesc" => $apiDesc ]
)->render() !!}
<xs:schema namespace="{{ $apiNamespace }}" xmlns="{{ $apiNamespace }}" xmlns:xs="{{ $apiSchema }}" elementFormDefault="qualified" >
    <xs:element name="{{ $corePlural }}">
        <xs:complexType mixed="true">
            <xs:sequence>
                <xs:element name="{{ $coreSingle }}">
                    <xs:complexType mixed="true">
                        <xs:sequence>
                        @forelse ($apiTables as $i => $apiTbl)
                            @if ($i == 0 && $apiTbl->table == $corePlural)
                                {!! $apiTbl->printFlds($type) !!}
                            @else
                                <xs:element name="{{ $apiTbl->table }}">
                                    <xs:complexType mixed="true">
                                        <xs:sequence>
                                        @if (!$apiTbl->inline)
                                            <xs:element name="{{ $apiTbl->singular }}">
                                                <xs:complexType mixed="true">
                                                    <xs:sequence>
                                                        {!! $apiTbl->printFlds($type) !!}
                                                    </xs:sequence>
                                                </xs:complexType>
                                            </xs:element>
                                        @else 
                                            {!! $apiTbl->printFlds($type) !!}
                                        @endif
                                        </xs:sequence>
                                    </xs:complexType>
                                </xs:element>
                            @endif
                        @empty
                        @endforelse
                        </xs:sequence>
                        <xs:attribute name="id" type="xs:string"/>
                    </xs:complexType>
                </xs:element>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>