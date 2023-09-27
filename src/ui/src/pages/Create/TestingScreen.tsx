import Card from '../../components/Card';
import { testingFrameworks } from '../../data/walkthrough';
import { CreateSubScreenProps, TestingFramework } from '../@types/CreateScreen'; // prettier-ignore

const TestingScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">
                        Choose a Testing Framework
                    </h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        Leaf will use this framework to create and run tests
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                {testingFrameworks.map(({ icon, key, name, description }) => (
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
                                    testing: key as TestingFramework,
                                });
                            }

                            navigate('docker');
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
