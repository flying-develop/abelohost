<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Database\ConnectionFactory;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Services\ArticleService;
use App\Views\ArticlePresenter;
use App\Views\SmartyView;

require dirname(__DIR__) . "/vendor/autoload.php";

/**
* @param bool $condition Выполнено ли проверяемое условие.
* @param string $message Описание сценария.
* @return void
* @throws RuntimeException При нарушении ожидаемого поведения.
*/
function ensureArticle(bool $condition, string $message): void
{
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$connection = (new ConnectionFactory())->create();
$articles = new ArticleRepository($connection);
$categories = new CategoryRepository($connection);
$service = new ArticleService($articles, $categories, new ArticlePresenter());
$controller = new ArticleController($service, new SmartyView(dirname(__DIR__)));
$connection->beginTransaction();
try {
	$connection->exec("DELETE FROM articles");
	$connection->exec("DELETE FROM categories");
	$insertCategory = $connection->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
	$categoryIds = [];
	foreach (["<script>Категория</script>", "Вторая категория"] as $name) {
		$insertCategory->execute([$name, "Описание категории"]);
		$categoryIds[] = (int) $connection->lastInsertId();
	}
	$insertArticle = $connection->prepare(
		"INSERT INTO articles (image, title, description, content, published_at, created_at, views) VALUES (?, ?, ?, ?, ?, ?, ?)"
	);
	$ids = [];
	foreach (["2022-01-01", "2020-01-01", "2021-01-01", "2030-01-01", "2029-01-01"] as $created) {
		$insertArticle->execute([
			"/data/articles/anemone_flower_macro_1706551_1280x720.jpg",
			"<script>Заголовок</script>", "<script>Описание</script>",
			"Первый абзац\r\nВторая строка\r\n\r\n<script>Текст</script>",
			"2024-01-01 12:00:00", $created . " 12:00:00", 7,
		]);
		$ids[] = (int) $connection->lastInsertId();
	}
	$insertLink = $connection->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
	foreach ([[$ids[0], $categoryIds[0]], [$ids[0], $categoryIds[1]], [$ids[1], $categoryIds[0]],
		[$ids[2], $categoryIds[0]], [$ids[2], $categoryIds[1]]] as $link) {
		$insertLink->execute($link);
	}
	$id = (string) $ids[0];
	$data = $service->getPage($ids[0], false);
	ensureArticle(count($data["paragraphs"]) === 2, "Абзацы обычного текста");
	ensureArticle(str_contains($data["paragraphs"][0], "\nВторая строка"), "Одиночный перенос сохраняется");
	ensureArticle(array_column($data["related"], "id") === [$ids[2], $ids[1], $ids[3]], "Общие категории, дополнение и отсутствие дублей");
	ensureArticle(count($data["categories"]) === 2, "Все категории статьи");

	$controller->show($id, "HEAD");
	ensureArticle(http_response_code() === 200 && $articles->findById($ids[0])->views === 7, "HEAD без увеличения");
	$html = $controller->show($id);
	ensureArticle(http_response_code() === 200 && $articles->findById($ids[0])->views === 8, "GET увеличивает на один");
	ensureArticle(str_contains($html, "Просмотров: 8"), "Отображение увеличенного счётчика");
	ensureArticle(!str_contains($html, "<script>"), "Экранирование текста и метаданных");
	ensureArticle(str_contains($html, "&lt;script&gt;Текст&lt;/script&gt;"), "Экранированный полный текст");
	ensureArticle(str_contains($html, "&lt;script&gt;Описание&lt;/script&gt;"), "Краткое описание");
	ensureArticle(str_contains($html, "/categories/" . $categoryIds[0]), "Ссылка на категорию");
	ensureArticle(str_contains($html, "01.01.2024"), "Дата публикации");
	ensureArticle(substr_count($html, 'class="article-card"') === 3, "Три похожие карточки");
	ensureArticle(strpos($html, "article-cover") < strpos($html, "article-heading"), "Изображение перед названием");
	ensureArticle(strpos($html, "article-content") < strpos($html, "article-views"), "Просмотры после текста");
	$controller->show($id);
	ensureArticle($articles->findById($ids[0])->views === 9, "Повторное открытие увеличивает ещё на один");
	$articles->findByCategory($categoryIds[0], 0, 12);
	$categories->findWithLatestArticles();
	$articles->findRelated($ids[0]);
	ensureArticle($articles->findById($ids[0])->views === 9, "Списки и похожие карточки не увеличивают просмотры");
	ensureArticle($articles->findById($ids[1])->views === 7, "Похожие статьи не просмотрены");
	foreach (["POST", "PUT", "DELETE", "OPTIONS"] as $method) {
		$controller->show($id, $method);
		ensureArticle(http_response_code() === 405, "Неподдерживаемый метод");
	}
	foreach (["0", "-1", "abc", "1 OR 1=1", "4294967296", (string) ($ids[4] + 1)] as $invalid) {
		$controller->show($invalid);
		ensureArticle(http_response_code() === 404, "Отсутствующая или некорректная статья");
	}
	ensureArticle($articles->findById($ids[0])->views === 9, "Ошибочные запросы не меняют счётчик");
	$connection->exec("DELETE FROM article_categories");
	ensureArticle(array_column($service->getPage($ids[0], false)["related"], "id") === [$ids[3], $ids[4], $ids[2]], "Без категорий используются последние статьи");
	$delete = $connection->prepare("DELETE FROM articles WHERE id = ?");
	foreach ([$ids[2], $ids[3], $ids[4]] as $deleted) {
		$delete->execute([$deleted]);
	}
	ensureArticle(count($articles->findRelated($ids[0])) === 1, "Меньше трёх доступных статей");
	$delete->execute([$ids[1]]);
	ensureArticle($articles->findRelated($ids[0]) === [], "Отсутствие похожих статей");
	ensureArticle(!str_contains($controller->show($id, "HEAD"), "Похожие статьи"), "Пустой блок скрывается");
	http_response_code(200);
	echo "Проверены статья, текст, категории, просмотры GET/HEAD, похожие статьи и HTTP-ошибки.", PHP_EOL;
} finally {
	$connection->rollBack();
}
