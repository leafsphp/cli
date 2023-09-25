import Card from '../../components/Card';

const TemplateEngineScreen: React.FC<React.PropsWithChildren<any>> = ({
    values,
    navigate,
    setValues,
}) => {
    const engines = [
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

    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">Choose a UI Engine</h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        This is the engine that will be used to render your UI.
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                {engines.map(({ icon, key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start mb-5 ${
                            key === values.templateEngine
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            setValues({ ...values, templateEngine: key });
                            navigate('frontendFramework');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            {icon} {name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {description}
                        </p>
                    </Card>
                ))}
            </div>
        </>
    );
};

export default TemplateEngineScreen;
