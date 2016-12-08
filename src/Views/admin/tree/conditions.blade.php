<!-- resources/views/vendor/survloop/admin/tree/conditions.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<div class="row">
    <div class="col-md-8 pB10">
        <h1><i class="fa fa-snowflake-o"></i> Filters / Conditions</h1>
        
        <form name="nodeEditor" method="post" action="/dashboard/tree/conds" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="addNewCond" value="1">
        {!! view('vendor.survloop.admin.db.inc-addCondition', [ "newOnly" => true ])->render() !!}
        </form>
    </div>
    <div class="col-md-4 taR p20">
        <a href="/dashboard/tree/conds" class="btn btn-xs mL10 @if ($filtOnly == 'all') btn-primary @else btn-default @endif ">All Filters</a>
        <a href="/dashboard/tree/conds?only=public" class="btn btn-xs mL10 @if ($filtOnly == 'public') btn-primary @else btn-default @endif ">Public Only</a>
        <a href="/dashboard/tree/conds?only=articles" class="btn btn-xs mL10 @if ($filtOnly == 'articles') btn-primary @else btn-default @endif ">Articles Only</a>
    </div>
</div>

<i>Make any edits needed below, and click the save button at the bottom. Multiple articles can be separated by commas.</i>

<form name="nodeEditor" method="post" 
    @if ($filtOnly == 'all') action="/dashboard/tree/conds"
    @else action="/dashboard/tree/conds?only={{ $filtOnly }}"
    @endif
    >
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="totalConds" value="{{ sizeof($condSplits) }}">
<input type="hidden" name="CondList" value="{{ $condIDs }}">


<table class="table table-striped f18">
<tr><th class="w33">Hash Tag, Description</th><th class="w33">Options</th><th class="w33">Condition (Raw)</th></tr>
@forelse ($condSplits as $i => $cond)
    <input type="hidden" name="CondID{{ $i }}" value="{{ $cond->CondID }}">
    <tr>
        <td>
            <input type="text" name="CondTag{{ $i }}" value="{{ $cond->CondTag }}" class="form-control f22 mB5">
            <input type="text" name="CondDesc{{ $i }}" value="{{ $cond->CondDesc }}" class="form-control f14">
        </td>
        <td>
            <div class="row">
                <div class="col-md-6">
                    <label>
                        <input type="checkbox" name="CondPublicFilter{{ $i }}" value="1" autocomplete="off"
                            @if ($cond->CondOpts%2 == 0) CHECKED @endif
                            > For public use
                    </label>
                </div>
                <div class="col-md-6">
                    
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>
                        <input type="checkbox" name="CondHasArticle{{ $i }}" id="CondHasArticleID{{ $i }}" class="hasArticleBox" value="1" autocomplete="off"
                            @if ($cond->CondOpts%3 == 0)
                                CHECKED
                            @endif
                            > Has related article(s)
                    </label>
                </div>
                <div class="col-md-6">
                </div>
            </div>
            <div id="condArticle{{ $i }}" class=" @if ($cond->CondOpts%3 == 0) disBlo @else disNon @endif ">
                <input type="text" name="CondArticles{{ $i }}" class="form-control f14 condArticleBox" 
                    @if ($cond->CondOpts%3 == 0)
                        value="{{ implode(' , ', $condArticles[$cond->CondID]) }}" 
                    @else
                        value=""
                    @endif
                    >
            </div>
        </td>
        <td>
            {!! view('vendor.survloop.admin.db.inc-describeCondition', [ "cond" => $cond, "i" => $i ])->render() !!}
            <div class="f10">
                <a href="javascript:void(0)" onClick="alert('(coming soon)');" class="f10 mR5">Edit</a> - 
                <a href="javascript:void(0)" id="condDelBtn{{ $i }}" class="condDelBtn f10 mL5">Delete</a>
            </div>
            <div id="condDel{{ $i }}" class="disNon red">
                <label>
                    <input type="checkbox" name="CondDelete{{ $i }}" value="1" autocomplete="off"> Yes, Delete
                </label>
            </div>
        </td>
    </tr>
@empty
@endforelse
</table>

<br />
<center><input type="submit" value="Save All Changes" class="btn btn-lg btn-primary" ></center>

</form>

<script type="text/javascript">
$(function() {
    $(document).on("click", "#addNewCond", function() {
        $("#newCond").slideToggle("fast");
    });
    $(document).on("click", ".condDelBtn", function() {
        var i = $(this).attr("id").replace("condDelBtn", "");
        $("#condDel"+i+"").slideToggle("fast");
    });
    $(document).on("click", ".hasArticleBox", function() {
        var i = $(this).attr("id").replace("CondHasArticleID", "");
        if (document.getElementById('CondHasArticleID'+i+'').checked) $("#condArticle"+i+"").slideDown("fast");
        else $("#condArticle"+i+"").slideUp("fast");
        return true;
    });
});
</script>

<div class="p20"></div><div class="p20"></div>
@endsection