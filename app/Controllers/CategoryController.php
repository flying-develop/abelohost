<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CategoryService;
use App\Views\SmartyView;

/** Отвечает за страницу категории и HTML-порции её статей. */
final class CategoryController
{
	/**
	* @param CategoryService $category Сервис категории.
	* @param SmartyView $view Представление Smarty.
	*/
	public function __construct(private readonly CategoryService $category, private readonly SmartyView $view)
	{
	}

	/**
	* @param string $categoryId Идентификатор из маршрута.
	* @param mixed $page Номер страницы из запроса.
	* @param bool $fragment Требуется ли только HTML-порция.
	* @return string Страница, порция карточек или сообщение об ошибке.
	*/
	public function show(string $categoryId, mixed $page = "1", bool $fragment = false): string
	{
		$id = $this->positiveInteger($categoryId, 4294967295);
		if ($id === null) {
			return $this->error(404, "Категория не найдена.", $fragment);
		}
		$pageNumber = $this->positiveInteger($page, intdiv(PHP_INT_MAX, CategoryService::PAGE_SIZE));
		if ($pageNumber === null) {
			return $this->error(400, "Некорректный номер страницы.", $fragment);
		}
		$data = $this->category->getPage($id, $pageNumber);
		if ($data === null) {
			return $this->error(404, "Категория не найдена.", $fragment);
		}
		http_response_code(200);
		$data["pageTitle"] = $data["category"]->name . " — Блог";
		return $this->view->render($fragment ? "partials/category-articles.tpl" : "category.tpl", $data);
	}

	/**
	* @param mixed $value Входное значение.
	* @param int $maximum Максимальное безопасное значение.
	* @return int|null Проверенное положительное целое либо отсутствие допустимого значения.
	*/
	private function positiveInteger(mixed $value, int $maximum): ?int
	{
		if (!is_string($value) && !is_int($value)) {
			return null;
		}
		if (!preg_match("/^[1-9][0-9]*$/D", (string) $value)) {
			return null;
		}
		$result = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => $maximum]]);
		return $result === false ? null : $result;
	}

	/**
	* @param int $status HTTP-статус ошибки.
	* @param string $message Сообщение пользователю.
	* @param bool $fragment Требуется ли ответ без макета.
	* @return string Экранированное сообщение об ошибке.
	*/
	private function error(int $status, string $message, bool $fragment): string
	{
		http_response_code($status);
		return $this->view->render($fragment ? "partials/request-error.tpl" : "error.tpl", [
			"pageTitle" => $status === 404 ? "Категория не найдена" : "Некорректный запрос",
			"message" => $message,
		]);
	}
}
