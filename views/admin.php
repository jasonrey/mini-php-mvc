<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminView extends View
{
	public function display()
	{
		$key = Lib::hash(Config::$adminkey);

		$cookie = Lib::cookie();

		$logged = $cookie->get($key);

		$ref = Req::get('ref');

		if (!empty($ref)) {
			if ($logged) {
				$segments = explode('/', base64_decode($ref));

				$base = array_shift($segments);
				$type = array_shift($segments);
				$subtype = array_shift($segments);

				$options = array('type' => $type);

				if (!empty($subtype)) {
					$options['subtype'] = $subtype;
				}

				Lib::redirect($base, $options);
				return;
			}

			return $this->form();
		}

		$type = Req::get('type');

		if (!$logged) {
			$options = array('type' => $type);

			$subtype = Req::get('subtype');
			if (!empty($subtype)) {
				$options['subtype'] = $subtype;
			}

			$ref = Lib::url('admin', $options);

			Lib::redirect('admin', array('ref' => urlencode(base64_encode($ref))));
			return;
		}

		if (!is_callable(array($this, $type))) {
			Lib::redirect('error');
			return;
		}

		return $this->$type();
	}

	public function form()
	{
		$ref = Req::get('ref');

		$this->set('ref', $ref);

		echo $this->includeTemplate('form');
	}
}
