<?php

declare(strict_types=1);

namespace App\Views;

use RuntimeException;
use Smarty\Smarty;

/** Настраивает Smarty и формирует HTML представлений. */
final class SmartyView
{
	private readonly Smarty $smarty;

	/** @param string $projectRoot Абсолютный путь к проекту. */
	public function __construct(string $projectRoot)
	{
		$this->smarty = new Smarty();
		$this->smarty->setTemplateDir($projectRoot . "/templates");
		$this->smarty->setCompileDir($this->directory($projectRoot . "/var/smarty/compile"));
		$this->smarty->setCacheDir($this->directory($projectRoot . "/var/smarty/cache"));
		$this->smarty->setEscapeHtml(true);
	}

	/**
	* @param string $template Имя шаблона, заданное приложением.
	* @param array<string, mixed> $data Данные представления.
	* @return string Полностью сформированный HTML.
	*/
	public function render(string $template, array $data): string
	{
		$page = $this->smarty->createTemplate($template);
		$page->assign($data);
		return $page->fetch();
	}

	/**
	* @param string $path Путь служебного каталога.
	* @return string Подготовленный путь.
	* @throws RuntimeException Если каталог нельзя создать.
	*/
	private function directory(string $path): string
	{
		if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
			throw new RuntimeException("Не удалось создать каталог Smarty: " . $path);
		}
		return $path;
	}
}
