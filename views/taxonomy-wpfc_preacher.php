<?php // phpcs:ignore
/**
 * Template used for displaying taxonomy archive pages
 *
 * @package SM/Views
 */

defined( 'ABSPATH' ) or die;

get_header();
?>

<?php echo wp_kses( wpfc_get_partial( 'content-sermon-wrapper-start' ), sm_template_allowed_html() ); ?>

<?php
echo wp_kses( render_wpfc_sorting(), sm_template_allowed_html() );

if ( have_posts() ) :

	echo wp_kses_post( apply_filters( 'taxonomy-wpfc_preacher-before-sermons', '' ) );

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2();
	endwhile;

	echo wp_kses_post( apply_filters( 'taxonomy-wpfc_preacher-after-sermons', '' ) );

	echo '<div class="sm-pagination ast-pagination">';
	sm_pagination();
	echo '</div>';
else :
	echo esc_html__( 'Sorry, but there are no posts matching your query.', 'mattytap-sermons' );
endif;
?>

<?php echo wp_kses( wpfc_get_partial( 'content-sermon-wrapper-end' ), sm_template_allowed_html() ); ?>

<?php
get_footer();
