<!-- resources/views/vendor/survloop/admin/user-manage.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container"><div class="slCard nodeWrap">
<h1><i class="fa fa-users"></i> Manage User Privileges</h1>

<div class="mT15 mB15 row">
    <div class="col-2"><b>
        {{ sizeof($printVoluns[0]) }} Administrators
    </b></div>
    <div class="col-2"><b>
        {{ sizeof($printVoluns[1]) }} Databasers
    </b></div>
    <div class="col-2"><b>
        {{ number_format(sizeof($printVoluns[2])) }} Staff
    </b></div>
    <div class="col-2"><b>
        {{ number_format(sizeof($printVoluns[3])) }} Partners
    </b></div>
    <div class="col-2"><b>
        {{ number_format(sizeof($printVoluns[4])) }} Volunteers
    </b></div>
    <div class="col-2"><b>
        {{ number_format(sizeof($printVoluns[5])) }} Basic Users
    </b></div>
</div>

<p>
    <b>Adding A User:</b> Use the public volunteer sign up form 
    (<a href="/register" target="_blank">/register</a>, while logged out, easiest in a separate browser) 
    to first create the new user. Then reload this page and change their privileges here as needed.
</p>

<div class="mT15 p5 brdBot">
    <div class="row">
        <div class="col-4"><b>Name</b></div>
        <div class="col-5">
            <b>Email</b> <span class="slGrey mL10">
            [<i class="fa fa-check-circle-o" 
                aria-hidden="true"></i> 
            verified]</span>
        </div>
        <div class="col-3"><b>User Permissions</b></div>
    </div>
</div>
<?php $cnt = 0; ?>
@foreach ($printVoluns as $userSet)
    @forelse ($userSet as $volun)
        <?php $cnt++; ?>
        <div class="p5 @if ($cnt%2 == 1) row2 @endif ">
            <div class="row">
                <div class="col-4"><b>
                    {!! $volun->printUsername(true) !!}
                </b></div>
                <div class="col-5">
                    <a href="mailto:{{ $volun->email }}"
                        style="word-break: break-all;"
                        >{{ $volun->email }}</a>
                    @if ($volun->hasVerifiedEmail())
                        <span class="slGrey">
                        <i class="fa fa-check-circle-o mL10" 
                            aria-hidden="true"></i></span>
                    @endif
                </div>
                <div class="col-3">
                    {{ $volun->listRoles() }}
                </div>
            </div>
        </div>
    @empty
    @endforelse
@endforeach

</div></div>

<div class="adminFootBuff"></div>
@endsection