<!doctype html>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>{$pageTitle}</title>
		<link rel="stylesheet" href="/build/app.css">
		<script type="module" src="/build/app.js"></script>
	</head>
	<body>
		<a class="skip-link" href="#main-content">Перейти к содержимому</a>
		<header class="site-header">
			<div class="container">
				<a class="site-title" href="/">Блог</a>
			</div>
		</header>
		<main id="main-content" class="container site-main">
			{block name="content"}{/block}
		</main>
		<footer class="site-footer">
			<div class="container">Блог</div>
		</footer>
	</body>
</html>
