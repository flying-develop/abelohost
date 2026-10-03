<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use App\Models\Category;
use Carbon\CarbonImmutable;
use PDO;

/** Читает категории и последние статьи из MySQL. */
final class CategoryRepository
{
	/** @param PDO $connection Соединение с MySQL в UTC. */
	public function __construct(private readonly PDO $connection)
	{
	}

	/** @return array<Category> Непустые категории с максимум тремя статьями. */
	public function findWithLatestArticles(): array
	{
		$groups = [];
		foreach ($this->connection->query($this->latestArticlesSql()) as $row) {
			$id = (int) $row["category_id"];
			$groups[$id]["name"] = $row["category_name"];
			$groups[$id]["articles"][] = new Article(
				(int) $row["id"], $row["image"], $row["title"], new CarbonImmutable($row["published_at"], "UTC"),
			);
		}
		$categories = [];
		foreach ($groups as $id => $group) {
			$categories[] = new Category($id, $group["name"], $group["articles"]);
		}
		return $categories;
	}

	/**
	* @param int $id Идентификатор категории.
	* @return Category|null Категория без статей либо отсутствие записи.
	*/
	public function findById(int $id): ?Category
	{
		$statement = $this->connection->prepare("SELECT id, name, description FROM categories WHERE id = :id");
		$statement->bindValue(":id", $id, PDO::PARAM_INT);
		$statement->execute();
		$row = $statement->fetch();
		return $row === false ? null : new Category((int) $row["id"], $row["name"], [], $row["description"]);
	}

	/**
	* @param int $articleId Идентификатор статьи.
	* @return array<Category> Категории статьи в порядке идентификаторов.
	*/
	public function findByArticle(int $articleId): array
	{
		$statement = $this->connection->prepare(
			"SELECT c.id, c.name, c.description FROM categories AS c "
			. "INNER JOIN article_categories AS ac ON ac.category_id = c.id WHERE ac.article_id = :article ORDER BY c.id"
		);
		$statement->bindValue(":article", $articleId, PDO::PARAM_INT);
		$statement->execute();
		$categories = [];
		foreach ($statement as $row) {
			$categories[] = new Category((int) $row["id"], $row["name"], [], $row["description"]);
		}
		return $categories;
	}

	/** @return string Запрос с ограничением числа статей внутри каждой категории. */
	private function latestArticlesSql(): string
	{
		return <<<'SQL'
			SELECT category_id, category_name, id, image, title, published_at
			FROM (
				SELECT c.id AS category_id, c.name AS category_name,
					a.id, a.image, a.title, a.published_at, a.created_at,
					ROW_NUMBER() OVER (
						PARTITION BY c.id ORDER BY a.created_at DESC, a.id DESC
					) AS article_rank
				FROM categories AS c
				INNER JOIN article_categories AS ac ON ac.category_id = c.id
				INNER JOIN articles AS a ON a.id = ac.article_id
			) AS ranked_articles
			WHERE article_rank <= 3
			ORDER BY category_id, created_at DESC, id DESC
			SQL;
	}
}
