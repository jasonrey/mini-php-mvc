<?php
!defined('MINI_EXEC') && die('No access.');
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no, minimal-ui" />

	<?php $this->block('pre-meta'); ?>

	<?php if (!empty($meta)) { ?>
		<?php foreach ($meta as $m) { ?>
			<?php if (isset($m['property'])) { ?>
			<meta property="<?php echo $m['property']; ?>" content="<?php echo $m['content']; ?>" />
			<?php } else { ?>
			<meta name="<?php echo $m['name']; ?>" content="<?php echo $m['content']; ?>" />
			<?php } ?>
		<?php } ?>
	<?php } ?>

	<?php $this->block('post-meta'); ?>

	<?php $this->block('pre-googlefont'); ?>

	<?php if (!empty($googlefont)) { ?>
		<?php if (is_array($googlefont)) { ?>
		<?php foreach ($googlefont as $font) { ?>
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=<?php echo $font; ?>" />
		<?php } ?>
		<?php } ?>

		<?php if (is_string($googlefont)) { ?>
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=<?php echo $googlefont; ?>" />
		<?php } ?>
	<?php } ?>

	<?php $this->block('post-googlefont'); ?>

	<?php $this->block('pre-css'); ?>

	<?php if (!empty($css)) { ?>
		<?php foreach ($css as $file) { ?>
			<?php if (substr($file, 0, 2) === '//' || substr($file, 0, 4) === 'http') { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $file; ?>" />
			<?php } else { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $base; ?>assets/css/<?php echo $file; ?>.css" />
			<?php } ?>
		<?php } ?>
	<?php } ?>

	<?php $this->block('post-css'); ?>

	<?php $this->block('pre-static'); ?>

	<?php if (!empty($static)) { ?>
		<?php foreach ($static as $file) { ?>
			<script type="text/javascript" src="<?php echo $base; ?>assets/static/<?php echo $file; ?>.js"></script>
		<?php } ?>
	<?php } ?>

	<?php $this->block('post-static'); ?>

	<?php $this->block('pre-js'); ?>

	<?php if (!empty($js)) { ?>
		<?php foreach ($js as $file) { ?>
			<?php if (substr($file, 0, 2) === '//' || substr($file, 0, 4) === 'http') { ?>
			<script type="text/javascript" src="<?php echo $file; ?>"></script>
			<?php } else { ?>
			<script type="text/javascript" src="<?php echo $base; ?>assets/js/<?php echo $file; ?>.js"></script>
			<?php } ?>
		<?php } ?>
	<?php } ?>

	<?php $this->block('post-js'); ?>

	<title><?php echo !empty($pagetitle) ? $pagetitle : ''; ?></title>
</head>
<body <?php if (!empty($env) && $env === 'development') { ?>data-development="1"<?php } ?>>
<?php $this->block('body'); ?>
</body>
</html>
