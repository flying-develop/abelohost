{foreach $articles as $article}
	<div class="col-12 col-md-6 col-lg-4">
		{include file="partials/article-card.tpl" article=$article}
	</div>
{/foreach}
{if $nextUrl !== null}
	<div class="col-12 load-more" data-load-more>
		<button class="load-more-button" type="button" data-url="{$nextUrl}">Показать ещё</button>
		<p class="load-more-error" role="alert"></p>
	</div>
{/if}
