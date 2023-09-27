import Card from '../../components/Card';
import { CreateSubScreenProps, FrontendFramework } from '../@types/CreateScreen'; // prettier-ignore

const DockerScreen: React.FC<
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

    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">
                        Choose a Container Solution
                    </h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        This option allows you to containerize your app with
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

export default DockerScreen;
