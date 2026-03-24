import React, { useEffect } from 'react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import performanceReducer from './store/performanceSlice';
import PerformanceNavLink from './components/PerformanceNavLink';

/**
 * We create a localized Redux store specifically for the Performance addon.
 * This keeps our Gamification state completely decoupled from the main app's Redux tree.
 */
const store = configureStore({
    reducer: {
        performance: performanceReducer
    }
});

export const performanceRoutes = [
    {
        key: 'performance-dashboard',
        path: '/v3/performance-dashboard',
        component: React.lazy(() => import('./components/PerformanceDashboard')),
        // We leave authority empty at route level, V3 uses component-level check based on Project/Global permissions
        authority: [], 
    }
];

const App = () => {
    useEffect(() => {
        // Expose the addon to the main plugin
        window.lazytasksPerformance = {
            performanceRoutes,
            navLink: PerformanceNavLink,
            PerformanceDashboard: React.lazy(() => import('./components/PerformanceDashboard')),
            PerformanceSettings: React.lazy(() => import('./components/Settings/PerformanceSettings')),
            store,
        };

        // Notify main app that the addon is ready to be consumed
        window.dispatchEvent(new Event('lazytasksPerformanceReady'));
    }, []);

    // We render our own provider so standalone injected components can access our localized state
    return (
        <Provider store={store}>
            {/* The root App component does not render UI itself, just the provider logic */}
        </Provider>
    );
};

export default App;
