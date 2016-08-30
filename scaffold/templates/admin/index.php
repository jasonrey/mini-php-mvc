<?php
!defined('MINI_EXEC') && die('No access.');

$this->using('common/html');

$this->start('body'); ?>

<p>Admin page</p>

<form class="logout-form" role="form" method="post" action="<?php echo $actionUrl; ?>">
	<button>Logout</button>
</form>

<?php $this->stop();
