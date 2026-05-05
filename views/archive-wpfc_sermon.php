<?php // phpcs:ignore
/**
 * Template used for displaying archive pages
 *
 * @package SM/Views
 */

defined( 'ABSPATH' ) or die;

get_header(); ?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Partial emits sermon archive wrapper HTML; per-site escapers are applied inside the partial. ?>

<?php
echo render_wpfc_sorting(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns sermon-sorting widget HTML built internally; safe by construction.

if ( have_posts() ) :

	echo apply_filters( 'archive-wpfc_sermon-before-sermons', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme/plugin extension point for HTML markup; sermon admins control filter implementations.

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2(); // You can edit the content of this function in `partials/content-sermon-archive.php`.
	endwhile;

	echo apply_filters( 'archive-wpfc_sermon-after-sermons', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme/plugin extension point for HTML markup; sermon admins control filter implementations.

	echo '<div class="sm-pagination ast-pagination">';
	sm_pagination();
	echo '</div>';
else :
	echo esc_html__( 'Sorry, but there aren\'t any posts matching your query.', 'sermon-works' );
endif;
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Partial emits closing wrapper HTML; escapers applied inside the partial. ?>

<?php
get_footer();
