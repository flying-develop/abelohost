<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Database\ConnectionFactory;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use App\Services\HomeService;
use App\Views\ArticlePresenter;
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
	$isHome = in_array($path, ["/", "/index.php"], true);
	$isCategory = is_string($path) && preg_match("~^/categories/([^/]+)(/articles)?$~D", $path, $route) === 1;
	if (!$isHome && !$isCategory) {
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
	$articles = new ArticleRepository($connection);
	$categories = new CategoryRepository($connection);
	$presenter = new ArticlePresenter();
	if ($isHome) {
		$controller = new HomeController(new HomeService($categories, $presenter), $view);
		echo $controller->index();
		return;
	}
	$service = new CategoryService($categories, $articles, $presenter);
	$controller = new CategoryController($service, $view);
	echo $controller->show($route[1], $_GET["page"] ?? "1", ($route[2] ?? "") === "/articles");
} catch (Throwable $exception) {
	error_log((string) $exception);
	http_response_code(500);
	echo "Не удалось загрузить страницу. Попробуйте позже.";
}
