<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Views\ArticlePresenter;

/** Подготавливает полный просмотр статьи и похожие карточки. */
final class ArticleService
{
	/**
	* @param ArticleRepository $articles Репозиторий статей.
	* @param CategoryRepository $categories Репозиторий категорий.
	* @param ArticlePresenter $presenter Подготовка карточек и дат.
	*/
	public function __construct(
		private readonly ArticleRepository $articles,
		private readonly CategoryRepository $categories,
		private readonly ArticlePresenter $presenter,
	) {
	}

	/**
	* @param int $id Проверенный идентификатор статьи.
	* @param bool $countView Нужно ли увеличить просмотры.
	* @return array<string, mixed>|null Данные страницы либо отсутствие статьи.
	*/
	public function getPage(int $id, bool $countView): ?array
	{
		if ($countView && !$this->articles->incrementViews($id)) {
			return null;
		}
		$article = $this->articles->findById($id);
		if ($article === null) {
			return null;
		}
		return [
			"article" => $article,
			"publication" => $this->presenter->present($article),
			"paragraphs" => $this->paragraphs($article->content),
			"categories" => $this->categories->findByArticle($id),
			"related" => $this->presenter->presentMany($this->articles->findRelated($id)),
			"pageTitle" => $article->title . " — Блог",
		];
	}

	/**
	* @param string $content Обычный текст статьи.
	* @return array<string> Абзацы с сохранёнными одиночными переносами.
	*/
	private function paragraphs(string $content): array
	{
		$text = trim(str_replace(["\r\n", "\r"], "\n", $content));
		return $text === "" ? [] : (preg_split("/\n[\t ]*\n(?:[\t ]*\n)*/u", $text) ?: []);
	}
}
