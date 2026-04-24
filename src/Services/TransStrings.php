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
		];
	}
}
