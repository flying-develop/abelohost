<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Repositories\CategoryRepository;

/** Подготавливает категории и карточки для представления главной. */
final class HomeService
{
	/** @param CategoryRepository $categories Репозиторий категорий. */
	public function __construct(private readonly CategoryRepository $categories)
	{
	}

	/** @return list<array{id: int, name: string, articles: list<array>}> Блоки категорий. */
	public function getCategoryBlocks(): array
	{
		$blocks = [];
		foreach ($this->categories->findWithLatestArticles() as $category) {
			$articles = [];
			foreach ($category->articles as $article) {
				$articles[] = $this->articleCard($article);
			}
			$blocks[] = ["id" => $category->id, "name" => $category->name, "articles" => $articles];
		}
		return $blocks;
	}

	/**
	* @param Article $article Статья с датой публикации.
	* @return array{id: int, image: string, title: string, date: string, datetime: string} Данные карточки.
	*/
	private function articleCard(Article $article): array
	{
		return [
			"id" => $article->id,
			"image" => $article->image,
			"title" => $article->title,
			"date" => $article->publishedAt->format("d.m.Y"),
			"datetime" => $article->publishedAt->toIso8601String(),
		];
	}
}
