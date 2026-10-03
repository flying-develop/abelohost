<?php

declare(strict_types=1);

namespace App\Models;

/** Категория блога с описанием и набором статей. */
final readonly class Category
{
	/**
	* @param int $id Идентификатор категории.
	* @param string $name Название категории.
	* @param array<Article> $articles Статьи категории.
	* @param string $description Описание категории.
	*/
	public function __construct(
		public int $id,
		public string $name,
		public array $articles,
		public string $description = "",
	) {
	}
}
