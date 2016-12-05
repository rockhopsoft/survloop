<!-- resources/views/vendor/survloop/profileAdm.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')
<div class="fC p20"></div>
<form name="deptEditor" action="/dashboard/volun/user/{{ $profileUser->id }}" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="uID" value="{{ $profileUser->id }}">

<h2>Profile: {{ $profileUser->name }}</h2>
<div class="fL"><table border=0 cellpadding=5 cellspacing=0 >
<tr><td><div class="nPrompt"><label for="nameID">Name:</label></div></td>
    <td><div class="nFld"><input type="text" name="name" id="nameID" value="{{ $profileUser->name }}"></div></td></tr>
<tr><td><div class="nPrompt"><label for="emailID">Email:</label></div></td>
    <td><div class="nFld"><input type="email" name="email" id="emailID" value="{{ $profileUser->email }}"></div></td></tr>
<tr><td class="vaT"><div class="nPrompt">Roles:</td><td><div class="nFldRadio"><?php
    foreach ($profileUser->rolesRanked as $i => $role) {
        echo "\n".'<input type="checkbox" name="roles[]" id="role'.$i.'" value="' . $role . '" ' 
            . (($profileUser->hasRole($role)) ? 'CHECKED' : '') . ' > <label for="role'.$i.'">' . ucfirst($role) . '</label><br />';
    }
?></div></td></tr>
<tr><td><div class="nPrompt">Since:</div></td>
    <td><div class="nPrompt">{{ date('F d, Y', strtotime($profileUser->created_at)) }}</div></td></tr>
</table></div>

<div class="nodeGap p20"></div>

<input type="submit" class="nFormBtnSub" style="font-size: 32px; float: none;" value="Save Changes">

</form>
<div class="fC p20 m20"></div>
@endsection