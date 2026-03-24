import { configureStore } from '@reduxjs/toolkit';
import performanceReducer from './performanceSlice';

const store = configureStore({
    reducer: {
        performance: performanceReducer
    },
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware({
            immutableCheck: false,
            serializableCheck: false,
        }),
});

export default store;
