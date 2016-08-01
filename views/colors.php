<!doctype html>
<html>
<head>
  <!-- pre -->
  <link rel="stylesheet" href="public/css/bootstrap.css">
  <link rel="stylesheet" href="public/css/font-awesome.min.css">
  <link rel="stylesheet" href="public/css/master.css">
  <link href='https://fonts.googleapis.com/css?family=Bangers' rel='stylesheet' type='text/css'>
  <script src="public/js/lib/angular-1.3.5/angular.min.js"></script>
  <script src="public/js/lib/angular-1.3.5/angular-sanitize.min.js"></script>
  <script src="public/js/lib/angular-1.3.5/angular-resource.min.js"></script>
  <script src="public/js/lib/angular-ui.router.js"></script>
  <script src="public/js/lib/jquery-2.1.0.min.js"></script>
  <script src="public/js/lib/bootstrap.min.js"></script>
  <script src="public/js/lib/ui-bootstrap-tpls-0.11.0.min.js"></script>
  <!-- end pre -->

  <!-- theme -->
  <title>Abraham Lincoln - Colors</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" type="image/x-icon" href="public/images/favicon_darkvision.ico">
  <link rel="stylesheet" href="public/genius/css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="public/genius/css/style.min.css" type="text/css" />
  <link rel="stylesheet" href="public/genius/css/retina.min.css" type="text/css" />
  <link rel="stylesheet" href="public/css/abe_lincoln.css" type="text/css" />
</head>

<html ng-app="Home">
<body ng-controller="ListDetail">
<div class="content container-fluid" ng-controller='ListDetail' data-ng-init="poll()" ui-view>
  <div class="col-xs-12"><h1>ABRAHAM LINCOLN</h1></div>
  <div class="link-buttons-container col-xs-12">
    <div class="link-buttons col-xs-2 col-xs-offset-1"><a href="/">H O M E</a></div>
    <div class="link-buttons col-xs-2"><a class="active" href="./colors">C O L O R</a></div>
    <div class="link-buttons col-xs-2"><a href="./videos">V I D E O S</a></div>
  </div>
  <div class="col-xs-12">
    <div class="insides col-xs-10 col-xs-offset-1">
    	<div class="row">
	    	<div class="col-xs-3"><a href="public/images/lincolncolor1.gif" download><img src="public/images/lincolncolor1.gif"/></a></div>
	    	<div class="col-xs-3"><a href="public/images/lincolncolor2.jpg" download><img src="public/images/lincolncolor2.jpg"/></a></div>
	    	<div class="col-xs-3"><a href="public/images/lincolncolor3.jpg" download><img src="public/images/lincolncolor3.jpg"/></a></div>
	    	<div class="col-xs-3"><a href="public/images/lincolncolor4.gif" download><img src="public/images/lincolncolor4.gif"/></a></div>
    	</div>
    	<div class="row padding-top">
	    	<div class="col-xs-3"><a href="public/images/lincolncolor5.png" download><img src="public/images/lincolncolor5.png"/></a></div>
    	</div>
    </div>
  </div>
</div> <!-- content container-fluid -->

<script src="public/js/index.js"></script>

</body>
</html>
