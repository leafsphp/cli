import { FileMinus, Folder, FolderPlus } from 'react-feather';

export const themes = [
    {
        key: 'leaf',
        icon: FileMinus,
        name: 'BASIC LEAF THEME',
        description:
            'A basic Leaf app with a single index.php file, the simplest and fastest way to get started with Leaf. You can further customize this theme to add some extra features.',
    },
    {
        key: 'mvc',
        icon: FolderPlus,
        name: 'LEAF MVC THEME',
        description:
            'Leaf MVC is a simple MVC framework for Leaf. It provides a solid base for building complex web apps quickly. It is designed to be simple, lightweight and easy to learn.',
    },
    {
        key: 'api',
        icon: Folder,
        name: 'LEAF API THEME',
        description:
            'Leaf API is a simple MVC framework for Leaf specially crafted for building APIs. It provides a solid base for building complex APIs quickly.',
    },
];

export const additionalFrontendOptions = [
    {
        key: 'vite',
        icon: <img src="https://vitejs.dev/logo.svg" className="w-5 h-5" />,
        name: 'Leaf + Vite',
        description: 'Bundle your app assets with Vite.',
    },
    {
        key: 'tailwind',
        icon: (
            <img
                src="https://tailwindcss.com/_next/static/media/tailwindcss-mark.3c5441fc7a190fb1800d4a5c7f07ba4b1345a9c8.svg"
                className="w-5 h-5"
            />
        ),
        name: 'Tailwind CSS',
        description: 'Set up Tailwind in your Leaf app.',
    },
];

export const containers = [
    {
        key: 'none',
        icon: (
            <img
                src="https://leafphp.dev/assets/leaf3-logo-circle.5b8e60e2.png"
                className="w-5 h-5"
            />
        ),
        name: 'No Container',
        description: 'Skip containerization. You can always add it later.',
    },
    {
        key: 'docker',
        icon: (
            <img
                src="https://www.docker.com/wp-content/uploads/2023/04/cropped-Docker-favicon-192x192.png"
                className="w-5 h-5"
            />
        ),
        name: 'Docker',
        description: 'Create a Docker container for your app.',
    },
];

export const frontendFrameworks = [
    {
        key: 'none',
        icon: (
            <img
                src="https://leafphp.dev/assets/leaf3-logo-circle.5b8e60e2.png"
                className="w-5 h-5"
            />
        ),
        name: 'Use template engine',
        description:
            'Render UIs on the server using your selected templating engine',
    },
    {
        key: 'react',
        icon: (
            <img
                src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii0xMS41IC0xMC4yMzE3NCAyMyAyMC40NjM0OCI+CiAgPHRpdGxlPlJlYWN0IExvZ288L3RpdGxlPgogIDxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIyLjA1IiBmaWxsPSIjNjFkYWZiIi8+CiAgPGcgc3Ryb2tlPSIjNjFkYWZiIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiPgogICAgPGVsbGlwc2Ugcng9IjExIiByeT0iNC4yIi8+CiAgICA8ZWxsaXBzZSByeD0iMTEiIHJ5PSI0LjIiIHRyYW5zZm9ybT0icm90YXRlKDYwKSIvPgogICAgPGVsbGlwc2Ugcng9IjExIiByeT0iNC4yIiB0cmFuc2Zvcm09InJvdGF0ZSgxMjApIi8+CiAgPC9nPgo8L3N2Zz4K"
                className="w-5 h-5"
            />
        ),
        name: 'React JS',
        description: 'The library for web and native user interfaces',
    },
    {
        key: 'vue',
        icon: <img src="https://vuejs.org/logo.svg" className="w-5 h-5" />,
        name: 'Vue JS',
        description: 'The Progressive JavaScript Framework',
    },
];

export const modules = [
    {
        key: 'none',
        name: 'None',
        description: 'Add no extra modules to your app.',
    },
    {
        key: 'db',
        name: 'Database',
        description: 'Install Leaf DB in your app.',
    },
    {
        key: 'auth',
        name: 'Authentication',
        description: 'Install Leaf Auth in your app.',
    },
    {
        key: 'session',
        name: 'Session',
        description: 'Install Leaf Session in your app.',
    },
    {
        key: 'cookie',
        name: 'Cookie',
        description: 'Install Leaf Cookie in your app.',
    },
    {
        key: 'cors',
        name: 'Cors',
        description: 'Install Leaf Cors in your app.',
    },
    {
        key: 'date',
        name: 'Date',
        description: 'Install Leaf Date in your app.',
    },
];

export const templateEngines = [
    {
        key: 'bare-ui',
        icon: (
            <img
                src="https://leafphp.dev/assets/leaf3-logo-circle.5b8e60e2.png"
                className="w-5 h-5"
            />
        ),
        name: 'Bare UI',
        description:
            'Barebones templating engine built for speed and efficiency.',
    },
    {
        key: 'blade',
        icon: (
            <img
                src="https://laravel.com/img/logomark.min.svg"
                className="w-5 h-5"
            />
        ),
        name: 'Laravel Blade',
        description: "Laravel's powerful and flexible templating engine.",
    },
];

export const testingFrameworks = [
    {
        key: 'none',
        icon: (
            <img
                src="https://leafphp.dev/assets/leaf3-logo-circle.5b8e60e2.png"
                className="w-5 h-5"
            />
        ),
        name: 'No Tests',
        description:
            'Exclude testing from your app. You can always add it later.',
    },
    {
        key: 'pest',
        icon: (
            <img
                src="https://pestphp.com/www/assets/logo.svg"
                className="w-5 h-5"
            />
        ),
        name: 'Pest PHP',
        description: 'The elegant PHP testing framework.',
    },
    {
        key: 'phpunit',
        icon: (
            <img src="https://phpunit.de/img/phpunit.svg" className="w-5 h-5" />
        ),
        name: 'PHPUnit',
        description: 'The PHP Testing Framework.',
    },
];
