<!-- resources/views/vendor/survloop/admin/tree/node-list-conditions.blade.php -->
@if (isset($conds) && sizeof($conds) > 0)
	@foreach ($conds as $i => $cond)
		@if ($i > 0) ; @endif
		{!! view( 'vendor.survloop.admin.db.inc-describeCondition', [ "cond" => $cond, "i" => $i ])->render() !!}
	@endforeach
@endif