import Card from '../../components/Card';
import { CreateSubScreenProps } from '../@types/CreateScreen';

const AdditionalFrontendOptionsScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
    const options = [
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

    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">
                        Choose Frontend Add-ons
                    </h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        Leaf will automatically install and configure selected
                        packages
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                {options.map(({ icon, key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start mb-5 ${
                            values.additionalFrontendOptions?.includes(key)
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            if (
                                values.additionalFrontendOptions?.includes(key)
                            ) {
                                setValues({
                                    ...values,
                                    additionalFrontendOptions:
                                        values.additionalFrontendOptions?.filter(
                                            (option) => option !== key
                                        ),
                                });
                            } else {
                                setValues({
                                    ...values,
                                    additionalFrontendOptions: [
                                        ...(values.additionalFrontendOptions ??
                                            []),
                                        key,
                                    ],
                                });
                            }
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
                <button className="mt-20" onClick={() => navigate('modules')}>
                    Next
                </button>
            </div>
        </>
    );
};

export default AdditionalFrontendOptionsScreen;
