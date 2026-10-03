<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Views\ArticlePresenter;

/** Подготавливает категории и карточки для представления главной. */
final class HomeService
{
	/**
	* @param CategoryRepository $categories Репозиторий категорий.
	* @param ArticlePresenter $presenter Подготовка карточек статей.
	*/
	public function __construct(
		private readonly CategoryRepository $categories,
		private readonly ArticlePresenter $presenter,
	) {
	}

	/** @return array<array{id: int, name: string, articles: array<array>}> Блоки категорий. */
	public function getCategoryBlocks(): array
	{
		$blocks = [];
		foreach ($this->categories->findWithLatestArticles() as $category) {
			$blocks[] = [
				"id" => $category->id,
				"name" => $category->name,
				"articles" => $this->presenter->presentMany($category->articles),
			];
		}
		return $blocks;
	}
}
