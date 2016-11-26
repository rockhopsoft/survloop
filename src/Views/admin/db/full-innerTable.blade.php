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
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="row gry6">
						<div class="col-md-9">
							
							@if ($tbl->TblEng != 'Users')
								<a href="/dashboard/db/table/{{ $tbl->TblName }}"><h2 class="m0">{{ $tbl->TblEng }}</h2></a>
							@else
								<h2 class="m0">{{ $tbl->TblEng }}</h2>
							@endif
							<h4 class="mT5">{!! $tbl->TblDesc !!}</h4>
							
							@if ($tbl->TblNumForeignIn > 0)
								<div class="pB5"><i class="fa fa-link"></i> {{ $tbl->TblNumForeignIn }} 
									@if ($tbl->TblNumForeignIn != 1) Tables @else Table @endif
									with Foreign Key{{ (($tbl->TblNumForeignIn != 1) ? 's' : '') }}
									@if (isset($tblForeigns[$tbl->TblID]))
										Incoming:</i> {!! $tblForeigns[$tbl->TblID] !!}
									@endif
								</div>
							@endif
							@if (isset($tblRules[$tbl->TblID]))
								@forelse ($tblRules[$tbl->TblID] as $rule)
									<div class="pB5"><a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" target="_blank" class="gry6"
										><i class="fa fa-university"></i> <i>{{ $rule->RuleStatement }}</i></a></div>
								@empty
								@endforelse
							@endif
							
						</div>
						<div class="col-md-3 taR gry6">
						
							@if ($tbl->TblEng != 'Users')
								<h4 class="m0">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }})</h4>
								group: {{ $group }}<br />
								type: {{ $tbl->TblType }}<br />
								{{ $tbl->TblNumFields }} Fields Total<br />
								@if ($tbl->TblNumForeignKeys > 0)
									{{ $tbl->TblNumForeignKeys }} Outgoing @if ($tbl->TblNumForeignKeys == 1) Key @else Keys @endif <br />
								@endif
							@else 
								<h4 class="m0">users</h4>
								3 Field Totals<br />
							@endif
							
						</div>
					</div>
				</div>
				<div class="panel-body">
					
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
