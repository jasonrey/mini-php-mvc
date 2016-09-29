<?php
!defined('MINI_EXEC') && die('No access.');

$this->using('common/html');

$this->start('body'); ?>

<div class="section section-login-form">
	<form class="login-form" role="form" method="post" action="<?php echo $actionUrl; ?>">
		<h2>Admin</h2>
		<div class="form-group">
			<label class="form-label" for="username">Username</label>
			<input class="form-input" type="text" name="username" />
		</div>

		<div class="form-group">
			<label class="form-label" for="username">Password</label>
			<input class="form-input" type="password" name="password" autocomplete="off" />
		</div>

		<input type="hidden" name="ref" value="<?php echo $ref; ?>" />

		<div class="form-actions">
			<button class="form-button" type="submit">Login</button>
		</div>
	</form>
</div>

<?php $this->stop();
