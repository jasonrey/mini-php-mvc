<?php
!defined('SERVER_EXEC') && die('No access.');
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" />
<title>Admin</title>
</head>
<body>
<div class="container">
	<div id="loginbox" class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
		<div class="page-header">
			<h1 class="text-info">Admin</h1>
		</div>

		<div class="panel panel-info" >
			<div class="panel-heading">
				<div class="panel-title">Sign In</div>
			</div>

			<div class="panel-body">
				<form id="loginform" role="form" method="post" action="<?php echo Lib::url('api', array('type' => 'admin', 'action' => 'verify')); ?>">
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
