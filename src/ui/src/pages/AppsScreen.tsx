import useSWR from 'swr';
import { useStore } from 'glassx';
import { Info, Terminal } from 'react-feather';

import ConsoleCard from '../components/ConsoleCard';
import PageLayout from '../components/PageLayout';
import { useEffect } from 'react';

const AppsScreen = () => {
    const [data, setData] = useStore('data');
    const [url] = useStore('url');

    const { data: appData, error } = useSWR(`${url}/leafDevToolsEventHook`);
    const consoleData = data?.console;

    useEffect(() => {
        if (appData) {
            setData(appData);
        }
    }, [appData, error]);

    const clearConsole = () => {
        fetch(`${url}/leafDevToolsEventHook?action=clearLogs`).then((res) => {
            if (res.ok) {
                setData(res.json());
            } else {
                console.error('Could not clear console logs.');
            }
        });
    };

    return (
        <PageLayout>
            <div className="px-5 lg:px-10">
                <div className="flex items-center">
                    <Terminal size={32} className="mr-3" />
                    <div>
                        <h1 className="text-2xl font-bold">DevTools Console</h1>
                        <div className="text-gray-400">
                            {consoleData?.length} Server Log
                            {consoleData?.length !== 1 && 's'}
                        </div>
                    </div>
                </div>
                <p className="text-gray-300 text-xs flex items-center mt-5 bg-green-900/50 py-2 px-3 rounded-md">
                    <Info size={12} className="mr-1" /> The devtools console
                    provides a simple way to log out data for quick and easy
                    debugging, just as you would do with console.log in
                    JavaScript.
                </p>
            </div>

            <div className="console-section mt-6 border-t border-blue-200/10">
                <ConsoleCard type="log">
                    {consoleData?.length > 0 ? (
                        <button
                            onClick={clearConsole}
                            className="bg-black/25 hover:bg-black/40 py-2 px-4 rounded"
                        >
                            Clear Console
                        </button>
                    ) : (
                        <div className="flex flex-col justify-center items-center py-5">
                            <div>There's no console data to show.</div>
                            <div>
                                You can log items out using the
                                Leaf\DevTools::console() method.
                            </div>

                            <pre className="mt-4 text-left" lang="php">
                                <code>
                                    Leaf\DevTools::console('console.log this
                                    data');
                                </code>
                            </pre>
                        </div>
                    )}
                </ConsoleCard>
                {consoleData?.map((item: any, index: number) => (
                    <ConsoleCard type={item[0]} key={index}>
                        {typeof item[1] === 'string'
                            ? item[1]
                            : JSON.stringify(item[1])}
                    </ConsoleCard>
                ))}
            </div>
        </PageLayout>
    );
};

export default AppsScreen;
