<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

/** Создаёт соединение MySQL из окружения приложения. */
final class ConnectionFactory
{
	/**
	* Создаёт PDO с настоящими подготовленными запросами и временем UTC.
	* @return PDO Соединение с базой данных.
	*/
	public function create(): PDO
	{
		$dsn = "mysql:host=" . $this->environment("DB_HOST")
			. ";port=" . $this->environment("DB_PORT")
			. ";dbname=" . $this->environment("DB_DATABASE") . ";charset=utf8mb4";
		$connection = new PDO($dsn, $this->environment("DB_USERNAME"), $this->environment("DB_PASSWORD"), [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		]);
		$connection->exec("SET time_zone = '+00:00'");
		return $connection;
	}

	/**
	* @param string $name Имя обязательной переменной окружения.
	* @return string Значение переменной.
	* @throws RuntimeException При отсутствии конфигурации.
	*/
	private function environment(string $name): string
	{
		$value = getenv($name);
		if ($value === false || $value === "") {
			throw new RuntimeException("Не задана переменная окружения: " . $name);
		}
		return $value;
	}
}
