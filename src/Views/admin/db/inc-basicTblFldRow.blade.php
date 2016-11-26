<!-- resources/views/vendor/survloop/admin/db/inc-basicTblFldRow.blade.php -->
<tr>
	<td>
		<div class="div">
			<div class="col-md-9">
			
				@if ($tblLinks > 0 && $dbAllowEdits && !$isPrint)
					<a 
					@if ($fld->FldTable > 0 && isset($GLOBALS["DB"]->tblAbbr[$GLOBALS['DB']->tbl[$fld->FldTable]])) 
						href="/dashboard/db/field/{{ $GLOBALS["DB"]->tblAbbr[$GLOBALS['DB']->tbl[$fld->FldTable]] }}/{{ $fld->FldName }}"
					@else
						href="/dashboard/db/field/generic/{{ $fld->FldName }}/{{ $fld->FldName }}"
					@endif
						><i class="fa fa-pencil fa-flip-horizontal mR5"></i> 
						<b class="fPerc125">{{ $fld->FldEng }}</b></a>
				@else 
					<b class="fPerc125">{{ $fld->FldEng }}</b>
				@endif
				<a id="fldSpecBtn{{ $fld->FldID }}" href="javascript:void(0)" 
					></a>
				@if ($fld->FldSpecType == 'Replica') 
					<span class="gry6 f8" data-toggle="tooltip" data-placement="top" 
						title="Replica field (copy of a Generic field)"><sup>^</sup></span>
				@endif
				
				<div id="fldSpecA{{ $fld->FldID }}" class=" @if ($isAll && $fld->FldID > 0) disNon @else disBlo @endif ">
					@if (trim($fld->FldDesc) != '')
						<div class="pL20">{!! $fld->FldDesc !!}</div>
					@endif
					@if (trim($fld->FldNotes) != '') 
						<div class="pL20 gryA"><i class="mR5">Notes:</i> {!! $fld->FldNotes !!}</div>
					@endif
					@if ($fld->FldID > 0 && isset($dbBusRulesFld[$fld->FldID]))
						<div><a href="/dashboard/db/bus-rules/edit/{{ $dbBusRulesFld[$fld->FldID][0] }}" 
							data-toggle="tooltip" data-placement="top" target="_blank" 
							title="{!! str_replace('"', "'", strip_tags($dbBusRulesFld[$fld->FldID][1])) !!}" 
							><i class="fa fa-university"></i></a></div>
					@endif
				</div>
				
				@if (trim($FldValues) != '' || trim($fld->FldDefault) != '')
					<div class="pL20 gry6">
					@if (trim($FldValues) != '')
						<i class="mR5">Values:</i>
						@if (strpos($FldValues, 'Def::') !== false)
							{{ str_replace('Def::', '', $FldValues) }} <span class="f10">(Definitions)</span>
						@else
							{{ $FldValues }}
						@endif
					@endif
					@if (trim($fld->FldDefault) != '')
						<i class="gry9 mL10">( default value: {{ $fld->FldDefault }} )</i>
					@endif
					</div>
				@endif
				
			</div>
			<div class="col-md-3 taR gry6">
			
				@if ($tblID > 0 && isset($GLOBALS["DB"]->tblAbbr[$GLOBALS['DB']->tbl[$tblID]]) && isset($fld->FldName))
					<div>{{ $GLOBALS["DB"]->tblAbbr[$GLOBALS['DB']->tbl[$tblID]] }}{{ $fld->FldName }}</div>
				@endif
				@if (strpos($fld->FldKeyType, 'Primary') !== false)
					@if ($fld->FldKeyStruct == 'Composite') Composite, @endif
					<b>Primary Key,</b> 
				@endif
				<nobr><i>
				@if (isset($FldDataTypes[$fld->FldType]))
					{{ $FldDataTypes[$fld->FldType][1] }} 
				@endif
				@if ($fld->FldIsIndex == 1)
					<span class="gry6 f8">Indexed</span> 
				@endif
				</i></nobr>
				@if (trim($fldForeignPrint) != '' || trim($fldGenerics) != '')
					<div>
						@if (trim($fldForeignPrint) != '')
							{!! $fldForeignPrint !!}
						@endif
						@if (trim($fldGenerics) != '')
							{!! $fldGenerics !!}
						@endif
					</div>
				@endif
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				
				@if ($isAll && $fld->FldID > 0) 
					<div id="fldSpec{{ $fld->FldID }}" class="disBlo p20 m20">
						{!! view( 'vendor.survloop.admin.db.fieldSpecifications', [ 
							"fld" 			=> $fld, 
							"fldSfx"		=> $fld->FldID,
							"FldDataTypes"	=> $FldDataTypes,
							"help"			=> $help, 
							"edit"			=> false, 
							"chkDis"		=> ' disabled ',
							"defSet"		=> ((strpos($fld->FldValues, 'Def::') !== false || strpos($fld->FldValues, 'DefX::') !== false) 
								? trim(str_replace('Def::', '', str_replace('DefX::', '', $fld->FldValues))) : '')
						] )->render() !!}
					</div>
				@else 
					<div id="fldSpec{{ $fld->FldID }}" class="disNon p20 m20"></div>
				@endif
				
			</div>
		</div>
	<td>
</tr>
