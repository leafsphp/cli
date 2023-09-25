import InlineForm from '../../components/InlineForm';
import { CreateSubScreenProps } from '../@types/CreateScreen';

const NameScreen: React.FC<React.PropsWithChildren<CreateSubScreenProps>> = ({
    values,
    navigate,
    setValues,
}) => {
    return (
        <>
            <div className="px-5 lg:px-10">
                <div>
                    <h1 className="text-2xl font-bold">Create Application</h1>
                    <div className="dark:text-gray-400 text-gray-600">
                        What would you like to name your application?
                    </div>
                </div>
            </div>

            <div className="py-5 px-5 lg:px-10">
                <InlineForm
                    value={values.name}
                    setValue={(value) =>
                        setValues({ ...values, name: value as string })
                    }
                    placeholder="Application Name"
                    onSubmit={() => {
                        navigate('type');
                    }}
                />
            </div>
        </>
    );
};

export default NameScreen;
