import React, { useEffect, useState } from 'react';
import { Provider, useDispatch, useSelector } from 'react-redux';
import { fetchRules, updateRules, syncBacklog } from '../../store/performanceSlice';
import store from '../../store';

/* ─── Design tokens (mirrors GeneralTabV3 S object) ──────── */
const S = {
    orange: '#ED7D31', orangeDk: '#D46A1E', orangeLt: '#FEF0E6',
    teal: '#39758D', tealLt: '#EBF1F4', tealDk: '#2C5C6E',
    bg: '#F4F6F8', white: '#FFFFFF', border: '#E2E8F0',
    text: '#1A202C', muted: '#64748B', light: '#94A3B8',
    danger: '#EF4444', success: '#10B981',
    amberBg: '#FFFBEB', amberBorder: '#FCD34D',
    amberText: '#92400E', amberTitle: '#B45309',
    shadow: '0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05)',
    radius: 12, radiusSm: 6,
};

const inputStyle = {
    height: 34, padding: '0 10px', border: `1px solid ${S.border}`, borderRadius: S.radiusSm,
    fontSize: 13, fontFamily: 'inherit', color: S.text, background: S.white, outline: 'none',
    boxSizing: 'border-box',
};

/* ─── Micro-components ───────────────────────────────────── */
const Toggle = ({ checked, onChange, disabled }) => (
    <div
        onClick={() => !disabled && onChange(!checked)}
        style={{
            position: 'relative', width: 44, height: 24,
            background: checked ? S.orange : '#CBD5E1',
            borderRadius: 24, cursor: disabled ? 'not-allowed' : 'pointer',
            transition: 'background .2s', flexShrink: 0, opacity: disabled ? 0.5 : 1,
        }}
    >
        <div style={{
            position: 'absolute', height: 18, width: 18,
            left: checked ? 23 : 3, top: 3, background: '#fff',
            borderRadius: '50%', transition: 'left .2s',
            boxShadow: '0 1px 3px rgba(0,0,0,.2)',
        }} />
    </div>
);

const Btn = ({ children, variant = 'primary', onClick, style: extra, disabled }) => {
    const [hov, setHov] = useState(false);
    const base = {
        display: 'inline-flex', alignItems: 'center', gap: 6,
        padding: '0 16px', height: 34, fontSize: 13, fontWeight: 600,
        borderRadius: S.radiusSm, border: 'none', cursor: disabled ? 'not-allowed' : 'pointer',
        transition: 'background .18s', fontFamily: 'inherit', letterSpacing: '0.1px',
        opacity: disabled ? 0.6 : 1,
    };
    const variants = {
        primary: { background: hov && !disabled ? S.orangeDk : S.orange, color: '#fff' },
        ghost: { background: hov && !disabled ? '#E2E8F0' : '#F1F5F9', color: S.muted },
        teal: { background: hov && !disabled ? S.tealDk : S.teal, color: '#fff' },
    };
    return (
        <button
            type="button"
            onMouseEnter={() => setHov(true)}
            onMouseLeave={() => setHov(false)}
            onClick={disabled ? undefined : onClick}
            disabled={disabled}
            style={{ ...base, ...variants[variant], ...extra }}
        >
            {children}
        </button>
    );
};

const Icon = ({ d, size = 16, stroke = 'currentColor', sw = 2 }) => (
    <svg viewBox="0 0 24 24" fill="none" stroke={stroke} strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round" style={{ width: size, height: size }}>
        {typeof d === 'string' ? <path d={d} /> : d}
    </svg>
);

const SectionHeader = ({ title, desc, actions }) => (
    <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 20 }}>
        <div>
            <div style={{ fontSize: 16, fontWeight: 700, color: S.text }}>{title}</div>
            {desc && <div style={{ fontSize: 13, color: S.muted, marginTop: 2 }}>{desc}</div>}
        </div>
        {actions && <div style={{ flexShrink: 0, marginLeft: 16 }}>{actions}</div>}
    </div>
);

const Card = ({ children, style: extra }) => (
    <div style={{
        background: S.white, border: `1px solid ${S.border}`,
        borderRadius: S.radius, padding: 24, boxShadow: S.shadow, ...extra,
    }}>
        {children}
    </div>
);

const CardTitle = ({ icon, children }) => (
    <div style={{ fontSize: 14, fontWeight: 600, color: S.text, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
        <svg viewBox="0 0 24 24" fill="none" stroke={S.teal} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ width: 16, height: 16 }}>
            {icon}
        </svg>
        {children}
    </div>
);

const SettingRow = ({ label, desc, children, first }) => (
    <div style={{
        display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16,
        padding: '14px 0', borderTop: first ? 'none' : `1px solid ${S.border}`,
    }}>
        <div style={{ flex: 1 }}>
            <div style={{ fontSize: 13, fontWeight: 600, color: S.text }}>{label}</div>
            {desc && <div style={{ fontSize: 12, color: S.muted, marginTop: 2 }}>{desc}</div>}
        </div>
        <div style={{ marginTop: 2, flexShrink: 0 }}>{children}</div>
    </div>
);

/* ─── Main Component ─────────────────────────────────────── */
const PerformanceSettingsInner = () => {
    const dispatch = useDispatch();
    const { rules, loading, syncing } = useSelector((state) => state.performance);
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
        dispatch(updateRules({ rules: localRules })).then((res) => {
            if (res?.error) {
                window.lazytasksShowNotification?.({
                    title: 'Performance',
                    message: 'Failed to save rules',
                    color: 'red',
                    autoClose: 2000,
                });
            } else {
                window.lazytasksShowNotification?.({
                    title: 'Performance',
                    message: 'Rules updated successfully',
                    color: 'green',
                    autoClose: 2000,
                });
                dispatch(fetchRules());
            }
        });
    };

    const handleSync = async () => {
        if (!window.confirm('Executing a manual synchronization will recalculate ALL gamification scores across your workspace from the beginning of time. Are you sure you want to proceed?')) {
            return;
        }

        let isComplete = false;
        let isFirstRun = true;

        while (!isComplete) {
            const res = await dispatch(syncBacklog(isFirstRun)).unwrap();
            isFirstRun = false;
            isComplete = res.is_complete;
            if (isComplete) {
                window.lazytasksShowNotification?.({
                    title: 'Performance',
                    message: 'Historical synchronization complete!',
                    color: 'green',
                    autoClose: 3000,
                });
                dispatch(fetchRules());
            }
        }
    };

    if (loading && localRules.length === 0) {
        return <div style={{ padding: '32px 0', color: S.muted, fontSize: 14 }}>Loading...</div>;
    }

    return (
        <>
            <SectionHeader
                title="Performance & Gamification"
                desc="Configure scoring rules, point values, and sync historical activity data."
                actions={
                    <Btn variant="primary" onClick={saveRules} disabled={loading}>
                        <Icon d="M20 6 9 17 4 12" sw={2.5} size={14} />
                        {loading ? 'Saving...' : 'Save Changes'}
                    </Btn>
                }
            />

            {/* Historical sync alert */}
            <Card style={{ marginBottom: 16, background: S.amberBg, border: `1px solid ${S.amberBorder}` }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
                    <svg viewBox="0 0 24 24" fill="none" stroke={S.amberTitle} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ width: 20, height: 20, flexShrink: 0, marginTop: 1 }}>
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 14, fontWeight: 600, color: S.amberTitle, marginBottom: 4 }}>
                            Historical Data Available
                        </div>
                        <div style={{ fontSize: 13, color: S.amberText, marginBottom: 12, lineHeight: 1.5 }}>
                            Your installation has past activity logs. Sync them now to retroactively calculate your team's gamification scores based on the current rule weights!
                        </div>
                        <Btn variant="teal" onClick={handleSync} disabled={syncing}>
                            <Icon d={<><path d="M21.5 2v6h-6"/><path d="M2.5 12a10 10 0 0 1 18.27-5.5L21.5 2"/><path d="M2.5 22v-6h6"/><path d="M21.5 12a10 10 0 0 1-18.27 5.5L2.5 22"/></>} size={14} />
                            {syncing ? 'Synchronizing...' : 'Sync (or Re-Sync) Historical Data'}
                        </Btn>
                    </div>
                </div>
            </Card>

            {/* Scoring rules */}
            <Card>
                <CardTitle icon={<><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></>}>
                    Gamification Scoring Rules
                </CardTitle>

                {localRules.map((rule, idx) => (
                    <SettingRow
                        key={rule.id}
                        first={idx === 0}
                        label={rule.rule_key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}
                        desc={rule.description}
                    >
                        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <span style={{ fontSize: 12, fontWeight: 500, color: S.muted }}>Pts</span>
                                <input
                                    type="number"
                                    value={rule.points}
                                    onChange={(e) => handlePointChange(rule.id, e.target.value)}
                                    style={{ ...inputStyle, width: 70, textAlign: 'center' }}
                                />
                            </div>
                            <Toggle
                                checked={Number(rule.is_active) === 1}
                                onChange={(val) => handleToggleActive(rule.id, val)}
                            />
                        </div>
                    </SettingRow>
                ))}
            </Card>
        </>
    );
};

const PerformanceSettings = () => (
    <Provider store={store}>
        <PerformanceSettingsInner />
    </Provider>
);

export default PerformanceSettings;
