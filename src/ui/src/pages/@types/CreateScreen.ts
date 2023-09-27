export interface WalkthroughSelections {
    name?: string;
    type?: 'leaf' | 'mvc' | 'api';
    templateEngine?: 'blade' | 'bare-ui';
    frontendFramework?: 'react' | 'vue';
    additionalFrontendOptions?: string[];
    modules?: string[];
    docker?: boolean;
    testing?: 'pest' | 'phpunit';
    review?: boolean;
}

export type WalkthroughSteps = keyof WalkthroughSelections;
export type ProjectType = WalkthroughSelections['type'];
export type TemplateEngine = WalkthroughSelections['templateEngine'];
export type FrontendFramework = WalkthroughSelections['frontendFramework'];
export type AdditionalFrontendOptions = WalkthroughSelections['additionalFrontendOptions'];
export type TestingFramework = WalkthroughSelections['testing'];

export interface CreateSubScreenProps {
    values: WalkthroughSelections;
    navigate: (step: WalkthroughSteps) => void;
    setValues: (values: WalkthroughSelections) => void;
}
