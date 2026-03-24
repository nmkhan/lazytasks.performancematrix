import React, { useState, useEffect } from 'react';
import PerformanceSettings from './Settings/PerformanceSettings';
import { fetchProjectScores } from '../store/performanceSlice';
import store from '../store';

const PerformanceDashboardInner = () => {
    const [performanceState, setPerformanceState] = useState(store.getState().performance);
    const [activeTab, setActiveTab] = useState('Overview'); // 'Overview' or 'Rules'
    const [timeframe, setTimeframe] = useState('all_time');

    useEffect(() => {
        const unsubscribe = store.subscribe(() => {
            setPerformanceState(store.getState().performance);
        });
        return () => unsubscribe();
    }, []);

    useEffect(() => {
        store.dispatch(fetchProjectScores({ projectId: 0, timeframe }));
    }, [timeframe]);

    const { scores, loading } = performanceState;

    // Aggregate Analytics Calculations
    const maxPoints = scores.length > 0 ? Math.max(...scores.map(s => s.points)) : 1;
    const avgPoints = scores.length > 0 ? Math.round(scores.reduce((sum, s) => sum + s.points, 0) / scores.length) : 0;
    const avgEfficiency = scores.length > 0 ? Math.round(scores.reduce((sum, s) => sum + s.efficiency.ratio, 0) / scores.length) : 0;
    const topScorer = scores.length > 0 ? scores[0] : null;

    return (
        <div className="shell">
            {/* Page Header */}
            <div className="settings-page-header">
                <div className="settings-page-header-left">
                    <h1 className="settings-page-title">Performance & Gamification</h1>
                    <div className="settings-topnav">
                        <button 
                            className={`sn-btn ${activeTab === 'Overview' ? 'sn-btn-active' : 'sn-btn-default'}`}
                            onClick={() => setActiveTab('Overview')}
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{width: '15px', height: '15px'}}>
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </button>
                        <button 
                            className={`sn-btn ${activeTab === 'Rules' ? 'sn-btn-active' : 'sn-btn-default'}`}
                            onClick={() => setActiveTab('Rules')}
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{width: '15px', height: '15px'}}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Scoring Rules
                        </button>
                    </div>
                </div>
                <div>
                    <select
                        className="timeframe-select"
                        value={timeframe}
                        onChange={e => setTimeframe(e.target.value)}
                    >
                        <option value="all_time">All-Time</option>
                        <option value="last_30_days">Last 30 Days</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                    </select>
                </div>
            </div>

            <div className="settings-layout">
                {/* Sidebar */}
                <aside className="sidebar">
                    <div className="sidebar-inner">
                        <div className="sidebar-card">
                            <div className="sidebar-section-label">Performance Dashboard</div>
                            <button className="nav-item active">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Workspace Overview
                            </button>
                            <button className="nav-item">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Company Leaderboard
                            </button>
                        </div>
                    </div>
                </aside>

                {/* Content Area */}
                <main className="content-area">
                    {activeTab === 'Rules' && <PerformanceSettings />}
                    
                    {activeTab === 'Overview' && (
                        <>
                            {/* Stats */}
                            <div className="stats-grid">
                                <div className="stat-card orange">
                                    <div className="stat-title">Top Scorer</div>
                                    <div className="stat-value">{topScorer ? topScorer.points.toLocaleString() : 0}</div>
                                    <div className="stat-trend neutral">{topScorer ? topScorer.name : 'N/A'}</div>
                                </div>
                                <div className="stat-card orange">
                                    <div className="stat-title">Active Participants</div>
                                    <div className="stat-value">{scores.length}</div>
                                    <div className="stat-trend neutral">Ranked Leaderboard</div>
                                </div>
                                <div className="stat-card teal">
                                    <div className="stat-title">Workspace Avg</div>
                                    <div className="stat-value">{avgPoints.toLocaleString()}</div>
                                    <div className="stat-trend neutral">Total Points Earned</div>
                                </div>
                                <div className="stat-card teal">
                                    <div className="stat-title">Avg Efficiency</div>
                                    <div className="stat-value">{avgEfficiency}%</div>
                                    <div className="stat-trend neutral">Completed / Assigned</div>
                                </div>
                            </div>

                            {/* Scatterplot */}
                            <div className="card">
                                <div className="card-title">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                        <path d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>
                                    Workspace Engagement Matrix
                                </div>
                                <div className="scatter-container">
                                    <div className="scatter-axis-y">Effectiveness (Points Earned)</div>
                                    <div className="scatter-axis-x">Efficiency (Completion Rate %)</div>
                                    
                                    {scores.map((user) => {
                                        let top = 100 - ((user.points / maxPoints) * 100);
                                        top = Math.max(10, Math.min(top, 90)); // Contain within bounds
                                        const left = Math.max(10, Math.min(user.efficiency.ratio, 90));
                                        const size = 30 + ((user.points / maxPoints) * 20); // Scale size from 30px to 50px
                                        
                                        return (
                                            <div
                                                key={`scatter-${user.id}`}
                                                className="bubble b-user"
                                                style={{
                                                    width: `${size}px`,
                                                    height: `${size}px`,
                                                    left: `${left}%`,
                                                    top: `${top}%`,
                                                    background: user.bg,
                                                    border: `2px solid ${user.color || '#fff'}`,
                                                    color: '#fff'
                                                }}
                                            >
                                                {user.initials}
                                                <div className="bubble-tooltip">
                                                    <div className="bt-name">{user.name}</div>
                                                    <div className="bt-stat">{user.points.toLocaleString()} pts</div>
                                                    <div className="bt-stat">{user.efficiency.ratio}% Efficiency</div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Leaderboard */}
                            <div className="card">
                                <div className="card-title">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                        <path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                    Top Performers
                                </div>
                                
                                {loading && <div style={{padding: '20px', textAlign: 'center'}}>Loading Workspace Analytics...</div>}
                                {!loading && scores.length === 0 && <div style={{padding: '20px', textAlign: 'center'}}>No gameplay scores tracked yet. Create/Complete tasks to earn points!</div>}
                                
                                {!loading && scores.length > 0 && (
                                    <div className="leaderboard-list">
                                        {scores.map((user) => (
                                            <div className="lb-row" key={`lb-${user.id}`}>
                                                <div className="lb-rank" style={{ color: user.color }}>#{user.rank}</div>
                                                <div className="user-avatar-badge" style={{ background: user.bg }}>{user.initials}</div>
                                                <div className="user-info">
                                                    <div className="user-name">{user.name}</div>
                                                    <div className="user-role">{user.role} &middot; {user.efficiency.ratio}% Efficiency</div>
                                                </div>
                                                <div className="lb-score">
                                                    <div className="lb-points">{user.points.toLocaleString()} pts</div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </main>
            </div>
        </div>
    );
};

export default PerformanceDashboardInner;
