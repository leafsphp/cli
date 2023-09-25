import { twMerge } from 'tailwind-merge';
import { Check, X } from 'react-feather';
import { PropsWithChildren, useState } from 'react';

import { InlineFormProps } from './@types/InlineForm';

const InlineForm: React.FC<PropsWithChildren<InlineFormProps>> = ({
    value,
    setValue,
    onSubmit,
    placeholder = '',
}) => {
    const [loading, setLoading] = useState(false);
    const [isError, setIsError] = useState<string | boolean>(false);

    return (
        <div className="mt-10 w-full flex relative">
            <input
                type="text"
                value={value}
                placeholder={placeholder}
                className={twMerge(
                    'absolute w-full rounded-full h-14 border dark:border-blue-200/20 border-gray-200 bg-transparent pl-5 pr-10',
                    isError ? 'border-red-500 dark:border-red-500' : ''
                )}
                onChange={(e) => {
                    const data = e.target.value;

                    setValue(data);

                    if (data === '') {
                        setIsError('Please enter a value');
                    } else {
                        setIsError(false);
                    }
                }}
            />
            <button
                disabled={loading || !!isError}
                className={twMerge(
                    'absolute right-2 top-2 bg-[#3eaf7c] hover:bg-[#3eaf7c]/75 ease-in-out py-3 px-4 rounded-full w-10 h-10 text-white outline-none focus:bg-green-500',
                    loading ? 'cursor-not-allowed' : '',
                    isError
                        ? 'bg-red-500 hover:bg-red-600 cursor-not-allowed'
                        : ''
                )}
                onClick={async () => {
                    setLoading(true);

                    try {
                        onSubmit();
                    } finally {
                        setLoading(false);
                    }
                }}
            >
                {!loading ? (
                    <>{isError ? <X size={10} /> : <Check size={10} />}</>
                ) : (
                    <div className="animate-ping">...</div>
                )}
            </button>
        </div>
    );
};

export default InlineForm;
