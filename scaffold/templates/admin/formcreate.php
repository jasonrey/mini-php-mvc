<?php
!defined('MINI_EXEC') && die('No access.');

$this->using('common/html');

$this->start('body'); ?>

<div class="page">
	<div class="section-login-form">
		<form class="login-form" role="form" method="post" action="<?php echo $actionUrl; ?>">
			<?php if (isset($errorMessage)) { ?>
			<div class="alert alert-danger"><?php echo $errorMessage; ?></div>
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

<?php $this->stop();
