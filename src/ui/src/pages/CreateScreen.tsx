import { Info, Terminal } from 'react-feather';

import PageLayout from '../components/PageLayout';
import Card from '../components/Card';

const CreateScreen = () => {
    // const [walkthrough, setWalkthrough] = useState();
    return (
        <PageLayout className="bg-white dark:bg-transparent text-gray-900 dark:text-white">
            <div className="pt-5">
                <div className="px-5">
                    <div className="flex items-center">
                        <Terminal size={32} className="mr-3" />
                        <div>
                            <h1 className="text-2xl font-bold">
                                Create Application
                            </h1>
                            <div className="dark:text-gray-400 text-gray-600">
                                Create a new Leaf app
                            </div>
                        </div>
                    </div>
                    <p className="dark:text-gray-300 text-white text-xs flex items-center mt-5 dark:bg-green-900/50 bg-green-900/75 py-2 px-3 rounded-md">
                        <Info size={12} className="mr-1" /> This screen allows
                        you setup a Leaf app to match your project.
                    </p>
                </div>

                <div className="console-section mt-6 border-t dark:border-blue-200/10 border-gray-200 p-5">
                    <Card>Card</Card>
                </div>
            </div>
        </PageLayout>
    );
};

export default CreateScreen;
