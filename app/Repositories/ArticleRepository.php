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

	/**
	* @param int $id Идентификатор статьи.
	* @return Article|null Полная статья либо отсутствие записи.
	*/
	public function findById(int $id): ?Article
	{
		$statement = $this->connection->prepare(
			"SELECT id, image, title, published_at, description, content, views FROM articles WHERE id = :id"
		);
		$statement->bindValue(":id", $id, PDO::PARAM_INT);
		$statement->execute();
		$row = $statement->fetch();
		return $row === false ? null : new Article(
			(int) $row["id"], $row["image"], $row["title"], new CarbonImmutable($row["published_at"], "UTC"),
			$row["description"], $row["content"], (int) $row["views"],
		);
	}

	/**
	* @param int $id Идентификатор статьи.
	* @return bool Найдена ли статья и увеличен ли счётчик.
	*/
	public function incrementViews(int $id): bool
	{
		$statement = $this->connection->prepare("UPDATE articles SET views = views + 1 WHERE id = :id");
		$statement->bindValue(":id", $id, PDO::PARAM_INT);
		$statement->execute();
		return $statement->rowCount() === 1;
	}

	/**
	* @param int $id Идентификатор текущей статьи.
	* @return array<Article> До трёх статей с приоритетом общих категорий.
	*/
	public function findRelated(int $id): array
	{
		$statement = $this->connection->prepare($this->relatedArticlesSql());
		$statement->bindValue(":source", $id, PDO::PARAM_INT);
		$statement->bindValue(":excluded", $id, PDO::PARAM_INT);
		$statement->execute();
		$articles = [];
		foreach ($statement as $row) {
			$articles[] = new Article(
				(int) $row["id"], $row["image"], $row["title"], new CarbonImmutable($row["published_at"], "UTC"),
			);
		}
		return $articles;
	}

	/** @return string Запрос похожих статей с дополнением последними из остальных категорий. */
	private function relatedArticlesSql(): string
	{
		return <<<'SQL'
			SELECT a.id, a.image, a.title, a.published_at
			FROM articles AS a
			WHERE a.id <> :excluded
			ORDER BY EXISTS (
				SELECT 1 FROM article_categories AS source
				INNER JOIN article_categories AS related ON related.category_id = source.category_id
				WHERE source.article_id = :source AND related.article_id = a.id
			) DESC, a.created_at DESC, a.id DESC
			LIMIT 3
			SQL;
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
