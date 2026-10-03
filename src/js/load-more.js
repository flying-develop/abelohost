export async function loadMore(button, request = fetch) {
	if (button.disabled) return;
	const container = button.closest("[data-load-more]");
	const error = container.querySelector("[role=alert]");
	const label = button.textContent;
	button.disabled = true;
	button.textContent = "Загрузка…";
	error.textContent = "";
	try {
		const response = await request(button.dataset.url, { credentials: "same-origin" });
		if (!response.ok) throw new Error("Не удалось загрузить статьи");
		const html = await response.text();
		const fragment = document.createRange().createContextualFragment(html);
		const firstLink = fragment.querySelector(".article-card-link");
		container.replaceWith(fragment);
		firstLink?.focus({ preventScroll: true });
	} catch {
		error.textContent = "Не удалось загрузить статьи. Попробуйте ещё раз.";
		button.disabled = false;
		button.textContent = label;
	}
}
