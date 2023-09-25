import { FileMinus, Folder, FolderPlus } from 'react-feather';

import Card from '../../components/Card';

const AppTypeScreen: React.FC<React.PropsWithChildren<any>> = ({
    values,
    navigate,
    setValues,
}) => {
    const themes = [
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

    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">Choose a starter kit</h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        What kind of Leaf app do you want to create?
                    </div>
                </div>
            </div>

            <div className="console-section mt-6 py-5 px-5 lg:px-10">
                {themes.map(({ icon: Icon, key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start mb-5 ${
                            key === values.type
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            setValues({ ...values, type: key });
                            navigate(key === 'api' ? 'testing' : 'templateEngine');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            <Icon size={16} /> {name}
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

export default AppTypeScreen;
