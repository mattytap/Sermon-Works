<?php // phpcs:ignore
/**
 * Template used for displaying single pages
 *
 * @package SM/Views
 */

defined( 'ABSPATH' ) or die;

get_header(); ?>

<?php echo wp_kses( wpfc_get_partial( 'content-sermon-wrapper-start' ), sm_template_allowed_html() ); ?>

<?php

echo wp_kses_post( apply_filters( 'single-wpfc_sermon-before-sermons', '' ) );

while ( have_posts() ) :
	global $post;
	the_post();

	if ( ! post_password_required( $post ) ) {
		wpfc_sermon_single_v2(); // You can edit the content of this function in `partials/content-sermon-single.php`.
	} else {
		echo wp_kses_post( get_the_password_form( $post ) );
	}

	if ( comments_open() || get_comments_number() ) :
		if ( ! apply_filters( 'single-wpfc_sermon-disable-comments', false ) ) {
			comments_template();
		}
	endif;
endwhile;

echo wp_kses_post( apply_filters( 'single-wpfc_sermon-after-sermons', '' ) );

?>

<?php echo wp_kses( wpfc_get_partial( 'content-sermon-wrapper-end' ), sm_template_allowed_html() ); ?>

<?php
get_footer();
