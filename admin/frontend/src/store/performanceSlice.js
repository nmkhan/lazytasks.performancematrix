import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import PerformanceService from '../services/PerformanceService';

export const fetchRules = createAsyncThunk(
    'performance/fetchRules',
    async (_, { rejectWithValue }) => {
        try {
            const response = await PerformanceService.getRules();
            return response;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

export const fetchProjectScores = createAsyncThunk(
    'performance/fetchProjectScores',
    async ({ projectId = 0, timeframe = 'all_time' } = {}, { rejectWithValue }) => {
        try {
            const response = await PerformanceService.getProjectScores(projectId, timeframe);
            return response;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

export const syncBacklog = createAsyncThunk(
    'performance/syncBacklog',
    async (reset = false, { rejectWithValue }) => {
        try {
            const response = await PerformanceService.syncBacklog(reset);
            return response;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

export const updateRules = createAsyncThunk(
    'performance/updateRules',
    async (payload, { rejectWithValue }) => {
        try {
            const response = await PerformanceService.updateRules(payload);
            return response;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

const initialState = {
    rules: [],
    loading: false,
    syncing: false,
    is_backlog_synced: true,
    last_synced_id: 0,
    scores: [],
    error: null,
};

const performanceSlice = createSlice({
    name: 'performance',
    initialState,
    reducers: {
        setLoading: (state, action) => {
            state.loading = action.payload;
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchRules.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchRules.fulfilled, (state, action) => {
                state.loading = false;
                state.rules = action.payload.data || [];
                state.is_backlog_synced = action.payload.is_backlog_synced;
                state.last_synced_id = action.payload.last_synced_id;
            })
            .addCase(fetchRules.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            })
            .addCase(fetchProjectScores.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchProjectScores.fulfilled, (state, action) => {
                state.loading = false;
                state.scores = action.payload.data || [];
            })
            .addCase(fetchProjectScores.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            })
            .addCase(updateRules.pending, (state) => {
                state.loading = true;
            })
            .addCase(updateRules.fulfilled, (state) => {
                state.loading = false;
            })
            .addCase(updateRules.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            })
            .addCase(syncBacklog.pending, (state) => {
                state.syncing = true;
                state.error = null;
            })
            .addCase(syncBacklog.fulfilled, (state, action) => {
                state.syncing = false;
                if (action.payload.is_complete) {
                    state.is_backlog_synced = true;
                }
            })
            .addCase(syncBacklog.rejected, (state, action) => {
                state.syncing = false;
                state.error = action.payload;
            });
    },
});

export const { setLoading } = performanceSlice.actions;

export default performanceSlice.reducer;
