import { defineConfig } from "vite";
import leaf from "@leafphp/vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        leaf({
            input: ["views/css/app.css"],
            refresh: true,
        }),
    ],
});
