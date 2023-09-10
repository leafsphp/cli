import FadeIn from 'react-fade-in';
import { twMerge } from 'tailwind-merge';

import Nav from './Nav';

const PageLayout = ({ children, className }: any) => {
    return (
        <div className={twMerge('flex h-screen w-screen', className)}>
            <Nav />
            <FadeIn className="w-[calc(100vw-100px)] xl:w-[calc(100vw-300px)] h-full absolute right-0 overflow-x-hidden">
                {children}
            </FadeIn>
        </div>
    );
};

export default PageLayout;
