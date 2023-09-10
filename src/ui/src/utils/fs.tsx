export const openDirectoryPicker = async (mode = 'read') => {
    // Feature detection. The API needs to be supported
    // and the app not run in an iframe.
    const supportsFileSystemAccess =
        'showDirectoryPicker' in window &&
        (() => {
            try {
                return window.self === window.top;
            } catch {
                return false;
            }
        })();

    // If the File System Access API is supported…
    if (supportsFileSystemAccess) {
        let directoryStructure = undefined;

        // Recursive function that walks the directory structure.
        // @ts-expect-error testing
        const getFiles = async (dirHandle, path = dirHandle.name) => {
            const dirs = [];
            const files = [];
            for await (const entry of dirHandle.values()) {
                const nestedPath = `${path}/${entry.name}`;

                if (entry.kind === 'file') {
                    files.push(
                        // @ts-expect-error Just testing
                        entry.getFile().then((file) => {
                            file.directoryHandle = dirHandle;
                            file.handle = entry;
                            return Object.defineProperty(
                                file,
                                'webkitRelativePath',
                                {
                                    configurable: true,
                                    enumerable: true,
                                    get: () => nestedPath,
                                }
                            );
                        })
                    );
                } else if (entry.kind === 'directory') {
                    dirs.push(getFiles(entry, nestedPath));
                }
            }
            return [
                ...(await Promise.all(dirs)).flat(),
                ...(await Promise.all(files)),
            ];
        };

        try {
            // Open the directory.
            // @ts-expect-error method might not be available in every browser
            const handle = await showDirectoryPicker({
                mode,
            });
            
            directoryStructure = getFiles(handle, undefined);
        } catch (err: any) {
            if (err.name !== 'AbortError') {
                console.error(err.name, err.message);
            }
        }
        return directoryStructure;
    }

    // Fallback if the File System Access API is not supported.
    return new Promise((resolve) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.webkitdirectory = true;

        input.addEventListener('change', () => {
            // @ts-expect-error input.files
            const files = Array.from(input.files);
            resolve(files);
        });

        if ('showPicker' in HTMLInputElement.prototype) {
            input.showPicker();
        } else {
            input.click();
        }
    });
};

export const FolderPickerButton = () => {
    return (
        <button
            className="bg-[#3eaf7c] hover:bg-[#3eaf7c]/75 ease-in-out py-3 px-4 rounded-lg text-white mt-10"
            onClick={async () => {
                console.log('This is running');
                const folder = await openDirectoryPicker();

                console.log(folder, 'This is the folder');
            }}
        >
            Set Projects Directory
        </button>
    );
};
