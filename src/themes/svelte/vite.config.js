import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
	plugins: [
		leaf({
			input: ['views/js/app.js'],
			refresh: true,
		}),
		svelte(),
	],
});
