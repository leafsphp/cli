import { Dispatch, SetStateAction } from "react";

export interface DirectoryInputProps {
    dir: string;
    setDir: Dispatch<SetStateAction<string>>;
    configMutate: VoidFunction;
    loading: boolean;
    setLoading: Dispatch<SetStateAction<boolean>>;
}
