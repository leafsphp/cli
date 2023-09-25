import { useState } from 'react';

import NameScreen from './Create/NameScreen';
import PageLayout from '../components/PageLayout';
import AppTypeScreen from './Create/AppTypeScreen';
import TemplateEngineScreen from './Create/TemplateEngineScreen';
import FrontendFrameworkScreen from './Create/FrontendFrameworkScreen';
import { WalkthroughSelections, WalkthroughSteps } from './@types/CreateScreen';
import AdditionalFrontendOptionsScreen from './Create/AdditionalFrontendOptionsScreen';

const CreateScreen = () => {
    const [walkthrough, setWalkthrough] = useState<WalkthroughSteps>('name');
    const [selected, setSelected] = useState<WalkthroughSelections>({
        name: '',
    });

    const createApp = () => {
        const formData = {
            ...selected,
            name: selected?.name?.trim().replace(/\s+/g, '-').toLowerCase(),
        };
    };

    // [Todo] Refactor this later

    return (
        <PageLayout className="bg-white dark:bg-transparent text-gray-900 dark:text-white">
            {walkthrough === 'name' && (
                <NameScreen
                    values={selected}
                    setValues={setSelected}
                    navigate={setWalkthrough}
                />
            )}

            {walkthrough === 'type' && (
                <AppTypeScreen
                    values={selected}
                    setValues={setSelected}
                    navigate={setWalkthrough}
                />
            )}
            
            {walkthrough === 'templateEngine' && (
                <TemplateEngineScreen
                    values={selected}
                    setValues={setSelected}
                    navigate={setWalkthrough}
                />
            )}
            
            {walkthrough === 'frontendFramework' && (
                <FrontendFrameworkScreen
                    values={selected}
                    setValues={setSelected}
                    navigate={setWalkthrough}
                />
            )}
            
            {walkthrough === 'additionalFrontendOptions' && (
                <AdditionalFrontendOptionsScreen
                    values={selected}
                    setValues={setSelected}
                    navigate={setWalkthrough}
                />
            )}
        </PageLayout>
    );
};

export default CreateScreen;
