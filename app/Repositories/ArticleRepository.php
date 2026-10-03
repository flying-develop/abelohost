<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use Carbon\CarbonImmutable;
use PDO;

/** Читает статьи категории порциями в порядке создания. */
final class ArticleRepository
{
	/** @param PDO $connection Соединение MySQL в UTC. */
	public function __construct(private readonly PDO $connection)
	{
	}

	/**
	* @param int $categoryId Идентификатор категории.
	* @param int $offset Количество пропускаемых статей.
	* @param int $limit Максимальное количество записей.
	* @return array<Article> Статьи по времени создания и идентификатору по убыванию.
	*/
	public function findByCategory(int $categoryId, int $offset, int $limit): array
	{
		$statement = $this->connection->prepare($this->categoryArticlesSql());
		$statement->bindValue(":category", $categoryId, PDO::PARAM_INT);
		$statement->bindValue(":offset", $offset, PDO::PARAM_INT);
		$statement->bindValue(":limit", $limit, PDO::PARAM_INT);
		$statement->execute();
		$articles = [];
		foreach ($statement as $row) {
			$articles[] = new Article(
				(int) $row["id"], $row["image"], $row["title"], new CarbonImmutable($row["published_at"], "UTC"),
			);
		}
		return $articles;
	}

	/** @return string Запрос порции статей одной категории. */
	private function categoryArticlesSql(): string
	{
		return <<<'SQL'
			SELECT a.id, a.image, a.title, a.published_at
			FROM articles AS a
			INNER JOIN article_categories AS ac ON ac.article_id = a.id
			WHERE ac.category_id = :category
			ORDER BY a.created_at DESC, a.id DESC
			LIMIT :limit OFFSET :offset
			SQL;
	}
}
