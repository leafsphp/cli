import HomeScreen from '../pages/HomeScreen';
import AppsScreen from '../pages/AppsScreen';
import CreateScreen from '../pages/CreateScreen';
import InsightsScreen from '../pages/InsightsScreen';
import LeafNotFound from '../pages/LeafNotFound';
import LoadingScreen from '../pages/LoadingScreen';
import RoutesScreen from '../pages/RoutesScreen';

const Router = (screen: string) => {
    switch (screen) {
        case 'Home':
            return <HomeScreen />;
        case 'Apps':
            return <AppsScreen />;
        case 'Create':
            return <CreateScreen />;
        case 'Loading':
            return <LoadingScreen />;
        case 'Routes':
            return <RoutesScreen />;
        case 'Insights':
            return <InsightsScreen />;
        case 'LeafNotFound':
            return <LeafNotFound />;
        default:
            return <HomeScreen />;
    }
};

export default Router;
