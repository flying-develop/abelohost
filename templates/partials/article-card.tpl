<article class="article-card">
	<a class="article-card-link" href="/articles/{$article.id}">
		<img class="article-image" src="{$article.image}" alt="{$article.title}"
			width="1280" height="720" loading="lazy" decoding="async">
		<div class="article-details">
			<h3 class="article-title">{$article.title}</h3>
			<time class="article-date" datetime="{$article.datetime}">{$article.date}</time>
			<span class="article-read" aria-hidden="true">Читать <span>→</span></span>
		</div>
	</a>
</article>
