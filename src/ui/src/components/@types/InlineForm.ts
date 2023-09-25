import { Dispatch, SetStateAction } from "react";

export interface InlineFormProps {
    placeholder?: string;
    value: string;
    setValue: Dispatch<SetStateAction<string>>;
    onSubmit: VoidFunction;
}
