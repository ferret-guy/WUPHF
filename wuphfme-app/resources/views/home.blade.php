@extends('layouts.app')

@section('content')
	<div id="w-lead">
		SEND A WUPHF:
	</div>
	@if ($errors->has('success'))
		<div class="success-msg">
			{{$errors->first('success')}}
		</div>
	@endif
	<br/>
	<form class="form-horizontal" role="form" method="POST" action="/wuphf">
		{{ csrf_field() }}

		<div class="form-group{{ $errors->has('recipient') ? ' has-error' : '' }}">
			<label for="recipient" class="col-md-4 control-label">Recipient</label>

			<div class="col-md-6">
				<input id="recipient" type="text" class="form-control" name="recipient" value="{{ old('recipient') }}" list="userlist" required>
				<datalist id="userlist">
					@foreach(Auth::user()->associated as $user)
					<option value="{{ $user }}">
					@endforeach
				</datalist>

				@if ($errors->has('recipient'))
					<span class="help-block">
						<strong>{{ $errors->first('recipient') }}</strong>
					</span>
				@endif
			</div>
		</div>

		<div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">
			<label for="message" class="col-md-4 control-label">Message</label>

			<div class="col-md-6">
				<input id="message" type="message" class="form-control" name="message" required>

				@if ($errors->has('message'))
					<span class="help-block">
						<strong>{{ $errors->first('message') }}</strong>
					</span>
				@endif
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-6 col-md-offset-4">
				<button type="submit" class="btn btn-primary">
					Send
				</button>
			</div>
		</div>
	</form>
@endsection
