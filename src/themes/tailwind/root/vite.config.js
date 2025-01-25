import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        leaf({
            input: ["js/app.js", "css/app.css"],
            refresh: true,
        }),
    ],
});
