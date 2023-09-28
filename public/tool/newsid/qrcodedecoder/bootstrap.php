<?php

require_once 'Zxing/Common/customFunctions.php';

spl_autoload_register(function ($className) {
	$filePath = __DIR__ . DIRECTORY_SEPARATOR . $className;
	$filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath) . '.php';
	if (file_exists($filePath)) {
		require_once $filePath;
	}
});
