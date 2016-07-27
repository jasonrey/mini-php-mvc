<?php
!defined('SERVER_EXEC') && die('No access.');
?>
<!DOCTYPE html>
<html>
<head>
	<base href="<?php echo Config::getHTMLBase(); ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no, minimal-ui" />

	<?php if (!empty($meta)) { ?>
		<?php foreach ($meta as $m) { ?>
			<?php if (isset($m['property'])) { ?>
			<meta property="<?php echo $m['property']; ?>" content="<?php echo $m['content']; ?>" />
			<?php } else { ?>
			<meta name="<?php echo $m['name']; ?>" content="<?php echo $m['content']; ?>" />
			<?php } ?>
		<?php } ?>
	<?php } ?>

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

	<?php if (!empty($css)) { ?>
		<?php foreach ($css as $file) { ?>
			<link
				rel="stylesheet"
				type="text/css"
				href="<?php echo strpos($file, 'http') === 0 ? $file : 'assets/css/' . $file . '.css'; ?>"
			/>
		<?php } ?>
	<?php } ?>

	<?php if (!empty($static)) { ?>
		<?php foreach ($static as $file) { ?>
			<script type="text/javascript" src="assets/static/<?php echo $file; ?>.js"></script>
		<?php } ?>
	<?php } ?>

	<?php if (!empty($js)) { ?>
		<?php foreach ($js as $file) { ?>
			<script
				type="text/javascript"
				src="<?php echo strpos($file, 'http') === 0 ? $file : 'assets/js/' . $file . '.js'; ?>"
			>
			</script>
		<?php } ?>
	<?php } ?>

	<title><?php echo !empty($pagetitle) ? $pagetitle : Config::getPageTitle(); ?></title>
</head>
<body <?php if (Config::env() === 'development') { ?>data-development="1"<?php } ?>>
<?php echo $body; ?>
</body>
</html>
