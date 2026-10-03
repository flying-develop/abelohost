<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Database\ConnectionFactory;
use App\Repositories\CategoryRepository;
use App\Services\HomeService;
use App\Views\SmartyView;
use App\Views\ArticlePresenter;
use Carbon\CarbonImmutable;

require dirname(__DIR__) . "/vendor/autoload.php";

/**
* Проверяет результат сценария и прекращает проверку при ошибке.
* @param bool $condition Выполнено ли условие.
* @param string $message Описание проверяемого поведения.
* @return void
* @throws RuntimeException Если условие не выполнено.
*/
function ensure(bool $condition, string $message): void
{
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$connection = (new ConnectionFactory())->create();
$repository = new CategoryRepository($connection);
$view = new SmartyView(dirname(__DIR__));
$service = new HomeService($repository, new ArticlePresenter());
$controller = new HomeController($service, $view);

$connection->beginTransaction();
try {
	$categoryInsert = $connection->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
	$categoryIds = [];
	foreach (["Пустая", "<script>Категория</script>", "Две статьи", "Четыре статьи", "Одинаковая дата"] as $name) {
		$categoryInsert->execute([$name, "Временные данные проверки"]);
		$categoryIds[] = (int) $connection->lastInsertId();
	}
	$articleInsert = $connection->prepare(
		"INSERT INTO articles (image, title, description, content, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?)"
	);
	$articleIds = [];
	foreach (["2023-01-01", "2022-01-01", "2022-01-01", "2021-01-01", "2024-01-01", "2025-01-01"] as $date) {
		$articleInsert->execute([
			"/data/articles/anemone_flower_macro_1706551_1280x720.jpg",
			"<script>alert(1)</script>", "Описание", "Текст", "2024-01-01 12:30:00", $date . " 12:30:00",
		]);
		$articleIds[] = (int) $connection->lastInsertId();
	}
	$linkInsert = $connection->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
	$expected = [
		$categoryIds[1] => [$articleIds[4]],
		$categoryIds[2] => [$articleIds[5], $articleIds[4]],
		$categoryIds[3] => [$articleIds[0], $articleIds[2], $articleIds[1]],
		$categoryIds[4] => [$articleIds[2], $articleIds[1]],
	];
	$links = $expected;
	$links[$categoryIds[3]][] = $articleIds[3];
	foreach ($links as $categoryId => $articles) {
		foreach ($articles as $articleId) {
			$linkInsert->execute([$articleId, $categoryId]);
		}
	}

	$models = $repository->findWithLatestArticles();
	$actual = [];
	foreach ($models as $category) {
		ensure(count($category->articles) <= 3, "Не более трёх статей в категории");
		$ids = [];
		foreach ($category->articles as $article) {
			ensure($article->publishedAt instanceof CarbonImmutable, "Дата должна использовать CarbonImmutable");
			ensure($article->publishedAt->getTimezone()->getName() === "UTC", "Дата должна быть в UTC");
			$ids[] = $article->id;
		}
		$actual[$category->id] = $ids;
	}
	ensure(!isset($actual[$categoryIds[0]]), "Пустая категория не должна отображаться");
	foreach ($expected as $categoryId => $ids) {
		ensure($actual[$categoryId] === $ids, "Порядок, лимит и несколько категорий у статьи");
	}
	$orderedIds = array_keys($actual);
	$sortedIds = $orderedIds;
	sort($sortedIds);
	ensure($orderedIds === $sortedIds, "Категории должны идти по ID");

	$html = $controller->index();
	ensure(!str_contains($html, "<script>alert(1)</script>"), "Заголовки должны экранироваться");
	ensure(str_contains($html, "&lt;script&gt;Категория&lt;/script&gt;"), "Название категории должно экранироваться");
	ensure(str_contains($html, "&lt;script&gt;alert(1)&lt;/script&gt;"), "Заголовок статьи должен экранироваться");
	ensure(str_contains($html, "01.01.2024"), "Формат отображения даты");
	ensure(str_contains($html, "2024-01-01T12:30:00+00:00"), "Машиночитаемая дата UTC");
	ensure(str_contains($html, "/articles/" . $articleIds[4]), "Ссылка на статью");
	ensure(str_contains($html, "/data/articles/"), "Путь изображения контента");

	$connection->exec("DELETE FROM categories");
	ensure($repository->findWithLatestArticles() === [], "Отсутствие категорий");
	ensure(str_contains($controller->index(), "Статей пока нет."), "Сообщение при отсутствии статей");
	echo "Проверены выборка MySQL, модели Carbon, даты, ссылки, экранирование и пустая главная.", PHP_EOL;
} finally {
	$connection->rollBack();
}
