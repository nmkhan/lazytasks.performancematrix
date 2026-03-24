import React, { useState } from 'react';

const PerformanceNavLink = () => {
    const [hovered, setHovered] = useState(false);

    // Guard: only render when the addon is active (window global is set by App.js on load)
    if (!window.lazytasksPerformance) return null;

    const isActive = window.location.hash.startsWith('#/v3/performance-dashboard');

    const style = {
        display: 'inline-flex', alignItems: 'center', gap: 6,
        height: 32, padding: '0 12px',
        borderRadius: 6,
        fontSize: 13, fontWeight: 600,
        border: 'none', cursor: 'pointer',
        transition: 'background 150ms, color 150ms',
        fontFamily: 'inherit',
        background: isActive ? '#18313B' : hovered ? '#d8e8ed' : '#EBF1F4',
        color: isActive ? '#fff' : '#202020',
    };

    return (
        <button
            style={style}
            onClick={() => { window.location.hash = '/v3/performance-dashboard'; }}
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{width: '15px', height: '15px'}}>
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Leaderboard
        </button>
    );
};

export default PerformanceNavLink;
