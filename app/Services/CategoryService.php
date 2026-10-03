<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Views\ArticlePresenter;

/** Подготавливает категорию и очередную порцию карточек. */
final class CategoryService
{
	public const PAGE_SIZE = 12;

	/**
	* @param CategoryRepository $categories Репозиторий категорий.
	* @param ArticleRepository $articles Репозиторий статей.
	* @param ArticlePresenter $presenter Подготовка карточек статей.
	*/
	public function __construct(
		private readonly CategoryRepository $categories,
		private readonly ArticleRepository $articles,
		private readonly ArticlePresenter $presenter,
	) {
	}

	/**
	* @param int $categoryId Проверенный идентификатор категории.
	* @param int $page Проверенный номер страницы.
	* @return array{category: \App\Models\Category, articles: array<array>, nextUrl: ?string}|null Данные представления.
	*/
	public function getPage(int $categoryId, int $page): ?array
	{
		$category = $this->categories->findById($categoryId);
		if ($category === null) {
			return null;
		}
		$articles = $this->articles->findByCategory(
			$categoryId, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE + 1,
		);
		$hasMore = count($articles) > self::PAGE_SIZE;
		return [
			"category" => $category,
			"articles" => $this->presenter->presentMany(array_slice($articles, 0, self::PAGE_SIZE)),
			"nextUrl" => $hasMore ? "/categories/" . $categoryId . "/articles?page=" . ($page + 1) : null,
		];
	}
}
