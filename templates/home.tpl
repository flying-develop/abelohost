{extends file="layout.tpl"}

{block name="content"}
	<h1 class="visually-hidden">Последние статьи блога</h1>
	{foreach $categories as $category}
		<section class="category-section" aria-labelledby="category-{$category.id}">
			<div class="category-heading">
				<h2 id="category-{$category.id}" class="category-title">{$category.name}</h2>
				<a class="category-all" href="/categories/{$category.id}">
					Все статьи <span aria-hidden="true">→</span>
					<span class="visually-hidden">категории «{$category.name}»</span>
				</a>
			</div>
			<div class="row g-4 g-lg-5">
				{foreach $category.articles as $article}
					<div class="col-12 col-md-6 col-lg-4">
						{include file="partials/article-card.tpl" article=$article}
					</div>
				{/foreach}
			</div>
		</section>
	{foreachelse}
		<p class="empty-state">Статей пока нет.</p>
	{/foreach}
{/block}
