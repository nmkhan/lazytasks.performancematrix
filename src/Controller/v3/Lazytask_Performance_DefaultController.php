<?php

namespace Lazytask_Performance\Controller\v3;

use WP_REST_Request;
use WP_Error;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Base controller for all Performance Addon routes.
 *
 * Provides JWT validation (authentication) and permission checks against the
 * main plugin's permission tables — the same source of truth used by the
 * main plugin's V3 controllers.
 *
 * Do not delegate to main plugin classes — use LAZYTASK_JWT_SECRET_KEY and
 * LAZYTASK_TABLE_PREFIX directly so the addon has no coupling to main plugin
 * internal class names.
 *
 * @since 1.0.0
 */
class Lazytask_Performance_DefaultController {

	/**
	 * Validate the JWT token in the Authorization header.
	 *
	 * Authentication only — does NOT read permissions from the token.
	 * Returns the decoded payload array on success, WP_Error on failure.
	 */
	protected function validate_token( WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'Authorization' );

		if ( ! $auth_header ) {
			return new WP_Error( 'jwt_auth_no_auth_header', 'Authorization header not found.', [ 'status' => 403 ] );
		}

		[ $token ] = sscanf( $auth_header, 'Bearer %s' );

		if ( ! $token ) {
			return new WP_Error( 'jwt_auth_bad_auth_header', 'Authorization header is required.', [ 'status' => 403 ] );
		}

		$secret_key = defined( 'LAZYTASK_JWT_SECRET_KEY' ) ? LAZYTASK_JWT_SECRET_KEY : false;
		if ( ! $secret_key ) {
			return new WP_Error( 'jwt_auth_bad_config', 'JWT is not configured properly.', [ 'status' => 403 ] );
		}

		try {
			$decoded = JWT::decode( $token, new Key( LAZYTASK_JWT_SECRET_KEY, 'HS256' ) );

			if ( $decoded->iss !== get_bloginfo( 'url' ) ) {
				return new WP_Error( 'jwt_auth_bad_iss', 'The iss does not match this server.', [ 'status' => 403 ] );
			}

			if ( ! isset( $decoded->data->user_id ) ) {
				return new WP_Error( 'jwt_auth_bad_request', 'User ID not found in the token.', [ 'status' => 403 ] );
			}

			if ( time() > $decoded->exp ) {
				return new WP_Error( 'jwt_auth_bad_request', 'Token has expired.', [ 'status' => 408 ] );
			}

			return [
				'code'   => 'jwt_auth_valid_token',
				'status' => 200,
				'data'   => [ 'token' => $decoded, 'status' => 200 ],
			];

		} catch ( Exception $e ) {
			return new WP_Error( 'jwt_auth_invalid_token', $e->getMessage(), [ 'status' => 403 ] );
		}
	}

	/**
	 * Route permission_callback for all performance routes.
	 *
	 * - Empty $permissions → auth-only: any authenticated user passes.
	 * - Non-empty $permissions → user must hold at least one listed global permission
	 *   (live DB query against main plugin's permission tables).
	 */
	public function permission_check( WP_REST_Request $request, array $permissions = [] ) {
		$response = $this->validate_token( $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $permissions ) ) {
			return true;
		}

		$userId = (int) $response['data']['token']->data->user_id;

		// WP admin bypass
		$user = get_userdata( $userId );
		if ( $user && in_array( 'administrator', (array) $user->roles ) ) {
			return true;
		}

		global $wpdb;

		// Plugin superadmin bypass — pms_is_superadmin is the V3 source of truth
		$isSuperAdmin = (bool) $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . LAZYTASK_TABLE_PREFIX . 'is_superadmin WHERE user_id = %d',
			$userId
		) );
		if ( $isSuperAdmin ) {
			return true;
		}

		// Live global-permissions query across all the user's project roles
		$globalPermissions = $wpdb->get_col( $wpdb->prepare(
			'SELECT DISTINCT p.name
			 FROM '      . LAZYTASK_TABLE_PREFIX . 'user_has_project_roles uhpr
			 INNER JOIN ' . LAZYTASK_TABLE_PREFIX . 'roles r             ON r.id  = uhpr.role_id
			 INNER JOIN ' . LAZYTASK_TABLE_PREFIX . 'role_has_permissions rhp ON rhp.role_id = r.id
			 INNER JOIN ' . LAZYTASK_TABLE_PREFIX . 'permissions p        ON p.id  = rhp.permission_id
			 WHERE uhpr.user_id = %d
			   AND p.permission_type = %s',
			$userId,
			'global'
		) ) ?: [];

		if ( empty( array_intersect( $globalPermissions, $permissions ) ) ) {
			return new WP_Error(
				'jwt_auth_bad_request',
				'You do not have permission to access this resource.',
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Extract the authenticated user ID from the request's JWT.
	 */
	protected function get_user_id_from_request( WP_REST_Request $request ) {
		$response = $this->validate_token( $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return (int) $response['data']['token']->data->user_id;
	}

	/**
	 * Project-scoped permission check — used inside controller methods.
	 */
	public function check_project_permission( int $userId, int $projectId, array $required ): bool {
		global $wpdb;

		$user = get_userdata( $userId );
		if ( $user && in_array( 'administrator', (array) $user->roles ) ) {
			return true;
		}

		$isSuperAdmin = (bool) $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . LAZYTASK_TABLE_PREFIX . 'is_superadmin WHERE user_id = %d',
			$userId
		) );
		if ( $isSuperAdmin ) {
			return true;
		}

		$placeholders = implode( ', ', array_fill( 0, count( $required ), '%s' ) );
		$args         = array_merge( [ $userId, $projectId ], $required );

		$count = $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*)
			 FROM '      . LAZYTASK_TABLE_PREFIX . 'user_has_project_roles uhpr
			 INNER JOIN ' . LAZYTASK_TABLE_PREFIX . 'role_has_permissions rhp ON rhp.role_id = uhpr.role_id
			 INNER JOIN ' . LAZYTASK_TABLE_PREFIX . 'permissions p            ON p.id        = rhp.permission_id
			 WHERE uhpr.user_id    = %d
			   AND uhpr.project_id = %d
			   AND p.name IN (' . $placeholders . ')',
			...$args
		) );

		return (int) $count > 0;
	}
}
