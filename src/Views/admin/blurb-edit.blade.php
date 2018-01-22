<!-- resources/views/survloop/admin/blurb-edit.blade.php -->

@extends('vendor.survloop.master')

@section('content')
<form name="mainPageForm" action="/dashboard/blurbs/{{ $blurbRow->DefID }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="DefID" value="{{ $blurbRow->DefID }}">
<div class="row">
    <div class="col-md-8">
        <h2>Editing Excerpt</h2>
        <label><h3>Excerpt Name:</h3>
        <input type="text" class="form-control input-lg slBlueDark f26" name="DefSubset" 
            value="{{ $blurbRow->DefSubset }}">
        </label>
    </div>
    <div class="col-md-4 taR">
        <a href="/dashboard/pages/list" class="btn btn-sm btn-default m10"
            ><i class="fa fa-caret-left"></i> Back To Excerpts List</a>
        <div class="pT20 mT20 mR10 mB10">
            <label>Hard-Coded <input type="checkbox" name="optHardCode" value="3" 
                @if ($blurbRow->DefIsActive > 0 && $blurbRow->DefIsActive%3 == 0) CHECKED @endif ></label>
        </div>
        <input type="submit" class="btn btn-lg btn-primary m10" value="Save Changes">
    </div>
</div>
<h3 class="mT20 mB5">Excerpt Content:</h3>
<textarea name="DefDescription" id="DefDescriptionID" 
    @if ($blurbRow->DefIsActive > 0 && $blurbRow->DefIsActive%3 == 0) class="w100" @else class="w100 nPrompt" @endif
    style="height: 500px;">{!! $blurbRow->DefDescription !!}</textarea><br /><br />
<input type="submit" class="btn btn-lg btn-primary m10" value="Save Changes">
</form>

<div class="adminFootBuff"></div>
@endsection