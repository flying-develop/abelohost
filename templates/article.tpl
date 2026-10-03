{extends file="layout.tpl"}

{block name="content"}
	<article class="article-page">
		<img class="article-cover" src="{$article->image}" alt="{$article->title}" width="1280" height="720">
		<div class="article-body">
			<h1 class="article-heading">{$article->title}</h1>
			<time class="article-date" datetime="{$publication.datetime}">{$publication.date}</time>
			{if $categories}
				<nav class="article-categories" aria-label="Категории статьи">
					{foreach $categories as $category}
						<a href="/categories/{$category->id}">{$category->name}</a>
					{/foreach}
				</nav>
			{/if}
			{if $article->description}
				<p class="article-description">{$article->description}</p>
			{/if}
			<div class="article-content">
				{foreach $paragraphs as $paragraph}
					<p>{$paragraph}</p>
				{/foreach}
			</div>
			<p class="article-views">Просмотров: {$article->views}</p>
		</div>
	</article>
	{if $related}
		<section class="related-section" aria-labelledby="related-title">
			<h2 id="related-title" class="category-title">Похожие статьи</h2>
			<div class="row g-4 g-lg-5">
				{foreach $related as $article}
					<div class="col-12 col-md-6 col-lg-4">
						{include file="partials/article-card.tpl" article=$article}
					</div>
				{/foreach}
			</div>
		</section>
	{/if}
{/block}
