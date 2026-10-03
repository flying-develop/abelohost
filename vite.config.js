import { defineConfig } from "vite";

export default defineConfig({
	base: "/build/",
	publicDir: false,
	css: {
		preprocessorOptions: {
			scss: {
				quietDeps: true,
			},
		},
	},
	build: {
		outDir: "public/build",
		emptyOutDir: true,
		cssCodeSplit: false,
		assetsInlineLimit: 0,
		rolldownOptions: {
			input: "src/js/app.js",
			output: {
				entryFileNames: "app.js",
				chunkFileNames: "chunks/[name]-[hash].js",
				assetFileNames: (asset) => asset.names.some((name) => name.endsWith(".css"))
					? "app.css"
					: "assets/[name]-[hash][extname]",
			},
		},
	},
});
