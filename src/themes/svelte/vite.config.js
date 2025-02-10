import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
	plugins: [
		leaf({
			input: ['app/views/js/app.js', 'app/views/css/app.css'],
			refresh: true,
		}),
		svelte(),
	],
});
