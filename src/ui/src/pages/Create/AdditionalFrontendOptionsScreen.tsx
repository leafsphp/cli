import Card from '../../components/Card';
import { additionalFrontendOptions } from '../../data/walkthrough';
import { CreateSubScreenProps } from '../@types/CreateScreen';

const AdditionalFrontendOptionsScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
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
                {additionalFrontendOptions
                    .filter(({ key }) => {
                        if (values.type === 'mvc') {
                            return key !== 'vite';
                        }

                        return true;
                    })
                    .map(({ icon, key, name, description }) => (
                        <Card
                            key={key}
                            className={`w-100 max-w-none items-start mb-5 ${
                                values.additionalFrontendOptions?.includes(key)
                                    ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                    : ''
                            }`}
                            onClick={() => {
                                if (
                                    values.additionalFrontendOptions?.includes(
                                        key
                                    )
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
                <button
                    className="mt-20 bg-green-800 px-5 py-2 rounded-md text-white"
                    onClick={() =>
                        navigate(values.type === 'leaf' ? 'modules' : 'testing')
                    }
                >
                    Next
                </button>
            </div>
        </>
    );
};

export default AdditionalFrontendOptionsScreen;
