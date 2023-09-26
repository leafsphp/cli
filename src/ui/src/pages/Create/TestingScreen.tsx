import Card from '../../components/Card';
import { CreateSubScreenProps, FrontendFramework } from '../@types/CreateScreen'; // prettier-ignore

const TestingScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
    const engines = [
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
            icon: (
                <img
                    src="https://v2.vuejs.org/images/logo.svg"
                    className="w-5 h-5"
                />
            ),
            name: 'Vue JS',
            description: 'The Progressive JavaScript Framework',
        },
    ];

    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">
                        Choose a Frontend Framework
                    </h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        Leaf will use this framework to render your UI.
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                {engines.map(({ icon, key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start mb-5 ${
                            key === values.frontendFramework
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            if (key !== 'none') {
                                setValues({
                                    ...values,
                                    frontendFramework: key as FrontendFramework,
                                });
                            }

                            navigate('additionalFrontendOptions');
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

export default TestingScreen;
