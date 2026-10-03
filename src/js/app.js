import "vite/modulepreload-polyfill";
import "../scss/app.scss";
import { loadMore } from "./load-more.js";

document.addEventListener("click", (event) => {
	const button = event.target.closest(".load-more-button");
	if (button) loadMore(button);
});
