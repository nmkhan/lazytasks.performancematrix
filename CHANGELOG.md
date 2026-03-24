# Changelog

All notable changes to the LazyTasks Performance Scorer addon are documented here.

---

## Features

### Performance Dashboard
- Scatter chart showing task completion performance per team member
- Bubble colors use hex values for consistent rendering across themes
- Custom hover tooltips on scatter bubbles showing member name, score, and task count
- CSS stylesheet for dashboard layout and chart styling

### Permissions
- `performance-scorer` permission in the Setting group (global scope)
- Gates access to the Performance Scorer settings tab and dashboard

---

## [Unreleased]

### Initial Release (v0.0.5)

1. `2026-03-24 13:20` — Initial commit: Performance Matrix addon v0.0.5 with scatter chart dashboard, PHP REST API, DB schema, and React/Redux frontend

### Bug Fixes & Improvements

2. `2026-03-24 13:35` — Add dedicated CSS stylesheet and enqueue for performance dashboard styling
3. `2026-03-24 13:39` — Fix: use hex colors instead of CSS custom properties for scatter bubble and badge backgrounds (consistent cross-theme rendering)
4. `2026-03-24 13:50` — Fix: custom hover tooltip on scatter chart bubbles replacing default Chart.js tooltip
5. `2026-03-24 14:20` — Fix: move `performance-scorer` permission from AddOn Permissions group to Setting group (global scope, matches other setting-level permissions)
6. `2026-03-24 17:36` — Refactor: add `get_wpdb()` helper method to DefaultController, replacing repeated `global $wpdb` declarations
