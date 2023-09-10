const ConsoleCard = ({ children, type }: any) => {
    return (
        <div
            className={
                'console-card px-4 py-1 border-b border-blue-200/10' +
                (type === 'warn'
                    ? ' bg-yellow-700/[0.5] text-amber-400'
                    : type === 'error'
                    ? ' bg-[#300f0f] text-[#f44336]'
                    : ' text-blue-50')
            }
        >
            {children}
        </div>
    );
};

export default ConsoleCard;
