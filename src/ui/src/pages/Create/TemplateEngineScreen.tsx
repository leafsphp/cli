import Card from '../../components/Card';
import { templateEngines } from '../../data/walkthrough';
import { CreateSubScreenProps, TemplateEngine } from '../@types/CreateScreen';

const TemplateEngineScreen: React.FC<
    React.PropsWithChildren<CreateSubScreenProps>
> = ({ values, navigate, setValues }) => {
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
                {templateEngines.map(({ icon, key, name, description }) => (
                    <Card
                        key={key}
                        className={`w-100 max-w-none items-start mb-5 ${
                            key === values.templateEngine
                                ? 'border-green-600 dark:border-green-600 hover:border-green-600'
                                : ''
                        }`}
                        onClick={() => {
                            setValues({
                                ...values,
                                templateEngine: key as TemplateEngine,
                            });
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
