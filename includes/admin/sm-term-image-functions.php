<?php
/**
 * Term-image admin UX for sermon taxonomies.
 *
 * Replaces the bundled taxonomy-images vendor library's term-edit form
 * extension with a small WP-native implementation. Renders an image
 * picker on the term-edit and term-add pages for wpfc_sermon_series,
 * wpfc_preacher, and wpfc_sermon_topics; persists the chosen attachment
 * ID to term_meta key sm_term_image_id (the same key the migrator
 * sm_update_2160_migrate_term_images() populates from the legacy
 * sermon_image_plugin option).
 *
 * Loaded only on the admin side via sermons.php's is_admin() block.
 *
 * @package SermonWorks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomies that get the image-picker UI.
 *
 * @return string[]
 */
function sm_term_image_taxonomies() {
	return array( 'wpfc_sermon_series', 'wpfc_preacher', 'wpfc_sermon_topics' );
}

/**
 * Register the term-image admin hooks.
 *
 * Runs on admin_init so the taxonomy-specific hook names are bound after
 * SM_Post_types::register_taxonomies() has run on 'init' priority 0.
 */
function sm_term_image_register_hooks() {
	foreach ( sm_term_image_taxonomies() as $taxonomy ) {
		add_action( "{$taxonomy}_edit_form_fields", 'sm_term_image_render_edit_field', 10, 2 );
		add_action( "{$taxonomy}_add_form_fields", 'sm_term_image_render_add_field', 10, 1 );
		add_action( "edited_{$taxonomy}", 'sm_term_image_save', 10, 1 );
		add_action( "created_{$taxonomy}", 'sm_term_image_save', 10, 1 );
	}
}
add_action( 'admin_init', 'sm_term_image_register_hooks' );

/**
 * Enqueue media + the picker JS on relevant admin screens.
 *
 * @param string $hook Current admin page hook.
 */
function sm_term_image_enqueue_scripts( $hook ) {
	if ( 'term.php' !== $hook && 'edit-tags.php' !== $hook ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->taxonomy, sm_term_image_taxonomies(), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'sm-term-image-picker',
		SM_URL . 'assets/js/admin/term-image-picker.js',
		array( 'jquery' ),
		SM_VERSION,
		true
	);
	wp_localize_script(
		'sm-term-image-picker',
		'smTermImagePicker',
		array(
			'modalTitle'  => __( 'Select or upload image', 'sermon-works' ),
			'modalButton' => __( 'Use this image', 'sermon-works' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'sm_term_image_enqueue_scripts' );

/**
 * Render the image picker on the term-edit page.
 *
 * Hooked to {taxonomy}_edit_form_fields. Hook fires inside a
 * <table class="form-table"> so the field is wrapped in a <tr>.
 *
 * @param WP_Term $term     Term being edited.
 * @param string  $taxonomy Taxonomy slug.
 */
function sm_term_image_render_edit_field( $term, $taxonomy ) {
	$image_id  = (int) get_term_meta( $term->term_id, 'sm_term_image_id', true );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	?>
	<tr class="form-field sm-term-image-field">
		<th scope="row"><label for="sm_term_image_id"><?php esc_html_e( 'Image', 'sermon-works' ); ?></label></th>
		<td>
			<?php wp_nonce_field( 'sm_term_image_save', 'sm_term_image_nonce' ); ?>
			<div class="sm-term-image-preview"<?php echo $image_url ? '' : ' style="display:none"'; ?>>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:150px;height:auto;display:block;margin-bottom:6px;" />
			</div>
			<input type="hidden" name="sm_term_image_id" id="sm_term_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
			<button type="button" class="button sm-term-image-add"><?php esc_html_e( 'Add Image', 'sermon-works' ); ?></button>
			<button type="button" class="button sm-term-image-remove"<?php echo $image_id ? '' : ' style="display:none"'; ?>><?php esc_html_e( 'Remove Image', 'sermon-works' ); ?></button>
		</td>
	</tr>
	<?php
}

/**
 * Render the image picker on the term-add page.
 *
 * Hooked to {taxonomy}_add_form_fields. The hook fires outside a table,
 * so the field is wrapped in a <div class="form-field"> per WP convention.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function sm_term_image_render_add_field( $taxonomy ) {
	?>
	<div class="form-field sm-term-image-field">
		<label for="sm_term_image_id"><?php esc_html_e( 'Image', 'sermon-works' ); ?></label>
		<?php wp_nonce_field( 'sm_term_image_save', 'sm_term_image_nonce' ); ?>
		<div class="sm-term-image-preview" style="display:none">
			<img src="" alt="" style="max-width:150px;height:auto;display:block;margin-bottom:6px;" />
		</div>
		<input type="hidden" name="sm_term_image_id" id="sm_term_image_id" value="" />
		<button type="button" class="button sm-term-image-add"><?php esc_html_e( 'Add Image', 'sermon-works' ); ?></button>
		<button type="button" class="button sm-term-image-remove" style="display:none"><?php esc_html_e( 'Remove Image', 'sermon-works' ); ?></button>
	</div>
	<?php
}

/**
 * Save the term-image association on edit / create.
 *
 * Hooked to edited_{taxonomy} and created_{taxonomy}. WP core's nonce
 * check on the edit-tag / add-tag form has already passed by the time
 * either action fires (see wp-admin/edit-tags.php), so the
 * sm_term_image_nonce check below is defence-in-depth — it fails closed
 * if some other code path triggers wp_update_term() / wp_insert_term()
 * with an attacker-controlled $_POST['sm_term_image_id'].
 *
 * @param int $term_id Term ID.
 */
function sm_term_image_save( $term_id ) {
	if ( ! isset( $_POST['sm_term_image_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['sm_term_image_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'sm_term_image_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_wpfc_categories' ) ) {
		return;
	}

	$image_id = isset( $_POST['sm_term_image_id'] ) ? absint( wp_unslash( $_POST['sm_term_image_id'] ) ) : 0;
	if ( $image_id ) {
		update_term_meta( (int) $term_id, 'sm_term_image_id', $image_id );
	} else {
		delete_term_meta( (int) $term_id, 'sm_term_image_id' );
	}
}
