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

	/** @return list<Category> Непустые категории с максимум тремя статьями. */
	public function findWithLatestArticles(): array
	{
		$groups = [];
		foreach ($this->connection->query($this->latestArticlesSql()) as $row) {
			$id = (int) $row["category_id"];
			$groups[$id]["name"] = $row["category_name"];
			$groups[$id]["articles"][] = new Article(
				(int) $row["id"],
				$row["image"],
				$row["title"],
				new CarbonImmutable($row["published_at"], "UTC"),
			);
		}
		$categories = [];
		foreach ($groups as $id => $group) {
			$categories[] = new Category($id, $group["name"], $group["articles"]);
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
					a.id, a.image, a.title, a.published_at,
					ROW_NUMBER() OVER (
						PARTITION BY c.id ORDER BY a.published_at DESC, a.id DESC
					) AS article_rank
				FROM categories AS c
				INNER JOIN article_categories AS ac ON ac.category_id = c.id
				INNER JOIN articles AS a ON a.id = ac.article_id
			) AS ranked_articles
			WHERE article_rank <= 3
			ORDER BY category_id, published_at DESC, id DESC
			SQL;
	}
}
