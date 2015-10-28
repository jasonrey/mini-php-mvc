<?php

!defined('SERVER_EXEC') && die('No access.');

class File
{
	public $path;
	public $filename;

	public function __construct($file = null, $filename = null)
	{
		if (!empty($file)) {
			$this->init($file, $filename);
		}
	}

	public function init($file, $filename = null)
	{
		$this->path = $file;
		$this->filename = !empty($filename) ? $filename : basename($this->path);
	}

	public function copy($target, $filename = '')
	{
		if (empty($filename)) {
			$filename = $this->filename;
		}

		if (!file_exists($target)) {
			mkdir($target, 0777, true);
		}

		if (!copy($this->path, $target . '/' . $filename)) {
			return false;
		}

		return Lib::file($target . '/' . $filename, $filename);
	}

	public function move($target, $filename = '')
	{
		if (empty($filename)) {
			$filename = $this->filename;
		}

		if (!file_exists($target)) {
			mkdir($target, 0777, true);
		}

		if (!rename($this->path, $target . '/' . $filename)) {
			return false;
		}

		$this->path = $target . '/' . $filename;
		$this->filename = $filename;
		return true;
	}

	public function delete()
	{
		return unlink($this->path);
	}

	public function write($data)
	{
		return file_put_contents($this->path, $data);
	}

	public function generateTemporaryFilename($prefix = '', $suffix = '')
	{
		$now = time();

		$filename = $this->filename;

		$segments = explode('.', $filename);

		$extension = '';

		if (count($segments) > 1) {
			$extension = array_pop($segments);
		}

		$temporaryName = $prefix . $now . '-' . implode('.', $segments) . $suffix . '.' . $extension;

		return $temporaryName;
	}
}
