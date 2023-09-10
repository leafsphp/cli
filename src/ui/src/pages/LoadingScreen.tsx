import useSWR from 'swr';
import { useStore } from 'glassx';
import { Loader } from 'react-feather';

const LoadingScreen = () => {
    // const [appUrl, setUrl] = useStore('url');
    const [, setData] = useStore('data');
    const [, setScreen] = useStore('screen');
    const [, setAppUsesLeaf] = useStore('appUsesLeaf');

    // if (!appUrl) {
    //     chrome.tabs.query({ active: true, lastFocusedWindow: true }, (tabs) => {
    //         let appUrl = tabs?.[0]?.url;

    //         if (typeof appUrl === 'string') {
    //             setUrl(new URL(appUrl).origin);
    //         }
    //     });
    // }

    const { data: appData } = useSWR(`${/*appUrl*/ ''}/leafDevToolsEventHook`);

    if (appData) {
        setData(appData);
        setAppUsesLeaf(true);
        setScreen('HomeScreen');
    }

    return (
        <div className="flex flex-col justify-center items-center w-screen h-screen">
            <Loader transform="rotate(-10 50 100)" />
        </div>
    );
};

export default LoadingScreen;
