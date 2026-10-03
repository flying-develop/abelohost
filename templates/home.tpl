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
		<main class="container py-5">
			<section class="card scaffold-card border-0 shadow-sm">
				<div class="card-body p-4 p-md-5">
					<div class="scaffold-logo mb-4" aria-hidden="true"></div>
					<h1 class="h2 mb-3">Каркас блога готов</h1>
					<p class="text-secondary">Страница сформирована Smarty. Стили и JavaScript собраны Vite.</p>
					<button class="btn btn-primary" type="button" data-bs-toggle="collapse"
						data-bs-target="#scaffold-details" aria-expanded="false" aria-controls="scaffold-details">
						Проверить Bootstrap
					</button>
					<div class="collapse" id="scaffold-details">
						<div class="alert alert-success mt-3 mb-0">
							JavaScript работает. Категории и статьи появятся на следующих этапах.
						</div>
					</div>
				</div>
			</section>
		</main>
	</body>
</html>
