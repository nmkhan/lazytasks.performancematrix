import React, { useEffect, useState } from 'react';
import { Provider, useDispatch, useSelector } from 'react-redux';
import { fetchRules, updateRules, syncBacklog } from '../../store/performanceSlice';
import store from '../../store';

const PerformanceSettingsInner = () => {
    const dispatch = useDispatch();
    const { rules, loading, syncing, is_backlog_synced } = useSelector((state) => state.performance);
    const [localRules, setLocalRules] = useState([]);

    useEffect(() => {
        dispatch(fetchRules());
    }, [dispatch]);

    useEffect(() => {
        setLocalRules(rules);
    }, [rules]);

    const handlePointChange = (id, newPoints) => {
        setLocalRules(prev => prev.map(r => r.id === id ? { ...r, points: Number(newPoints) } : r));
    };

    const handleToggleActive = (id, isActive) => {
        setLocalRules(prev => prev.map(r => r.id === id ? { ...r, is_active: isActive ? 1 : 0 } : r));
    };

    const saveRules = () => {
        dispatch(updateRules({ rules: localRules })).then(() => {
            alert('Rules updated successfully!');
            dispatch(fetchRules());
        });
    };

    const handleSync = async () => {
        let isComplete = false;
        let isFirstRun = true;
        
        // Confirm first since this will wipe the leaderboard if re-running!
        if (isFirstRun && !window.confirm('Executing a manual synchronization will recalculate ALL gamification scores across your workspace from the beginning of time. Are you sure you want to proceed?')) {
            return;
        }

        while (!isComplete) {
            const res = await dispatch(syncBacklog(isFirstRun)).unwrap();
            isFirstRun = false;
            
            isComplete = res.is_complete;
            if (isComplete) {
                alert('Historical synchronization complete!');
                dispatch(fetchRules());
            }
        }
    };

    if (loading && localRules.length === 0) return <div>Loading...</div>;

    return (
        <div className="card">
            <div style={{ marginBottom: '24px', padding: '16px', backgroundColor: '#FFFBEB', border: '1px solid #FCD34D', borderRadius: '8px' }}>
                <h3 style={{ margin: '0 0 8px 0', fontSize: '15px', color: '#B45309' }}>Historical Data Available</h3>
                <p style={{ margin: '0 0 12px 0', fontSize: '13px', color: '#92400E' }}>
                    Your installation has past activity logs. Sync them now to retroactively calculate your team's gamification scores based on the current rule weights!
                </p>
                <div style={{ display: 'flex', gap: '10px' }}>
                    <button 
                        onClick={handleSync}
                        disabled={syncing}
                        className="sn-btn sn-btn-active"
                    >
                        {syncing ? 'Synchronizing chunks...' : 'Sync (or Re-Sync) Historical Data'}
                    </button>
                </div>
            </div>
            
            <div className="card-title">Gamification Scoring Rules</div>
            
            <div className="rules-list" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {localRules.map((rule) => (
                    <div key={rule.id} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px', border: '1px solid var(--border)', borderRadius: 'var(--radius)' }}>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, color: 'var(--text)' }}>
                                {rule.rule_key.replace(/_/g, ' ').toUpperCase()}
                            </div>
                            <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>
                                {rule.description}
                            </div>
                        </div>
                        
                        <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <label style={{ fontSize: '12px', fontWeight: 500 }}>Points:</label>
                                <input 
                                    type="number" 
                                    value={rule.points} 
                                    onChange={(e) => handlePointChange(rule.id, e.target.value)}
                                    style={{ width: '60px', padding: '4px', borderRadius: '4px', border: '1px solid var(--border)' }}
                                />
                            </div>
                            <label style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px', cursor: 'pointer' }}>
                                <input 
                                    type="checkbox" 
                                    checked={Number(rule.is_active) === 1}
                                    onChange={(e) => handleToggleActive(rule.id, e.target.checked)}
                                />
                                Active
                            </label>
                        </div>
                    </div>
                ))}
            </div>

            <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'flex-end' }}>
                <button 
                    onClick={saveRules} 
                    disabled={loading}
                    className="sn-btn sn-btn-active"
                >
                    {loading ? 'Saving...' : 'Save Rules'}
                </button>
            </div>
        </div>
    );
};

const PerformanceSettings = () => (
    <Provider store={store}>
        <PerformanceSettingsInner />
    </Provider>
);

export default PerformanceSettings;
