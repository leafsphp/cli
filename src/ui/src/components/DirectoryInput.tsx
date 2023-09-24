import { PropsWithChildren, useState } from 'react';
import { Check, X } from 'react-feather';
import { DirectoryInputProps } from './@types/DirectoryInput';
import { twMerge } from 'tailwind-merge';

const DirectoryInput: React.FC<PropsWithChildren<DirectoryInputProps>> = ({
    dir,
    setDir,
    configMutate,
    loading,
    setLoading,
}) => {
    const [isError, setIsError] = useState(false);

    return (
        <div className="mt-10 w-full flex relative">
            <input
                type="text"
                placeholder="/users/username/projects"
                className={twMerge(
                    'absolute w-full rounded-full h-14 border dark:border-blue-200/20 border-gray-200 bg-transparent pl-5 pr-10',
                    isError ? 'border-red-500 dark:border-red-500' : ''
                )}
                onChange={(e) => {
                    const data = e.target.value;

                    setDir(data);

                    if (
                        !/^\/(?:[\w.-]+\/)*[\w.-]+$/.test(data) &&
                        data !== ''
                    ) {
                        setIsError(true);
                    } else {
                        setIsError(false);
                    }
                }}
            />
            <button
                disabled={loading || isError}
                className={twMerge(
                    'absolute right-2 top-2 bg-[#3eaf7c] hover:bg-[#3eaf7c]/75 ease-in-out py-3 px-4 rounded-full w-10 h-10 text-white outline-none focus:bg-green-500',
                    loading ? 'cursor-not-allowed' : '',
                    isError
                        ? 'bg-red-500 hover:bg-red-600 cursor-not-allowed'
                        : ''
                )}
                onClick={async () => {
                    setLoading(true);

                    fetch('http://localhost:5500/server.php?action=setConfig', {
                        method: 'POST',
                        body: JSON.stringify({
                            data: {
                                dir,
                            },
                        }),
                    })
                        .then(() => {
                            configMutate();
                        })
                        .catch((err) => {
                            console.log('An error occurred', err);
                        })
                        .finally(() => {
                            setLoading(false);
                        });
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

export default DirectoryInput;
