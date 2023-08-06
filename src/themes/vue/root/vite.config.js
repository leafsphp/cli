import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
	plugins: [
		leaf({
			input: ["app/views/js/app.jsx"],
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
