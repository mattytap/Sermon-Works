<?php
/**
 * Admin View: Settings
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

$current_tab = empty( $current_tab ) ? 'general' : $current_tab;
?>
<div class="wrap sm sm_settings_<?php echo esc_attr( $current_tab ); ?>">
	<div class="intro">
		<h1 class="wp-heading-inline">Sermon Works Settings</h1>
	</div>
	<?php SM_Admin_Settings::show_messages(); ?>
	<div class="settings-main">
		<div class="settings-content">
			<form method="<?php echo esc_attr( apply_filters( 'sm_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>"
					id="mainform" action="" enctype="multipart/form-data">
				<nav class="nav-tab-wrapper sm-nav-tab-wrapper">
					<?php
					foreach ( $tabs as $name => $label ) {
						echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=wpfc_sermon&page=sm-settings&tab=' . $name ) ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
					}
					do_action( 'sm_settings_tabs' );
					?>
				</nav>
				<div class="inside">
					<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
					<?php
					do_action( 'sm_sections_' . $current_tab );
					do_action( 'sm_settings_' . $current_tab );
					?>
					<p class="submit">
						<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
							<input name="save" class="button-primary sm-save-button" type="submit"
									value="<?php esc_attr_e( 'Save changes', 'sermon-works' ); ?>"/>
						<?php endif; ?>
						<?php wp_nonce_field( 'sm-settings' ); ?>
					</p>
				</div>
			</form>
		</div>
		<div class="settings-side">
		   <?php
			echo wp_kses_post( apply_filters( 'settings_page_sidebar_extra_boxs', $arg='' ) );
			?>
		</div>
	</div>
</div>
