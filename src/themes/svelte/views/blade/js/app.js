import { createInertiaApp } from "@inertiajs/svelte";
import { resolvePageComponent } from "@leafphp/vite-plugin/inertia-helpers";

const appName = import.meta.env.VITE_APP_NAME || "Leaf PHP";

createInertiaApp({
	title: (title) => `${title} - ${appName}`,
	resolve: (name) =>
		resolvePageComponent(`./Pages/${name}.svelte`,
			import.meta.glob('./Pages/**/*.svelte', { eager: true })
		),
	//or with persistent layouts
	// {// setting the default page layout
	// 	const pages = import.meta.glob('./Pages/**/*.svelte', { eager: true })
	// 	let page = pages[`./Pages/${name}.svelte`]
	// 	return { default: page.default, layout: page.layout || Layout }
	// },
	setup({ el, App, props, plugin }) {
		new App({ target: el, props })
	},
	progress: {
		color: "#4B5563",
	},
});
