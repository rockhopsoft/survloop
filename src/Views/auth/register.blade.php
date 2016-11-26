<!-- resources/views/vendor/survloop/auth/register.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="p20"></div>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
					<div class="row m0">
						<div class="col-md-9 pB10">
							@if (isset($GLOBALS["DB"]->sysOpts["signup-instruct"]) 
								&& trim($GLOBALS["DB"]->sysOpts["signup-instruct"]) != '')
								{!! $GLOBALS["DB"]->sysOpts["signup-instruct"] !!}
							@else
								<h1 class="m0">Sign Up</h1>
							@endif
						</div>
						<div class="col-md-3 taR pT5">
							@if (!isset($GLOBALS["DB"]->sysOpts["signup-instruct"]) 
								|| trim($GLOBALS["DB"]->sysOpts["signup-instruct"]) != '<h2 class="mT5 mB0">Create Admin Account</h2>')
								<a href="/login" class="btn btn-default">Login</a>
							@endif
						</div>
					</div>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/register') }}">
                    <input type="hidden" name="newVolunteer" value="1" >
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">
                            	<span class="nPrompt">Name</span>
                            </label>
	                        
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">
                            	<span class="nPrompt">E-Mail Address</span>
                            </label>
	                        
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">
                            	<span class="nPrompt">Password</span>
                            </label>
	                       
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password-confirm" class="col-md-4 control-label">
                            	<span class="nPrompt">Confirm Password</span>
                            </label>
                            
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group taC">
							<button type="submit" class="btn btn-primary f32">
								Register
							</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="nodeGap"></div>

</form></div></center>

@endsection
