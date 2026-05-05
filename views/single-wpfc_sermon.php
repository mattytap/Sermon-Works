<?php // phpcs:ignore
/**
 * Template used for displaying single pages
 *
 * @package SM/Views
 */

defined( 'ABSPATH' ) or die;

get_header(); ?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Partial emits sermon archive wrapper HTML; per-site escapers are applied inside the partial. ?>

<?php

echo apply_filters( 'single-wpfc_sermon-before-sermons', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme/plugin extension point for HTML markup; sermon admins control filter implementations.

while ( have_posts() ) :
	global $post;
	the_post();

	if ( ! post_password_required( $post ) ) {
		wpfc_sermon_single_v2(); // You can edit the content of this function in `partials/content-sermon-single.php`.
	} else {
		echo get_the_password_form( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress core function returns trusted password form markup.
	}

	if ( comments_open() || get_comments_number() ) :
		if ( ! apply_filters( 'single-wpfc_sermon-disable-comments', false ) ) {
			comments_template();
		}
	endif;
endwhile;

echo apply_filters( 'single-wpfc_sermon-after-sermons', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme/plugin extension point for HTML markup; sermon admins control filter implementations.

?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Partial emits closing wrapper HTML; escapers applied inside the partial. ?>

<?php
get_footer();
