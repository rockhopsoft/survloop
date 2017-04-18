<!-- resources/views/survloop/admin/blurb-edit.blade.php -->

@extends('vendor.survloop.master')

@section('content')
<form name="editInstruct" action="/dashboard/blurbs/{{ $blurbRow->DefID }}" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="DefID" value="{{ $blurbRow->DefID }}">
<div class="row">
    <div class="col-md-8">
        <h2>Editing Blurb</h2>
        <label><h3>Blurb Name:</h3>
        <input type="text" class="form-control input-lg slBlueDark f26" name="DefSubset" 
            value="{{ $blurbRow->DefSubset }}">
        </label>
    </div>
    <div class="col-md-4 taC">
        <a href="/dashboard/pages/list" class="btn btn-sm btn-default m20"
            ><i class="fa fa-caret-left"></i> Back To Blurbs List</a>
        <input type="submit" class="btn btn-lg btn-primary m10" value="Save Changes">
    </div>
</div>
<h3 class="mT20 mB5">Blurb Content:</h3>
<textarea name="DefDescription" id="summernote" class="w100 nPrompt" style="height: 500px;"
    >{!! $blurbRow->DefDescription !!}</textarea><br /><br />
</form>

<div class="adminFootBuff"></div>
@endsection