import Card from '../../components/Card';
import { themes } from '../../data/walkthrough';
import { CreateSubScreenProps, ProjectType } from '../@types/CreateScreen';

const AppTypeScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
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
                            setValues({ ...values, type: key as ProjectType });
                            navigate(
                                key === 'api' ? 'testing' : 'templateEngine'
                            );
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
