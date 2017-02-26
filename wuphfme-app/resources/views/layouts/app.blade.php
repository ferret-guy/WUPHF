<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Wuphf</title>

        <link href="/css/app.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div id="w-header">
			<div class="w-content">
				<span class="w-brand"><a href="/"><img src="/img/banner-xsmall.png" /></a></span>
				<span class="w-actions">
				@if (!Auth::guest())
					<div>{{ Auth::user()->username }} - <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
						Logout
					</a></div>
					<div><a href="#">Manage</a></div>
				@endif
				</span>
				<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
					{{ csrf_field() }}
				</form>
			</div>
		</div>
		<div id="w-main">
			<div class="w-content">
				@yield('content')
			</div>
		</div>
    </body>
</html>

