<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ uniqid() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Load</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" />
</head>
<body>
	<div class="container">
		<div ui-view></div>
	</div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="../app/js/front/node_modules/jquery-validation/dist/jquery.validate.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<!-- Angular Core -->
	<script src="../app/js/front/node_modules/angular/angular.min.js"></script>
	<script src="../app/js/front/node_modules/angular-ui-router/release/angular-ui-router.min.js"></script>
	<script src="../app/js/front/node_modules/angular-ui-uploader/dist/uploader.min.js"></script>
	<script src="../app/js/front/node_modules/angular-file-upload/dist/angular-file-upload.min.js"></script>
	<script src="../app/js/front/node_modules/angular-ui-validate/dist/validate.min.js"></script>
	<script src="../app/js/front/node_modules/angular-form-validate/dist/angular-validate.min.js"></script>
	<!-- Angular Component-->
	<script src="../app/js/front/app.js"></script>
	<script src="../app/js/front/controllers/mainControllers.js"></script>
	<script src="../app/js/front/services/mainServices.js"></script>
</body>
</html>