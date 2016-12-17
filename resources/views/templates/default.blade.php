<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Chatty</title>
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	</head>
	<body>
		@include('templates.partials.navigation')
		<div class="container">
			@include('templates.partials.alerts')
			@yield('content')
		</div>
	</body>
</html>

<!-- https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css -->

<!-- bootstrap/css/bootstrap.css -->