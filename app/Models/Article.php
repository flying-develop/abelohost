<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;

/** Карточка статьи с датой публикации в UTC. */
final readonly class Article
{
	/**
	* @param int $id Идентификатор статьи.
	* @param string $image Публичный путь изображения.
	* @param string $title Заголовок статьи.
	* @param CarbonImmutable $publishedAt Дата публикации в UTC.
	*/
	public function __construct(
		public int $id,
		public string $image,
		public string $title,
		public CarbonImmutable $publishedAt,
	) {
	}
}
