import assert from "node:assert/strict";
import test from "node:test";
import { loadMore } from "../src/js/load-more.js";

function control() {
	const error = { textContent: "" };
	const container = {
		querySelector: () => error,
		replaceWith(fragment) { this.replacement = fragment; },
	};
	return {
		error,
		container,
		button: {
			disabled: false,
			textContent: "Показать ещё",
			dataset: { url: "/categories/1/articles?page=2" },
			closest: () => container,
		},
	};
}

globalThis.document = {
	createRange: () => ({
		createContextualFragment: (html) => ({ html, querySelector: () => null }),
	}),
};

test("Порция заменяет старую кнопку, повторный клик не отправляет запрос", async () => {
	const { button, container } = control();
	let finish;
	let calls = 0;
	const request = (url, options) => {
		calls++;
		assert.equal(url, button.dataset.url);
		assert.equal(options.credentials, "same-origin");
		return new Promise((resolve) => { finish = resolve; });
	};
	const pending = loadMore(button, request);
	assert.equal(button.disabled, true);
	assert.equal(button.textContent, "Загрузка…");
	await loadMore(button, request);
	assert.equal(calls, 1);
	finish({ ok: true, text: async () => "карточки и новая кнопка" });
	await pending;
	assert.equal(container.replacement.html, "карточки и новая кнопка");
});

test("Ошибка сохраняет кнопку и допускает повторную попытку", async () => {
	const { button, container, error } = control();
	await loadMore(button, async () => { throw new Error("Нет сети"); });
	assert.equal(container.replacement, undefined);
	assert.equal(button.disabled, false);
	assert.equal(button.textContent, "Показать ещё");
	assert.match(error.textContent, /Попробуйте ещё раз/);
	await loadMore(button, async () => ({ ok: true, text: async () => "последние карточки без кнопки" }));
	assert.equal(error.textContent, "");
	assert.equal(container.replacement.html, "последние карточки без кнопки");
});

test("Ответ HTTP 500 не добавляется в список статей", async () => {
	const { button, container, error } = control();
	await loadMore(button, async () => ({ ok: false, text: async () => "ошибка сервера" }));
	assert.equal(container.replacement, undefined);
	assert.equal(button.disabled, false);
	assert.match(error.textContent, /Не удалось загрузить статьи/);
});
