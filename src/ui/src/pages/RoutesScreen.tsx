import { useStore } from 'glassx';
import { Info, Layers } from 'react-feather';

import PageLayout from '../components/PageLayout';

const RoutesScreen = () => {
    const [data] = useStore('data');
    const routes = data?.app?.routes;

    return (
        <PageLayout>
            <div className="pt-5">
                <div className="px-5">
                    <div className="flex items-center">
                        <Layers size={32} className="mr-3" />
                        <div>
                            <h1 className="text-2xl font-bold">
                                Application Routes
                            </h1>
                            <div className="text-gray-400">
                                {routes?.length - 1} Route
                                {routes?.length - 1 !== 1 && 's'} defined
                            </div>
                        </div>
                    </div>
                    <p className="text-gray-300 text-xs flex items-center mt-5 bg-green-900/50 py-2 px-3 rounded-md">
                        <Info size={12} className="mr-1" /> This screen provides
                        insights into your application routes.
                    </p>
                </div>

                <div className="console-section mt-6 border-t border-blue-200/10">
                    <table className="table-auto w-full">
                        <thead className="border-b border-blue-200/10">
                            <tr className="text-left text-md">
                                <th className="px-5 uppercase">Methods</th>
                                <th className="px-5 uppercase">Pattern</th>
                                <th className="px-5 uppercase">Name</th>
                                <th className="px-5 uppercase">Handler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {routes
                                ?.filter((i: any) =>
                                    !i?.pattern?.includes(
                                        '/leafDevToolsEventHook'
                                    )
                                )
                                ?.map((route: any) => (
                                    <tr className="hover:bg-slate-100/5 transition-all ease-in">
                                        <td className="px-5 py-4">
                                            {route?.methods?.join?.(' | ') ||
                                                '-'}
                                        </td>
                                        <td className="px-5">
                                            {route?.pattern}
                                        </td>
                                        <td className="px-5">
                                            {route?.name || 'N/A'}
                                        </td>
                                        <td className="px-5">
                                            {typeof route?.handler === 'object'
                                                ? 'User Function'
                                                : route?.handler}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </PageLayout>
    );
};

export default RoutesScreen;
