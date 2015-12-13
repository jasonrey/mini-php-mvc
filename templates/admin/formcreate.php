<?php
!defined('SERVER_EXEC') && die('No access.');
?>
<div class="page">
	<div class="section-login-form">
		<form class="login-form" role="form" method="post" action="<?php echo Lib::url('admin', array('view' => 'admin', 'type' => 'system', 'subtype' => 'create')); ?>">
			<?php if (isset($errorMessage)) { ?>
			<div class="alert alert-danger">There was an error.</div>
			<?php } ?>

			<div class="alert alert-warning">There are no admins created yet.<br />Create one now.</div>

			<div class="form-group">
				<label class="form-label" for="username">Username</label>
				<input class="form-input" type="text" name="username" />
			</div>

			<div class="form-group">
				<label class="form-label" for="username">Password</label>
				<input class="form-input" type="password" name="password" />
			</div>

			<input type="hidden" name="ref" value="<?php echo $ref; ?>" />

			<div class="form-actions">
				<button class="form-button" type="submit">Create</button>
			</div>
		</form>
	</div>
</div>
