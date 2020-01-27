<!-- resources/views/vendor/survloop/admin/db/full-innerTable.blade.php -->

@forelse ($groupTbls as $group => $tbls)
    
    @if ($isExcel)
        <tr><td colspan=5 ></td></tr>
        <tr><td colspan=5 class="p5 pL20 slGrey">Group: <b>{{ $group }}...</b></td></tr>
        <tr><td colspan=5 ></td></tr>
    @endif
    
    @forelse ($tbls as $tbl)
        
        @if ($isExcel)
            {!! $basicTblDescs[$tbl->tbl_id] !!}
            {!! $basicTblFlds !!}
            <tr><td></td></tr>
        @else 
            <div class="nodeAnchor"><a name="tbl{{ $tbl->tbl_id }}"></a></div>
            <div class="pT20"></div>
            <div class="card">
                <div class="card-header">
                    
                    <h6 class="m0 float-right taR">{{ $tbl->tbl_name }}<br />
                        ({{ $tbl->tbl_abbr }})</h6>
                    @if ($tbl->tbl_eng != 'Users')
                        <a href="/dashboard/db/table/{{ $tbl->tbl_name }}"
                            ><h2 class="m0">{{ $tbl->tbl_eng }}</h2></a>
                    @else
                        <h2 class="m0">{{ $tbl->tbl_eng }}</h2>
                    @endif
                    <h5 class="mT5">{!! $tbl->tbl_desc !!}</h5>
                    
                </div>
                <div class="card-body">
                    
                    @if ($tbl->tbl_num_foreign_in > 0)
                        @if (isset($tblForeigns[$tbl->tbl_id]))
                            <div class="float-right taR">
                                <i>Incoming:</i> {!! $tblForeigns[$tbl->tbl_id] !!}
                            </div>
                        @endif
                        <i class="fa fa-link"></i> {{ $tbl->tbl_num_foreign_in }} 
                            @if ($tbl->tbl_num_foreign_in != 1) Tables @else Table @endif
                            with Foreign Key{{ (($tbl->tbl_num_foreign_in != 1) ? 's' : '') }}
                    @endif
                    @if (isset($tblRules[$tbl->tbl_id]))
                        @forelse ($tblRules[$tbl->tbl_id] as $rule)
                            <a href="/dashboard/db/bus-rules/edit/{{ $rule->rule_id }}" 
                                target="_blank" class="slGrey"><i class="fa fa-university"></i> 
                                {{ $rule->RuleStatement }}</a>
                        @empty
                        @endforelse
                    @endif
                
                    @if ($tbl->tbl_eng != 'Users')
                        <div class="label label-primary">Group: {{ $group }}</div>
                        <div class="label label-primary">Type: {{ $tbl->tbl_type }}</div>
                        <div class="label label-primary">
                            {{ $tbl->tbl_num_fields }} Fields Total
                        </div>
                        @if ($tbl->tbl_num_foreign_keys > 0)
                            {{ $tbl->tbl_num_foreign_keys }} Outgoing 
                            @if ($tbl->tbl_num_foreign_keys == 1) Key @else Keys @endif 
                        @endif
                    @endif
                    {!! $basicTblFlds[$tbl->tbl_id] !!}
                </div>
            </div>
        @endif
        
    @empty

    @endforelse
    
@empty

@endforelse

@if ($isExcel)
    <tr><td colspan=5 style="border-top: 1px #999 solid;"><br /><br /></td></tr>
@endif
