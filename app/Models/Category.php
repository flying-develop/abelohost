<?php

declare(strict_types=1);

namespace App\Models;

/** Категория с последними статьями для главной страницы. */
final readonly class Category
{
	/**
	* @param int $id Идентификатор категории.
	* @param string $name Название категории.
	* @param list<Article> $articles Последние статьи категории.
	*/
	public function __construct(
		public int $id,
		public string $name,
		public array $articles,
	) {
	}
}
