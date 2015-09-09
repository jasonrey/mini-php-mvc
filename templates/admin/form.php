<?php
!defined('SERVER_EXEC') && die('No access.');
?>
<!DOCTYPE html>
<html>
<head>
<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700,100' rel='stylesheet' type='text/css' />
<link rel="stylesheet/less" type="text/css" href="assets/css/admin.less" />
<script type="text/javascript" src="assets/js/less.min.js"></script>
<title>Admin</title>
</head>
<body>
<div class="section">
	<form class="login-form">
		<div class="form-group">
			<label class="form-label" for="username">Username</label>
			<input class="form-input" type="text" />
		</div>
	</form>
</div>

<div class="container">
	<div id="loginbox">
		<div class="page-header">
			<h1 class="text-info">Admin</h1>
		</div>

		<div class="panel panel-info" >
			<div class="panel-heading">
				<div class="panel-title">Sign In</div>
			</div>

			<div class="panel-body">
				<form id="loginform" role="form" method="post" action="<?php echo Lib::url('admin', array('type' => 'system', 'subtype' => 'verify')); ?>">
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
							<input id="login-username" type="text" class="form-control" name="username" value="" placeholder="Username">
						</div>
					</div>

					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
							<input id="login-password" type="password" class="form-control" name="password" placeholder="Password">
						</div>
					</div>

					<div class="form-group">
						<div class="input-group">
							<input type="submit" value="Login" class="btn btn-info" />
						</div>
					</div>

					<input type="hidden" name="ref" value="<?php echo $ref; ?>" />
				</form>
			</div>
		</div>
	</div>
</div>
</body>
</html>
