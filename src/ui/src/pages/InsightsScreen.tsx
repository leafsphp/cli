import { useStore } from 'glassx';
import { Info, Package } from 'react-feather';
import ReactJson from 'react-json-view';

import PageLayout from '../components/PageLayout';

const InsightsScreen = () => {
    const [data] = useStore('data');

    return (
        <PageLayout>
            <div className="pt-5">
                <div className="px-5">
                    <div className="flex items-center">
                        <Package size={32} className="mr-3" />
                        <div>
                            <h1 className="text-2xl font-bold">
                                Application Overview
                            </h1>
                            <div className="text-gray-400">
                                Config, session, env, etc.
                            </div>
                        </div>
                    </div>
                    <p className="text-gray-300 text-xs flex items-center mt-5 bg-green-900/50 py-2 px-3 rounded-md">
                        <Info size={12} className="mr-1" /> This screen provides
                        insights into your application config, env, session,
                        cookies, etc.
                    </p>
                </div>

                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        App Config
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.app?.config}
                    />
                </div>
                
                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        App Request
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.request}
                    />
                </div>

                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        $_SERVER
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.server}
                    />
                </div>

                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        Cookies
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.cookies}
                    />
                </div>
                
                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        Session
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.session ?? []}
                    />
                </div>
                
                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        Headers
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.headers}
                    />
                </div>

                <div className="console-section mt-6 border-t border-blue-200/10 p-5">
                    <h2 className="text-lg mb-2 font-bold uppercase">
                        Env
                    </h2>
                    <ReactJson
                        theme="ashes"
                        style={{
                            background: 'transparent',
                            fontSize: '1rem',
                            maxHeight: 500,
                            overflowY: 'auto',
                        }}
                        src={data?.env}
                    />
                </div>
            </div>
        </PageLayout>
    );
};

export default InsightsScreen;
