<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Views\SmartyView;

/** Отвечает за страницу статьи и учёт её открытий. */
final class ArticleController
{
	/**
	* @param ArticleService $article Сервис просмотра статьи.
	* @param SmartyView $view Представление Smarty.
	*/
	public function __construct(private readonly ArticleService $article, private readonly SmartyView $view)
	{
	}

	/**
	* @param string $articleId Идентификатор из маршрута.
	* @param string $method HTTP-метод запроса.
	* @return string Полная страница либо сообщение об ошибке.
	*/
	public function show(string $articleId, string $method = "GET"): string
	{
		header("Cache-Control: no-store");
		if (!in_array($method, ["GET", "HEAD"], true)) {
			header("Allow: GET, HEAD");
			return $this->error(405, "Метод не поддерживается.");
		}
		$id = $this->articleId($articleId);
		if ($id === null) {
			return $this->error(404, "Статья не найдена.");
		}
		$data = $this->article->getPage($id, $method === "GET");
		if ($data === null) {
			return $this->error(404, "Статья не найдена.");
		}
		http_response_code(200);
		return $this->view->render("article.tpl", $data);
	}

	/**
	* @param string $value Входной идентификатор.
	* @return int|null Допустимый идентификатор INT UNSIGNED.
	*/
	private function articleId(string $value): ?int
	{
		if (!preg_match("/^[1-9][0-9]*$/D", $value)) {
			return null;
		}
		$id = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4294967295]]);
		return $id === false ? null : $id;
	}

	/**
	* @param int $status HTTP-статус ошибки.
	* @param string $message Сообщение пользователю.
	* @return string Страница ошибки.
	*/
	private function error(int $status, string $message): string
	{
		http_response_code($status);
		return $this->view->render("error.tpl", ["pageTitle" => $message, "message" => $message]);
	}
}
