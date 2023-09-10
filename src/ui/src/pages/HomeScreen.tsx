import useSWR from 'swr';
import { useStore } from 'glassx';
import FadeIn from 'react-fade-in';
import {
    DownloadCloud,
    Terminal,
    Layers,
    Star,
    BookOpen,
    GitHub,
    Check,
} from 'react-feather';

import Card from '../components/Card';
import { useState } from 'react';

const HomeScreen = () => {
    const [, setScreen] = useStore('screen');
    const [dir, setDir] = useState('');
    const [loading, setLoading] = useState(false);

    const { data: versionData } = useSWR(
        'https://repo.packagist.org/p2/leafs/leaf.json'
    );
    const { data: config, mutate: configMutate } = useSWR(
        'http://localhost:5500/server.php?action=getConfig'
    );

    const leafInfo = versionData?.packages['leafs/leaf'];

    return (
        <FadeIn className="flex flex-col justify-center items-center w-screen h-screen bg-white dark:bg-transparent">
            <div className="flex items-center mb-10">
                <img
                    src="https://leafphp.dev/logo-circle.png"
                    className="w-16 h-16 mr-4"
                    alt="logo"
                />
                <div>
                    <div className="flex items-center">
                        <h1 className="text-4xl font-bold dark:text-white text-gray-900">
                            Leaf CLI &#123;ui&#125;
                        </h1>
                        <span className="bg-green-100 text-green-800 text-xs font-medium mr-2 mb-2 ml-1 px-2 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                            Beta
                        </span>
                    </div>
                    <p className="dark:text-gray-300 text-gray-600">
                        v0.0.0 - 2 Sep, 2023
                    </p>
                </div>
            </div>

            {config?.data?.dir ? (
                <div className="grid grid-cols-1 md:grid-cols-3 justify-center gap-3">
                    <Card className="text-gray-900 dark:text-white">
                        <DownloadCloud />
                        <h6 className="mt-2 font-bold tracking-tight">
                            {leafInfo?.[0]?.version}
                        </h6>
                        <p className="font-normal text-xs text-gray-500">
                            Latest Leaf Version
                        </p>
                    </Card>
                    <Card
                        className="text-gray-900 dark:text-white"
                        onClick={() => setScreen('Create')}
                    >
                        <Terminal />
                        <h6 className="mt-2 font-bold tracking-tight">
                            Create
                        </h6>
                        <p className="font-normal text-xs text-gray-500">
                            Setup a new Leaf app
                        </p>
                    </Card>
                    <Card
                        className="text-gray-900 dark:text-white"
                        onClick={() => setScreen('Apps')}
                    >
                        <Layers />
                        <h6 className="mt-2 font-bold tracking-tight">Apps</h6>
                        <p className="font-normal text-xs dark:text-gray-500 text-gray-800">
                            List your apps
                        </p>
                    </Card>
                </div>
            ) : (
                <div className="flex flex-col justify-center items-center">
                    <div className="text-gray-900 dark:text-white flex flex-col justify-center items-center my-10 max-w-[550px] px-10">
                        <p className="text-center">
                            We noticed this is your first time using the UI. To
                            get started, you need to configure a directory where
                            Leaf will save all of the projects you create using
                            the UI. You can always update the folder you select.
                        </p>

                        <div className="mt-10 w-full flex relative">
                            <input
                                type="text"
                                className="absolute w-full rounded-full h-14 border dark:border-blue-200/20 border-gray-200 bg-transparent pl-5 pr-10"
                                placeholder="/users/username/projects"
                                onChange={(e) => setDir(e.target.value)}
                            />
                            <button
                                className="absolute right-2 top-2 bg-[#3eaf7c] hover:bg-[#3eaf7c]/75 ease-in-out py-3 px-4 rounded-full w-10 h-10 text-white"
                                onClick={async () => {
                                    setLoading(true);

                                    fetch(
                                        'http://localhost:5500/server.php?action=setConfig',
                                        {
                                            method: 'POST',
                                            body: JSON.stringify({
                                                data: {
                                                    dir,
                                                },
                                            }),
                                        }
                                    )
                                        .then(() => {
                                            configMutate();
                                        })
                                        .catch((err) => {
                                            console.log(
                                                'An error occurred',
                                                err
                                            );
                                        })
                                        .finally(() => {
                                            setLoading(false);
                                        });
                                }}
                            >
                                {!loading ? (
                                    <Check size={10} />
                                ) : (
                                    <div className="animate-ping">...</div>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {config?.data?.dir && (
                <div className="flex gap-8 mt-16">
                    <a
                        href="https://github.com/leafsphp/cli"
                        className="flex items-center dark:text-gray-400 text-gray-600 hover:text-gray-300 transition-all ease-in-out"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <Star className="mr-1" size={15} /> Star on GitHub
                    </a>
                    <a
                        href="http://github.com/leafsphp"
                        className="flex items-center dark:text-gray-400 text-gray-600 hover:text-gray-300 transition-all ease-in-out"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <GitHub className="mr-1" size={15} /> Leaf on GitHub
                    </a>
                    <a
                        href="http://leafphp.dev"
                        className="flex items-center dark:text-gray-400 text-gray-600 hover:text-gray-300 transition-all ease-in-out"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <BookOpen className="mr-1" size={15} /> Leaf Docs
                    </a>
                </div>
            )}
        </FadeIn>
    );
};

export default HomeScreen;
