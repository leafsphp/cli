import Card from '../../components/Card';
import { modules } from '../../data/walkthrough';
import { CreateSubScreenProps } from '../@types/CreateScreen';

const ModulesScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">Add leaf modules</h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        Select modules to add to your leaf app
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10 grid grid-cols-1 md:grid-cols-2 gap-2">
                {modules.map(({ key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start ${
                            values.modules?.includes(key)
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            if (key === 'none') {
                                setValues({
                                    ...values,
                                    modules: ['none'],
                                });
                            } else {
                                if (values.modules?.includes(key)) {
                                    setValues({
                                        ...values,
                                        modules: values.modules?.filter(
                                            (option) =>
                                                option !== key &&
                                                option !== 'none'
                                        ),
                                    });
                                } else {
                                    setValues({
                                        ...values,
                                        modules: [
                                            ...(values.modules?.filter(
                                                (option) => option !== 'none'
                                            ) ?? []),
                                            key,
                                        ],
                                    });
                                }
                            }
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            {name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {description}
                        </p>
                    </Card>
                ))}
            </div>

            <div className="px-5 lg:px-10 mt-20">
                <button
                    className="bg-green-800 px-5 py-2 rounded-md text-white"
                    onClick={() => navigate('testing')}
                >
                    Next
                </button>
            </div>
        </>
    );
};

export default ModulesScreen;
