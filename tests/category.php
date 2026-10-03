<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Database\ConnectionFactory;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use App\Views\ArticlePresenter;
use App\Views\SmartyView;

require dirname(__DIR__) . "/vendor/autoload.php";

/**
* @param bool $condition Выполнено ли проверяемое условие.
* @param string $message Описание сценария.
* @return void
* @throws RuntimeException При нарушении ожидаемого поведения.
*/
function ensureCategory(bool $condition, string $message): void
{
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$connection = (new ConnectionFactory())->create();
$articles = new ArticleRepository($connection);
$categories = new CategoryRepository($connection);
$service = new CategoryService($categories, $articles, new ArticlePresenter());
$controller = new CategoryController($service, new SmartyView(dirname(__DIR__)));
$connection->beginTransaction();
try {
	$insertCategory = $connection->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
	$insertArticle = $connection->prepare(
		"INSERT INTO articles (image, title, description, content, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?)"
	);
	$insertLink = $connection->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
	$articleIds = [];
	for ($number = 1; $number <= 25; $number++) {
		$insertArticle->execute([
			"/data/articles/anemone_flower_macro_1706551_1280x720.jpg",
			"<script>Статья</script>", "Описание", "Текст",
			"2024-01-" . str_pad((string) (26 - $number), 2, "0", STR_PAD_LEFT) . " 12:00:00",
			"2021-01-" . str_pad((string) (int) ceil($number / 2), 2, "0", STR_PAD_LEFT) . " 12:00:00",
		]);
		$articleIds[] = (int) $connection->lastInsertId();
	}
	$categoryIds = [];
	foreach ([0, 3, 12, 13, 24, 25] as $count) {
		$insertCategory->execute(["<script>Категория</script>", "<script>Описание</script>"]);
		$id = (int) $connection->lastInsertId();
		$categoryIds[$count] = $id;
		foreach (array_slice($articleIds, 0, $count) as $articleId) {
			$insertLink->execute([$articleId, $id]);
		}
		$expected = array_reverse(array_slice($articleIds, 0, $count));
		$seen = [];
		for ($page = 1; $page <= max(1, (int) ceil($count / 12)); $page++) {
			$data = $service->getPage($id, $page);
			$ids = array_column($data["articles"], "id");
			ensureCategory($ids === array_slice($expected, ($page - 1) * 12, 12), "Порции и порядок created_at, id");
			$seen = array_merge($seen, $ids);
			$hasMore = $page * 12 < $count;
			$url = $hasMore ? "/categories/" . $id . "/articles?page=" . ($page + 1) : null;
			ensureCategory($data["nextUrl"] === $url, "Следующая порция и окончание списка");
			$html = $controller->show((string) $id, (string) $page, true);
			ensureCategory(http_response_code() === 200, "HTTP 200 для порции");
			ensureCategory(substr_count($html, 'class="article-card"') === count($ids), "Число карточек в HTML");
			ensureCategory(str_contains($html, "data-load-more") === $hasMore, "Кнопка только при наличии статей");
			ensureCategory(!str_contains($html, "<!doctype") && !str_contains($html, "site-header"), "Порция без макета");
			ensureCategory(!str_contains($html, "<script>Статья</script>"), "Экранирование карточек");
		}
		ensureCategory($seen === $expected && count(array_unique($seen)) === $count, "Все статьи без повторов");
		$html = $controller->show((string) $id);
		ensureCategory(str_contains($html, "<!doctype html>"), "Полная страница с макетом");
		ensureCategory(str_contains($html, "&lt;script&gt;Описание&lt;/script&gt;"), "Экранирование описания");
		ensureCategory(str_contains($html, "&lt;script&gt;Категория&lt;/script&gt;"), "Экранирование названия");
		ensureCategory(str_contains($html, "Статей пока нет.") === ($count === 0), "Пустая категория");
		ensureCategory(trim($controller->show((string) $id, "100", true)) === "", "Порция за концом списка");
	}

	$id = (string) $categoryIds[25];
	foreach (["0", "-1", "1.5", "abc", "1 OR 1=1", [], "", "9223372036854775807"] as $page) {
		$controller->show($id, $page, true);
		ensureCategory(http_response_code() === 400, "Некорректный номер страницы даёт 400");
	}
	foreach (["0", "-1", "abc", "1 OR 1=1", "4294967296"] as $invalidId) {
		$controller->show($invalidId);
		ensureCategory(http_response_code() === 404, "Некорректный ID даёт 404");
	}
	$missingId = (string) ($connection->query("SELECT MAX(id) FROM categories")->fetchColumn() + 1);
	$controller->show($missingId, "1", true);
	ensureCategory(http_response_code() === 404, "Отсутствующая категория даёт 404");
	ensureCategory($service->getPage((int) $missingId, 1) === null, "Отсутствующая категория в сервисе");
	http_response_code(200);
	echo "Проверены категории с 0/3/12/13/24/25 статьями, created_at, порции, кнопки, экранирование и HTTP-ошибки.", PHP_EOL;
} finally {
	$connection->rollBack();
}
