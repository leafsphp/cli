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
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);

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

        if (
            formData.frontendFramework ||
            formData.additionalFrontendOptions?.includes('tailwind')
        ) {
            formData.additionalFrontendOptions =
                formData.additionalFrontendOptions?.filter(
                    (option) => option !== 'vite'
                );
        }

        fetch('http://localhost:5500/server.php?action=createApp', {
            method: 'POST',
            body: JSON.stringify({
                data: JSON.stringify(formData),
            }),
        })
            .then((res) => {
                if (res.ok) {
                    return res.json();
                }
            })
            .then((response) => {
                setValues({
                    ...values,
                    ...response?.data,
                });

                setSuccess(true);
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
    ) : success ? (
        <SuccessSection
            values={values}
            setValues={setValues}
            navigate={navigate}
        />
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

            {values.type !== 'api' && (
                <>
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

                    {values.frontendFramework && (
                        <div className="mt-6 py-2 px-5 lg:px-10">
                            <h2 className="font-bold mb-2">
                                Your frontend framework
                            </h2>
                            {frontendFramework && (
                                <Card
                                    key={frontendFramework.key}
                                    className="w-100 max-w-none items-start"
                                    onClick={() => {
                                        navigate('frontendFramework');
                                    }}
                                >
                                    <h3 className="font-bold mb-1 flex items-center gap-1">
                                        {frontendFramework.icon}{' '}
                                        {frontendFramework.name}
                                    </h3>
                                    <p className="dark:text-gray-400 text-gray-600">
                                        {frontendFramework.description}
                                    </p>
                                </Card>
                            )}
                        </div>
                    )}

                    {(values.additionalFrontendOptions?.length ?? 0) > 0 && (
                        <div className="mt-6 py-2 px-5 lg:px-10">
                            <h2 className="font-bold mb-2">
                                Additional Frontend Options
                            </h2>
                            {values.additionalFrontendOptions?.map((option) => {
                                const item = additionalFrontendOptions.find(
                                    (o) => o.key === option
                                );

                                return item ? (
                                    <Card
                                        key={item.key}
                                        className="w-100 max-w-none items-start mb-2"
                                        onClick={() => {
                                            navigate(
                                                'additionalFrontendOptions'
                                            );
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
                    )}

                    {values.type === 'leaf' && values.modules && (
                        <div className="mt-6 py-2 px-5 lg:px-10">
                            <h2 className="font-bold mb-2">Selected Modules</h2>
                            {values.modules?.map((option) => {
                                const item = modules.find(
                                    (o) => o.key === option
                                );

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
                    )}
                </>
            )}

            {values.testing && (
                <div className="mt-6 py-2 px-5 lg:px-10">
                    <h2 className="font-bold mb-2">Your testing framework</h2>
                    {testingFramework ? (
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
                    ) : (
                        <Card
                            key="none"
                            className="w-100 max-w-none items-start"
                            onClick={() => {
                                navigate('testing');
                            }}
                        >
                            <h3 className="font-bold mb-1 flex items-center gap-1">
                                None
                            </h3>
                            <p className="dark:text-gray-400 text-gray-600">
                                You can always add a testing framework later.
                            </p>
                        </Card>
                    )}
                </div>
            )}

            <div className="mt-6 pt-2 pb-12 px-5 lg:px-10">
                <h2 className="font-bold mb-2">Selected container solution</h2>
                {!values.docker && (
                    <Card
                        key="none"
                        className="w-100 max-w-none items-start"
                        onClick={() => {
                            navigate('docker');
                        }}
                    >
                        <h3 className="font-bold mb-1 flex items-center gap-1">
                            None
                        </h3>
                        <p className="dark:text-gray-400 text-gray-600">
                            You can always add a container solution later.
                        </p>
                    </Card>
                )}
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

const SuccessSection: React.FC<CreateSubScreenProps> = ({ values }) => {
    const [, setScreen] = useStore('screen');

    return (
        <div className="px-5 lg:px-10 h-[80vh] flex flex-col justify-center items-center">
            <div className="mb-8 text-center ">
                <h2 className="text-2xl">
                    Your {values.type} app has been created!
                </h2>
                <p className="text-gray-500">
                    To get started, you can follow these steps:
                </p>
            </div>

            <pre className="flex flex-col gap-4 w-full bg-gray-100 rounded-lg p-5">
                <div className="flex items-center gap-2">
                    <div>$</div>
                    <div>
                        cd{' '}
                        {values?.directory
                            ? `${values?.directory}/${values?.name}`
                            : values.name}
                    </div>
                </div>
                {!!(
                    values?.additionalFrontendOptions ||
                    values?.frontendFramework
                ) && (
                    <div className="flex items-center gap-2">
                        <div>$</div>
                        <div>leaf view:dev</div>
                    </div>
                )}
                <div className="flex items-center gap-2">
                    <div>$</div>
                    <div>leaf serve</div>
                </div>
            </pre>

            <div className="mt-8">
                <button
                    className="bg-green-800 px-5 py-2 rounded-md text-white"
                    onClick={() => {
                        setScreen('home');
                    }}
                >
                    Go Home
                </button>
            </div>
        </div>
    );
};

export default ReviewScreen;
