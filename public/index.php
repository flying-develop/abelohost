<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Database\ConnectionFactory;
use App\Repositories\CategoryRepository;
use App\Services\HomeService;
use App\Views\SmartyView;

$projectRoot = dirname(__DIR__);
header("Content-Type: text/html; charset=UTF-8");

if (!is_file($projectRoot . "/vendor/autoload.php")) {
	http_response_code(503);
	echo "Зависимости не установлены. Выполните composer install согласно README.md.";
	return;
}

require $projectRoot . "/vendor/autoload.php";

try {
	$view = new SmartyView($projectRoot);
	$path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
	if (!in_array($path, ["/", "/index.php"], true)) {
		http_response_code(404);
		echo $view->render("not-found.tpl", ["pageTitle" => "Страница не найдена"]);
		return;
	}
	if (!is_file(__DIR__ . "/build/app.js") || !is_file(__DIR__ . "/build/app.css")) {
		http_response_code(503);
		echo "Ресурсы не собраны. Выполните npm run build согласно README.md.";
		return;
	}
	$connection = (new ConnectionFactory())->create();
	$repository = new CategoryRepository($connection);
	$controller = new HomeController(new HomeService($repository), $view);
	echo $controller->index();
} catch (Throwable $exception) {
	error_log((string) $exception);
	http_response_code(500);
	echo "Не удалось загрузить страницу. Попробуйте позже.";
}
