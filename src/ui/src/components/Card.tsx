import { twMerge } from 'tailwind-merge';

const Card = ({ children, className, ...props }: any) => {
    return (
        <div
            className={twMerge(
                'flex flex-col justify-center items-center cursor-pointer p-6 dark:bg-gray-900/25 bg-gray-100/20 border border-gray-700/25 rounded-lg hover:border-gray-600/25 hover:bg-green-900/5 transition ease-in max-w-[200px] ',
                className
            )}
            {...props}
        >
            {children}
        </div>
    );
};

export default Card;
