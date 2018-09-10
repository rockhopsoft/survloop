<!-- resources/views/vendor/survloop/admin/db/full-innerTable.blade.php -->

@forelse ($groupTbls as $group => $tbls)
    
    @if ($isExcel)
        <tr><td colspan=5 ></td></tr>
        <tr><td colspan=5 class="p5 pL20 f18 gry6">Group: <b>{{ $group }}...</b></td></tr>
        <tr><td colspan=5 ></td></tr>
    @endif
    
    @forelse ($tbls as $tbl)
        
        @if ($isExcel)
            {!! $basicTblDescs[$tbl->TblID] !!}
            {!! $basicTblFlds !!}
            <tr><td></td></tr>
        @else 
            <a name="tbl{{ $tbl->TblID }}"></a>
            <div class="card">
                <div class="card-header">
                    
                    <h6 class="m0 float-right taR">{{ $tbl->TblName }}<br />({{ $tbl->TblAbbr }})</h6>
                    @if ($tbl->TblEng != 'Users')
                        <a href="/dashboard/db/table/{{ $tbl->TblName }}"><h2 class="m0">{{ $tbl->TblEng }}</h2></a>
                    @else
                        <h2 class="m0">{{ $tbl->TblEng }}</h2>
                    @endif
                    <h5 class="mT5">{!! $tbl->TblDesc !!}</h5>
                    
                </div>
                <div class="card-body">
                    
                    @if ($tbl->TblNumForeignIn > 0)
                        @if (isset($tblForeigns[$tbl->TblID]))
                            <div class="float-right taR"><i>Incoming:</i> {!! $tblForeigns[$tbl->TblID] !!}</div>
                        @endif
                        <i class="fa fa-link"></i> {{ $tbl->TblNumForeignIn }} 
                            @if ($tbl->TblNumForeignIn != 1) Tables @else Table @endif
                            with Foreign Key{{ (($tbl->TblNumForeignIn != 1) ? 's' : '') }}
                    @endif
                    @if (isset($tblRules[$tbl->TblID]))
                        @forelse ($tblRules[$tbl->TblID] as $rule)
                            <a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" target="_blank" 
                                class="slGrey"><i class="fa fa-university"></i> {{ $rule->RuleStatement }}</a>
                        @empty
                        @endforelse
                    @endif
                
                    @if ($tbl->TblEng != 'Users')
                        <div class="label label-primary">Group: {{ $group }}</div>
                        <div class="label label-primary">Type: {{ $tbl->TblType }}</div>
                        <div class="label label-primary">{{ $tbl->TblNumFields }} Fields Total</div>
                        @if ($tbl->TblNumForeignKeys > 0)
                            {{ $tbl->TblNumForeignKeys }} Outgoing 
                            @if ($tbl->TblNumForeignKeys == 1) Key @else Keys @endif 
                        @endif
                    @endif
                    {!! $basicTblFlds[$tbl->TblID] !!}
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
