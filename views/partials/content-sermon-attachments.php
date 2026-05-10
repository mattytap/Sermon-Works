<?php
/**
 * To edit this file, please copy the contents of this file to one of these locations:
 * - `/wp-content/themes/<your_theme>/partials/content-sermon-attachments.php`
 * - `/wp-content/themes/<your_theme>/template-parts/content-sermon-attachments.php`
 * - `/wp-content/themes/<your_theme>/content-sermon-attachments.php`
 *
 * That will ensure that your changes are not deleted on plugin update.
 *
 * Sometimes, we need to edit this file to add new features or to fix some bugs, and when we do so, we will modify the
 * changelog in this header comment.
 *
 * @package SermonManager\Views\Partials
 *
 * @since   2.13.0 - added
 * @since   3.0.1  - render `sermon_notes_multiple` and `sermon_bulletin_multiple` arrays alongside the singular keys
 */

defined( 'ABSPATH' ) or die;

global $post;

$notes             = get_wpfc_sermon_meta( 'sermon_notes' );
$notes_multiple    = get_wpfc_sermon_meta( 'sermon_notes_multiple' );
$bulletin          = get_wpfc_sermon_meta( 'sermon_bulletin' );
$bulletin_multiple = get_wpfc_sermon_meta( 'sermon_bulletin_multiple' );
?>
<div id="wpfc-attachments" class="cf">
	<p>
		<strong><?php echo esc_html__( 'Download Files', 'sermon-works' ); ?></strong>

		<?php if ( $notes ) : ?>
			<a href="<?php echo esc_url( $notes ); ?>"
				class="sermon-attachments"
				download="<?php echo esc_attr( basename( $notes ) ); ?>">
				<span class="dashicons dashicons-media-document"></span>
				<?php echo esc_html__( 'Notes', 'sermon-works' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( is_array( $notes_multiple ) && ! empty( $notes_multiple ) ) : ?>
			<?php foreach ( $notes_multiple as $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"
					class="sermon-attachments"
					download="<?php echo esc_attr( basename( $url ) ); ?>">
					<span class="dashicons dashicons-media-document"></span>
					<?php echo esc_html__( 'Notes', 'sermon-works' ); ?>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( $bulletin ) : ?>
			<a href="<?php echo esc_url( $bulletin ); ?>"
				class="sermon-attachments"
				download="<?php echo esc_attr( basename( $bulletin ) ); ?>">
				<span class="dashicons dashicons-media-document"></span>
				<?php echo esc_html__( 'Bulletin', 'sermon-works' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( is_array( $bulletin_multiple ) && ! empty( $bulletin_multiple ) ) : ?>
			<?php foreach ( $bulletin_multiple as $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"
					class="sermon-attachments"
					download="<?php echo esc_attr( basename( $url ) ); ?>">
					<span class="dashicons dashicons-media-document"></span>
					<?php echo esc_html__( 'Bulletin', 'sermon-works' ); ?>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</p>
</div>
