<?php

namespace Lazytask_Performance\Services;

/**
 * Central registry of user-facing translatable strings for the LazyTasks
 * Performance addon. Loaded into React via wp_localize_script as
 * window.appLocalizerPerformance.i18n; callers use the translate() helper
 * at admin/frontend/src/utils/i18n.js.
 *
 * New strings for brand-new features should ideally be wrapped with
 * @wordpress/i18n directly in JSX per the hybrid plan — this file stays
 * for strings that were hardcoded before the i18n rollout and for
 * convenience keys reused across many components.
 */
class TransStrings {

	public static function getStrings() {
		return [
			// Dashboard — page chrome
			'Performance & Gamification' => __( 'Performance & Gamification', 'lazytasks-performance' ),
			'Dashboard'                  => __( 'Dashboard', 'lazytasks-performance' ),
			'Scoring Rules'              => __( 'Scoring Rules', 'lazytasks-performance' ),
			'Performance Dashboard'      => __( 'Performance Dashboard', 'lazytasks-performance' ),
			'Workspace Overview'         => __( 'Workspace Overview', 'lazytasks-performance' ),
			'Company Leaderboard'        => __( 'Company Leaderboard', 'lazytasks-performance' ),

			// Timeframe selector
			'All-Time'      => __( 'All-Time', 'lazytasks-performance' ),
			'Last 30 Days'  => __( 'Last 30 Days', 'lazytasks-performance' ),
			'This Quarter'  => __( 'This Quarter', 'lazytasks-performance' ),
			'This Year'     => __( 'This Year', 'lazytasks-performance' ),

			// Stat cards
			'Top Scorer'           => __( 'Top Scorer', 'lazytasks-performance' ),
			'Active Participants'  => __( 'Active Participants', 'lazytasks-performance' ),
			'Ranked Leaderboard'   => __( 'Ranked Leaderboard', 'lazytasks-performance' ),
			'Workspace Avg'        => __( 'Workspace Avg', 'lazytasks-performance' ),
			'Total Points Earned'  => __( 'Total Points Earned', 'lazytasks-performance' ),
			'Avg Efficiency'       => __( 'Avg Efficiency', 'lazytasks-performance' ),
			'Completed / Assigned' => __( 'Completed / Assigned', 'lazytasks-performance' ),

			// Charts
			'Workspace Engagement Matrix'     => __( 'Workspace Engagement Matrix', 'lazytasks-performance' ),
			'Effectiveness (Points Earned)'   => __( 'Effectiveness (Points Earned)', 'lazytasks-performance' ),
			'Efficiency (Completion Rate %)'  => __( 'Efficiency (Completion Rate %)', 'lazytasks-performance' ),
			'Top Performers'                  => __( 'Top Performers', 'lazytasks-performance' ),

			// Empty / loading states
			'Loading Workspace Analytics...' => __( 'Loading Workspace Analytics...', 'lazytasks-performance' ),
			'No gameplay scores tracked yet. Create/Complete tasks to earn points!' => __( 'No gameplay scores tracked yet. Create/Complete tasks to earn points!', 'lazytasks-performance' ),

			// Nav link (PerformanceNavLink)
			'Leaderboard' => __( 'Leaderboard', 'lazytasks-performance' ),

			// Tooltip fragments (on scatterplot bubbles)
			'Efficiency' => __( 'Efficiency', 'lazytasks-performance' ),

			// Settings panel (PerformanceSettings.jsx)
			'Performance'                         => __( 'Performance', 'lazytasks-performance' ),
			'Failed to save rules'                => __( 'Failed to save rules', 'lazytasks-performance' ),
			'Rules updated successfully'          => __( 'Rules updated successfully', 'lazytasks-performance' ),
			'Executing a manual synchronization will recalculate ALL gamification scores across your workspace from the beginning of time. Are you sure you want to proceed?' => __( 'Executing a manual synchronization will recalculate ALL gamification scores across your workspace from the beginning of time. Are you sure you want to proceed?', 'lazytasks-performance' ),
			'Historical synchronization complete!' => __( 'Historical synchronization complete!', 'lazytasks-performance' ),
			'Loading...'                          => __( 'Loading...', 'lazytasks-performance' ),
			'Configure scoring rules, point values, and sync historical activity data.' => __( 'Configure scoring rules, point values, and sync historical activity data.', 'lazytasks-performance' ),
			'Saving...'                           => __( 'Saving...', 'lazytasks-performance' ),
			'Save Changes'                        => __( 'Save Changes', 'lazytasks-performance' ),
			'Historical Data Available'           => __( 'Historical Data Available', 'lazytasks-performance' ),
			"Your installation has past activity logs. Sync them now to retroactively calculate your team's gamification scores based on the current rule weights!" => __( "Your installation has past activity logs. Sync them now to retroactively calculate your team's gamification scores based on the current rule weights!", 'lazytasks-performance' ),
			'Synchronizing...'                    => __( 'Synchronizing...', 'lazytasks-performance' ),
			'Sync (or Re-Sync) Historical Data'   => __( 'Sync (or Re-Sync) Historical Data', 'lazytasks-performance' ),
			'Gamification Scoring Rules'          => __( 'Gamification Scoring Rules', 'lazytasks-performance' ),
			'Pts'                                 => __( 'Pts', 'lazytasks-performance' ),
		];
	}
}
