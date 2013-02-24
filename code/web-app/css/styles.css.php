<?php
header('Content-Type: text/css; charset=UTF-8');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (3600 * 24)) . ' GMT');

foreach (scandir('.') as $value) {
	if (preg_match('@\.css$@', $value)) {
		echo file_get_contents($value);
	}
}

?>