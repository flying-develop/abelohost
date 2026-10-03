# Repository Guidelines

## Project Structure & Module Organization

This is a plain PHP blog scaffold using PHP 8.4, MySQL 8.4, and Smarty 5, without frameworks. `public/index.php` is the entry point. `templates/` holds Smarty templates; `var/smarty/` stores private generated compilation and cache files. `src/` is reserved for JavaScript, SCSS, and images. Vite generates `public/build/`; never edit that directory directly.

Docker configuration lives in `docker/` and `docker-compose.yml`. MySQL data belongs outside the project, at `../abelo-storage/mysql` by default. Requirements, development stages, and setup instructions are in `README.md`. Blog features and database tables are planned for later stages.

## Build, Test, and Development Commands

Copy `.env.example` to `.env`, configure credentials and UID/GID, and create the external MySQL directory before starting.

- `docker compose build php-fpm`: build PHP with Composer and required extensions.
- `docker compose run --rm --no-deps php-fpm composer install`: install PHP dependencies.
- `docker compose run --rm --no-deps node npm install`: install frontend dependencies and generate the initial npm lock-file; use `npm ci` once the lock-file exists.
- `docker compose run --rm --no-deps node npm run build`: clean and rebuild public resources.
- `docker compose run --rm --no-deps node npm run watch`: rebuild resources on changes; refresh the browser manually.
- `docker compose up -d`: start the site at `http://localhost:8080`.

## Coding Style & Naming Conventions

Follow the mandatory rules below: tabs in code, Russian comments and PHPDoc, descriptive names, PascalCase classes, and camelCase methods. YAML requires spaces for indentation. Keep implementation simple; do not introduce frameworks. No formatter or linter is configured yet.

## Testing Guidelines

No test framework or coverage threshold is configured. Validate Compose with `docker compose config --quiet`, validate Composer, and run `php -l public/index.php` in the PHP container. Verify Smarty rendering, Bootstrap interaction, Vite build/watch, PDO connectivity, and database persistence using `README.md`. Clearly report checks blocked by the environment.

## Commit & Pull Request Guidelines

Use focused, imperative commits for each completed stage, for example `Add Docker environment and application scaffold`. Describe behavior, linked requirements, verification results, and unavailable checks in pull requests. Include screenshots for UI changes. Keep dependency lock-files in Git; never invent a lock-file when dependency resolution is unavailable.

## Security & Configuration

Never commit `.env`, credentials, dependencies, generated resources, or Smarty cache. Serve only `public/`. Use parameterized PDO queries and context-appropriate output escaping for future blog features. Keep the AI-use disclosure in `README.md` accurate as work progresses.

# AGENTS.md — правила для AI-агентов (Codex)

Данный файл содержит ОБЯЗАТЕЛЬНЫЕ правила разработки.
Несоблюдение любого пункта считается ошибкой.

1. Новый код должен быть хорошо тестируемым.
2. Для всех новых методов и классов ОБЯЗАТЕЛЬНО создавать полноценную документацию в формате phpDoc.
3. При написании кода использовать только лучшие практики, избегать антипаттернов. Код должен быть хорошо читаемым для человека.
4. Методы не должны превышать 30 строк. Запрещено уменьшать количество строк за счёт ухудшения читабельности или сложных конструкций.
5. Запрещено использовать переменные по ссылке.
6. Запрещено использовать статические методы — необходимо использовать объекты.
7. Для оформления отступов использовать ТОЛЬКО табуляцию (`\t`), пробелы запрещены. 
8. Все комментарии, пояснения к коду и phpDoc ОБЯЗАТЕЛЬНО писать на русском языке. 
9. Один контроллер — одна зона ответственности. 
10. В контроллерах ОБЯЗАТЕЛЬНО выполнять валидацию входных данных.
11. Нельзя доверять значениям, полученным через GET, POST или COOKIE, так как они могут быть подделаны. Все параметры должны получаться через `request->get` или обрабатываться индивидуально для предотвращения XSS и SQL-инъекций. Если необходимы спецсимволы, необходимо вручную удалять особо опасные символы: `"><'/`. 
12. В базе данных email, пароли, номера телефонов и другие персональные данные ОБЯЗАТЕЛЬНО должны храниться в зашифрованном виде. Нарушение данного правила является критической ошибкой. 
13. Везде, где это возможно, использовать двойные кавычки вместо одинарных.