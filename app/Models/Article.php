<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;

/** Статья с датой публикации в UTC и данными для полного просмотра. */
final readonly class Article
{
	/**
	* @param int $id Идентификатор статьи.
	* @param string $image Публичный путь изображения.
	* @param string $title Заголовок статьи.
	* @param CarbonImmutable $publishedAt Дата публикации в UTC.
	* @param string $description Краткое описание.
	* @param string $content Полный текст.
	* @param int $views Количество просмотров.
	*/
	public function __construct(
		public int $id,
		public string $image,
		public string $title,
		public CarbonImmutable $publishedAt,
		public string $description = "",
		public string $content = "",
		public int $views = 0,
	) {
	}
}
