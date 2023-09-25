import GlassX, { PersistedState, useStore } from 'glassx';

import Router from './utils/router';

// [TODO] Fix all `any` types later

GlassX.store({
    state: {
        screen: 'Home',
    },
    plugins: [
        new PersistedState({
            key: 'leaf-devtools',
            exclude: ['screen'],
        }),
    ],
});

function App() {
    const [screen] = useStore('screen');

    return Router(screen);
}

export default App;
