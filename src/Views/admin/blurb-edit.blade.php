<!-- resources/views/survloop/admin/blurb-edit.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container"><div class="slCard nodeWrap">
<form name="mainPageForm" action="/dashboard/pages/snippets/{{ $blurbRow->def_id }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="DefID" value="{{ $blurbRow->def_id }}">
<div class="row">
    <div class="col-8">
        <h2>Editing Excerpt</h2>
        <label><h3>Excerpt Name:</h3>
        <input type="text" class="form-control form-control-lg slBlueDark" name="DefSubset" 
            value="{{ $blurbRow->def_subset }}">
        </label>
    </div>
    <div class="col-4 taR">
        <a href="/dashboard/pages/snippets" class="btn btn-sm btn-secondary m10"
            ><i class="fa fa-caret-left"></i> Back To Snippets List</a>
        <div class="pT20 mT20 mR10 mB10">
            <label>Hard-Coded 
                <input type="checkbox" name="optHardCode" value="3" 
                    @if ($blurbRow->def_order > 0 && $blurbRow->def_order%3 == 0) CHECKED @endif >
            </label>
        </div>
        <input type="submit" class="btn btn-lg btn-primary m10" value="Save Changes">
    </div>
</div>
<h3 class="mT20 mB5">Excerpt Content:</h3>
<textarea name="DefDescription" id="DefDescriptionID" class="form-control
    @if ($blurbRow->def_order > 0 && $blurbRow->def_order%3 == 0) w100 @else w100 nPrompt @endif "
    style="height: 500px;">{!! $blurbRow->def_description !!}</textarea><br /><br />
<input type="submit" class="btn btn-lg btn-primary m10" value="Save Changes">
</form>
</div></div>

<div class="adminFootBuff"></div>

@endsection