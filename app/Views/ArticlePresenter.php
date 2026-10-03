<?php

declare(strict_types=1);

namespace App\Views;

use App\Models\Article;

/** Подготавливает данные одинаковых карточек для всех страниц. */
final class ArticlePresenter
{
	/**
	* @param Article $article Статья с датой публикации.
	* @return array{id: int, image: string, title: string, date: string, datetime: string} Данные карточки.
	*/
	public function present(Article $article): array
	{
		return [
			"id" => $article->id,
			"image" => $article->image,
			"title" => $article->title,
			"date" => $article->publishedAt->format("d.m.Y"),
			"datetime" => $article->publishedAt->toIso8601String(),
		];
	}

	/**
	* @param array<Article> $articles Статьи для отображения.
	* @return array<array{id: int, image: string, title: string, date: string, datetime: string}> Карточки.
	*/
	public function presentMany(array $articles): array
	{
		return array_map($this->present(...), $articles);
	}
}
