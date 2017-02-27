@extends('layouts.app')

@section('content')
	<div id="main-app">
		@php
			$validAPIs = Array(
				"email" => "address",
				"phone" => "number",
				"print" => "printerid",
				"sms" => "number",
				"snapchat" => "username",
				"tweet" => "username"
			);
		@endphp
		<div id="#main-app" class="container">
			@foreach(Auth::user()->associated as $user)
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<div class="panel panel-default">
						<div class="panel-heading">Contact: {{$user}}</div>
						<div class="panel-body">
							@foreach($validAPIs as $pk=>$sk)
							<div class="form-group{{ $errors->has($user.'-'.$pk) ? ' has-error' : '' }}">
								<label for="{{$user}}-{{$pk}}" class="col-md-4 control-label">{{ucfirst($pk)}}</label>
					
								<div class="col-md-6">
									<sync-text endpoint="/contacts/{{$user}}/{{$pk}}/{{$sk}}" id="{{$user}}-{{$pk}}" type="text" class="form-control" name="{{$user}}-{{$pk}}" value="{{ old($user.'-'.$pk) }}" required>
					
									@if ($errors->has($user.'-'.$pk))
										<span class="help-block">
											<strong>{{ $errors->first($user.'-'.$pk) }}</strong>
										</span>
									@endif
								</div>
							</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
			@endforeach
			<form method="POST" action="/createuser">
			{{ csrf_field() }}
				<div class="row">
					<div class="col-md-8 col-md-offset-2">
						<div class="panel panel-default">
							<div class="panel-heading">Create User</div>
							<div class="panel-body">
								<div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
									<label for="username" class="col-md-4 control-label">Username</label>
										<input style="display: inline-block;width: 40%;" id="username" type="text" class="form-control" name="username" value="" required>
						
										@if ($errors->has('username'))
											<span class="help-block">
												<strong>{{ $errors->first('username') }}</strong>
											</span>
										@endif
										<button style="float: right;" type="submit" class="btn btn-primary">
											Create
										</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<script>window.Laravel={csrfToken:"{{ csrf_token() }}"};</script>
	<script src="js/app.js"></script>
	<script>
		var app = new Vue({
			el: "#main-app"
		});
	</script>
@endsection
