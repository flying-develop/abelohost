<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\HomeService;
use App\Views\SmartyView;

/** Отвечает за отображение главной страницы. */
final class HomeController
{
	/**
	* @param HomeService $home Сервис главной страницы.
	* @param SmartyView $view Представление Smarty.
	*/
	public function __construct(private readonly HomeService $home, private readonly SmartyView $view)
	{
	}

	/** @return string Главная страница без пользовательских параметров выборки. */
	public function index(): string
	{
		return $this->view->render("home.tpl", [
			"pageTitle" => "Блог",
			"categories" => $this->home->getCategoryBlocks(),
		]);
	}
}
