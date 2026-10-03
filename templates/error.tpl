{extends file="layout.tpl"}

{block name="content"}
	<section class="error-page">
		<h1>{$pageTitle}</h1>
		<p>{$message}</p>
		<a href="/">Вернуться на главную</a>
	</section>
{/block}
