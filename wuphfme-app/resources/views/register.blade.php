<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Wuphf</title>

        <link href="/css/app.css" rel="stylesheet" type="text/css" />
		<script>window.Laravel={csrfToken:"{{ csrf_token() }}"};</script>
		<script src="/js/app.js" type="text/javascript" ></script>
    </head>
    <body>
        <div id="header">
			<div class="content">
				<span class="brand"><img src="/img/banner-xsmall.png" /></span>
				<span class="actions">
					<div>James Rowley - <a href="#">Logout</a></div>
					<div><a href="#">Manage</a></div>
				</span>
			</div>
		</div>
		<div id="main">
			<div class="content">
				<div id="wuphf-app">
					<sync-text endpoint="/ste" initial="{{ Session::get("seshtest") }}"></sync-text>
					<sync-text endpoint="/contacts/ferret_guy/email/something"></sync-text>
				</div>
			</div>
		</div>
		<script>
			var wapp = new Vue({
				el: "#wuphf-app"
			});
		</script>
    </body>
</html>
