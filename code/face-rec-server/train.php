<?php

// Include configuration file
require_once 'config.php';

if (empty($argv[1])) {
	die("Usage: {$argv[0]} path");
}

$ch = curl_init();
$data = array(
    'access_token' => Config::FACE_REC_SECRET
);
curl_setopt($ch, CURLOPT_URL, Config::URL . 'ninja.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($code == 200) {
	trim($response);
	$lines = split("\n", $response);
	$path = $argv[1];
	foreach ($lines as $line) {
		preg_replace('@\s*@', '', $line);
		if (! empty($line)) {
			$group = split(',', $line);
			$gid = array_shift($group);
			$command = "cd " . $path . " && ./face-train " . $gid . " '" . implode(',', $group) . "'\n";
			print $command;
			shell_exec($command);
		}
	}
}
curl_close($ch);


?>