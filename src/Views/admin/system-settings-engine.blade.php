<!-- resources/views/vendor/survloop/admin/system-settings-engine.blade.php -->
@extends('vendor.survloop.admin.system-settings-ajax-form')
@section('formContent')

<input type="hidden" id="optListID" name="optList" 
	value="has-usernames,user-name-req,has-partners,has-volunteers,req-mfa-users,req-mfa-volunteers,req-mfa-partners,req-mfa-staff,req-mfa-admin,users-create-db,has-canada,site-name,cust-abbr,cust-package,parent-company,app-url,logo-url,parent-website,app-root-path,app-license,app-license-url,app-license-img,app-license-snc,rawSettings">
<input type="hidden" id="styListID" name="styList" value="">

<h3 class="mB15 slBlueDark">User Settings</h3>
<table class="table table-striped">
@foreach ([
		'has-usernames', 'user-name-req', 
        'has-partners', 'has-volunteers', 
        'req-mfa-admin', 'req-mfa-staff', 'req-mfa-partners', 
        'req-mfa-volunteers', 'req-mfa-users',
        'users-create-db', 'has-canada'
	] as $opt)
    {!! view(
        'vendor.survloop.admin.system-one-setting', 
        [
            "opt" => $opt, 
            "val" => $sysDef->v["settingsList"][$opt]
        ]
    )->render() !!}
@endforeach
</table>

@if (sizeof($sysDef->v["rawSettings"]) > 0)
	<p><br /></p>

	<h3 class="mT30 mB15 slBlueDark">Custom Settings</h3>
	<table class="table table-striped">
    @foreach ($sysDef->v["rawSettings"] as $i => $s)
        <h4 class="m0">{{ $s->setting }}</h4>
        <label class="mL20">
            <input type="radio" name="setting{{ $i }}" value="Y"
                @if ($s->val == 'Y') CHECKED @endif
                > Yes
        </label>
        <label class="mL20">
            <input type="radio" name="setting{{ $i }}" value="N"
                @if ($s->val == 'N') CHECKED @endif
                > No
        </label>
        @if ($i == ceil(sizeof($sysDef->v["rawSettings"])/2))
            </div><div class="col-md-6">
        @endif
    @endforeach
    </table>
@endif

<p><br /></p>

<h3 class="mT30 mB15 slBlueDark">Survloop Extension Package Details</h3>
<table class="table table-striped">
@foreach ([
		'site-name', 'cust-abbr', 'cust-package', 'parent-company',
		'app-url', 'logo-url', 'parent-website', 'app-root-path'
	] as $opt)
    {!! view(
        'vendor.survloop.admin.system-one-setting', 
        [
            "opt" => $opt, 
            "val" => $sysDef->v["settingsList"][$opt] 
        ]
    )->render() !!}
@endforeach
</table>

<p><br /></p>

<h3 class="mT30 mB15 slBlueDark">License Settings</h3>
<table class="table table-striped">
@foreach ([
		'app-license', 'app-license-url', 
		'app-license-img', 'app-license-snc'
	] as $opt)
    {!! view(
        'vendor.survloop.admin.system-one-setting', 
        [
            "opt" => $opt, 
            "val" => $sysDef->v["settingsList"][$opt]
        ]
    )->render() !!}
@endforeach
</table>

@endsection
