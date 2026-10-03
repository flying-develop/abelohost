{extends file="layout.tpl"}

{block name="content"}
	<section class="category-page" aria-labelledby="category-title">
		<h1 id="category-title" class="category-title">{$category->name}</h1>
		<p class="category-description">{$category->description}</p>
		<div class="row g-4 g-lg-5">
			{include file="partials/category-articles.tpl"}
		</div>
		{if !$articles}
			<p class="empty-state">Статей пока нет.</p>
		{/if}
	</section>
{/block}
