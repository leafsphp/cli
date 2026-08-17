import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
	plugins: [
		leaf({
			input: ["views/js/app.js"],
			refresh: true,
		}),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
	],
});
