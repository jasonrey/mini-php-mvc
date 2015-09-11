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
<div class="page">
	<div class="section section-login-form">
		<form class="login-form" role="form" method="post" action="<?php echo Lib::url('admin', array('type' => 'system', 'subtype' => 'login')); ?>">
			<h2>Admin</h2>
			<div class="form-group">
				<label class="form-label" for="username">Username</label>
				<input class="form-input" type="text" />
			</div>

			<div class="form-group">
				<label class="form-label" for="username">Password</label>
				<input class="form-input" type="password" />
			</div>

			<input type="hidden" name="ref" value="<?php echo $ref; ?>" />

			<div class="form-actions">
				<button class="form-button" type="submit">Login</button>
			</div>
		</form>
	</div>
</div>
</body>
</html>
