<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

header("Content-Type: text/html; charset=UTF-8");

if (!is_file($projectRoot . "/vendor/autoload.php")) {
	http_response_code(503);
	echo "Зависимости не установлены. Выполните composer install согласно README.md.";
	return;
}

if (!is_file(__DIR__ . "/build/app.js") || !is_file(__DIR__ . "/build/app.css")) {
	http_response_code(503);
	echo "Ресурсы не собраны. Выполните npm run build согласно README.md.";
	return;
}

require $projectRoot . "/vendor/autoload.php";

try {
	$compileDirectory = $projectRoot . "/var/smarty/compile";
	$cacheDirectory = $projectRoot . "/var/smarty/cache";

	foreach ([$compileDirectory, $cacheDirectory] as $directory) {
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new RuntimeException("Не удалось создать каталог Smarty: " . $directory);
		}
	}

	$smarty = new Smarty\Smarty();
	$smarty->setTemplateDir($projectRoot . "/templates");
	$smarty->setCompileDir($compileDirectory);
	$smarty->setCacheDir($cacheDirectory);
	$smarty->setEscapeHtml(true);
	$smarty->assign("pageTitle", "Блог — каркас проекта");
	$smarty->display("home.tpl");
} catch (Throwable $exception) {
	error_log((string) $exception);
	http_response_code(500);
	echo "Не удалось загрузить страницу. Подробности доступны в журнале PHP.";
}
