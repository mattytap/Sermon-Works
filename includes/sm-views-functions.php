<?php
/**
 * Sermon view counter.
 *
 * Replaces the bundled Justin Tadlock entry-views library with a small
 * WP-native implementation. The counter increments a `Views` post meta
 * value on template_redirect for any post type that registers `entry-views`
 * post-type support (Sermon Works registers this for `wpfc_sermon` at
 * includes/class-sm-post-types.php:322). The `[sermon-views]` shortcode
 * reads the same meta.
 *
 * Closes two CVEs filed against the previous bundled vendor:
 *   - CVE-2025-12368 — stored XSS in the shortcode `before` / `after`
 *     attributes, fixed here by esc_html() on both attrs.
 *   - CVE-2025-63002 — unauthenticated state-change via the
 *     wp_ajax_nopriv_wpfc_entry_views handler, closed here by removing
 *     the AJAX surface entirely (counting moves server-side).
 *
 * The wpfc_entry_views_*() function names and the `Views` meta key are
 * preserved unchanged for drop-in compatibility with installs migrating
 * from upstream Sermon Manager.
 *
 * @package SermonWorks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Increment the view count on a singular sermon view.
 *
 * @return void
 */
function wpfc_entry_views_template_redirect() {
	if ( ! is_singular() || is_feed() || is_preview() ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( ! post_type_supports( $post->post_type, 'entry-views' ) ) {
		return;
	}
	wpfc_entry_views_update( $post->ID );
}
add_action( 'template_redirect', 'wpfc_entry_views_template_redirect' );

/**
 * Update the view count for a post.
 *
 * Honours the `sm_views_add_view` filter (caching plugins or hit-throttlers
 * can return false to suppress increments) and the `wpfc_entry_views_meta_key`
 * filter for the meta key (default `Views`).
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wpfc_entry_views_update( $post_id = 0 ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return;
	}
	if ( ! apply_filters( 'sm_views_add_view', true ) ) {
		return;
	}
	$meta_key  = apply_filters( 'wpfc_entry_views_meta_key', 'Views' );
	$old_views = (int) get_post_meta( $post_id, $meta_key, true );
	update_post_meta( $post_id, $meta_key, $old_views + 1, $old_views );
}

/**
 * Render or return the view count for a post.
 *
 * Doubles as the [sermon-views] shortcode handler and as a public function
 * called from the admin sermon-list "views" column at
 * includes/admin/class-sm-admin-post-types.php:131.
 *
 * @param array $attr Shortcode attributes: `before`, `after`, `post_id`.
 * @return string Formatted view count with optional surrounding markup.
 */
function wpfc_entry_views_get( $attr = array() ) {
	$attr = shortcode_atts(
		array(
			'before'  => '',
			'after'   => '',
			'post_id' => get_the_ID(),
		),
		$attr
	);

	$post_id = absint( $attr['post_id'] );
	if ( ! $post_id ) {
		return '';
	}

	$meta_key = apply_filters( 'wpfc_entry_views_meta_key', 'Views' );
	$views    = (int) get_post_meta( $post_id, $meta_key, true );

	return esc_html( $attr['before'] ) . number_format_i18n( $views ) . esc_html( $attr['after'] );
}
add_shortcode( 'sermon-views', 'wpfc_entry_views_get' );
