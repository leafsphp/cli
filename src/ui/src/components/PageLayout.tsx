import { useStore } from 'glassx';
import FadeIn from 'react-fade-in';
import { XCircle } from 'react-feather';
import { twMerge } from 'tailwind-merge';

import { PageLayoutProps } from './@types/PageLayout';

const PageLayout: React.FC<React.PropsWithChildren<PageLayoutProps>> = ({
    children,
    className,
}) => {
    const [, setScreen] = useStore('screen');

    return (
        <div
            className={twMerge(
                'h-screen w-screen flex flex-col justify-start items-center',
                className
            )}
        >
            <XCircle
                size={30}
                className="fixed right-10 top-10 text-gray-700 dark:text-gray-200 cursor-pointer"
                onClick={() => setScreen('home')}
            />
            <FadeIn className="w-full h-full pt-20 max-w-[650px]">
                {children}
            </FadeIn>
        </div>
    );
};

export default PageLayout;
