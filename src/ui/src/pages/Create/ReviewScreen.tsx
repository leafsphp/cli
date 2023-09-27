import { useState } from 'react';
import { useStore } from 'glassx';

import Card from '../../components/Card';
import { CreateSubScreenProps } from '../@types/CreateScreen';
import {
    additionalFrontendOptions,
    containers,
    frontendFrameworks,
    modules,
    templateEngines,
    testingFrameworks,
    themes,
} from '../../data/walkthrough';

const ReviewScreen: React.FC<React.PropsWithChildren<CreateSubScreenProps>> = ({
    values,
    navigate,
    setValues,
}) => {
    const [, setScreen] = useStore('screen');
    const [loading, setLoading] = useState(false);

    const appType = themes.find((theme) => theme.key === values.type);
    const templateEngine = templateEngines.find(
        (engine) => engine.key === values.templateEngine
    );
    const frontendFramework = frontendFrameworks.find(
        (framework) => framework.key === values.frontendFramework
    );
    const testingFramework = testingFrameworks.find(
        (framework) => framework.key === values.testing
    );

    const createApp = () => {
        setLoading(true);

        const formData = {
            ...values,
            name: values?.name?.trim().replace(/\s+/g, '-').toLowerCase(),
        };

        fetch('http://localhost:5500/server.php?action=createApp', {
            method: 'POST',
            body: JSON.stringify({
                data: formData,
            }),
        })
            .then((res) => {
                setValues({});

                if (res.ok) {
                    setScreen('home');
                }
            })
            .catch((err) => {
                console.log('An error occurred', err);
            })
            .finally(() => {
                setLoading(false);
            });
    };

    return loading ? (
        <LoadingSection />
    ) : (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">
                        Review your app config
                    </h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        Make sure everything looks good before creating your
                        app.
                    </div>
                </div>
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Your application type</h2>
                {appType && (
                    <Card
                        key={appType.key}
                        className="w-100 max-w-none items-start"
                        onClick={() => {
                            navigate('type');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            <appType.icon size={16} /> {appType.name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {appType.description}
                        </p>
                    </Card>
                )}
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Your template engine</h2>
                {templateEngine && (
                    <Card
                        key={templateEngine.key}
                        className="w-100 max-w-none items-start"
                        onClick={() => {
                            navigate('templateEngine');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            {templateEngine.icon} {templateEngine.name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {templateEngine.description}
                        </p>
                    </Card>
                )}
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Your frontend framework</h2>
                {frontendFramework && (
                    <Card
                        key={frontendFramework.key}
                        className="w-100 max-w-none items-start"
                        onClick={() => {
                            navigate('frontendFramework');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            {frontendFramework.icon} {frontendFramework.name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {frontendFramework.description}
                        </p>
                    </Card>
                )}
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Additional Frontend Options</h2>
                {values.additionalFrontendOptions?.map((option) => {
                    const item = additionalFrontendOptions.find(
                        (o) => o.key === option
                    );

                    return item ? (
                        <Card
                            key={item.key}
                            className="w-100 max-w-none items-start mb-2"
                            onClick={() => {
                                navigate('additionalFrontendOptions');
                            }}
                        >
                            <h3 className="font-bold mb-1 flex items-center gap-1">
                                {item.icon} {item.name}
                            </h3>
                            <p className="dark:text-gray-400 text-gray-600">
                                {item.description}
                            </p>
                        </Card>
                    ) : (
                        <></>
                    );
                })}
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Selected Modules</h2>
                {values.modules?.map((option) => {
                    const item = modules.find((o) => o.key === option);

                    return item ? (
                        <Card
                            key={item.key}
                            className="w-100 max-w-none items-start mb-2"
                            onClick={() => {
                                navigate('modules');
                            }}
                        >
                            <h3 className="font-bold mb-1 flex items-center gap-1">
                                {item.name}
                            </h3>
                            <p className="dark:text-gray-400 text-gray-600">
                                {item.description}
                            </p>
                        </Card>
                    ) : (
                        <></>
                    );
                })}
            </div>

            <div className="mt-6 py-2 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Your testing framework</h2>
                {testingFramework && (
                    <Card
                        key={testingFramework.key}
                        className="w-100 max-w-none items-start"
                        onClick={() => {
                            navigate('testing');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            {testingFramework.icon} {testingFramework.name}
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            {testingFramework.description}
                        </p>
                    </Card>
                )}
            </div>

            <div className="mt-6 pt-2 pb-12 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Selected container solution</h2>
                {containers
                    ?.filter((theme) => theme.key === 'docker' && values.docker)
                    .map(({ icon, key, name, description }) => (
                        <Card
                            key={key}
                            className="w-100 max-w-none items-start"
                            onClick={() => {
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

            <div className="px-5 lg:px-10 pb-10">
                <button
                    className="bg-green-800 px-5 py-2 rounded-md text-white"
                    onClick={createApp}
                >
                    Create app
                </button>
            </div>
        </>
    );
};

const LoadingSection = () => {
    return (
        <div className="px-5 lg:px-10 h-[80vh] flex flex-col justify-center items-center">
            <h2 className="mb-3 text-center text-2xl">
                Creating your Leaf app
            </h2>
            <div className="flex text-4xl">
                <div className="animate-bounce">.</div>
                <div className="animate-bounce [animation-delay:75ms]">.</div>
                <div className="animate-bounce">.</div>
            </div>
        </div>
    );
};

export default ReviewScreen;
