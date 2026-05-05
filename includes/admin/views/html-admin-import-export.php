<?php
/**
 * HTML for import/export page.
 *
 * @package SM/Core/Admin/Views
 */

defined( 'ABSPATH' ) or die;
?>
<div class="sm wrap">
	<div class="intro">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Sermon Works Import/Export', 'sermon-works' ); ?></h1>
	</div>
	<div class="wp-list-table widefat">
		<p><?php esc_html_e( 'We have made it easy to backup, migrate or bring sermons from another plugin. Choose the relevant option below to get started.', 'sermon-works' ); ?></p>
		<div id="the-list">
			<div class="plugin-card card-import-sm">
				<div class="plugin-card-top">
					<img src="<?php echo esc_url( SM_URL ); ?>assets/images/import-sm.jpg" class="plugin-icon"
							alt="<?php esc_attr_e( 'Import from file', 'sermon-works' ); ?>">
					<div class="name column-name">
						<h3>
							<?php esc_html_e( 'Import from file', 'sermon-works' ); ?>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li>
								<?php
								$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
								$size       = size_format( $bytes );
								$upload_dir = wp_upload_dir();
								if ( ! empty( $upload_dir['error'] ) ) :
									?>
									<div class="error">
										<p>
											<?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:', 'sermon-works' ); ?>
										</p>
										<p>
											<strong>
												<?php echo esc_html( $upload_dir['error'] ); ?>
											</strong>
										</p>
									</div>
								<?php else : ?>
									<form enctype="multipart/form-data" id="sm-import-upload-form" method="post"
											class="wp-upload-form"
											action="<?php echo esc_url( wp_nonce_url( $_SERVER['REQUEST_URI'] . '&doimport=sm', 'sm_import_export_sm' ) ); ?>">
										<p>
											<input type="file" id="upload" name="import" size="25"/>
											<input type="hidden" name="action" value="save"/>
											<input type="hidden" name="max_file_size" value="<?php echo (int) $bytes; ?>"/>
										</p>
										<input class="button" id="submit" type="submit" name="submit"
												value="<?php esc_attr_e( 'Import from file', 'sermon-works' ); ?>"/>
									</form>
									<span class="button activate-now" id="sm-import-trigger">
										<span>
											<?php esc_html_e( 'Import', 'sermon-works' ); ?>
										</span>
										<span class="import-sniper">
											<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>">
										</span>
									</span>
								<?php endif; ?>
							</li>
							<li><a href="" class=""
										aria-label="<?php esc_attr_e( 'More Details', 'sermon-works' ); ?>">
									<?php esc_html_e( 'More Details', 'sermon-works' ); ?>
								</a></li>
						</ul>
					</div>
					<div class="desc column-description">
						<p><?php esc_html_e( 'Import sermons from another Sermon Works (or Sermon Manager) installation.', 'sermon-works' ); ?></p>
					</div>
				</div>
			</div>
			<div class="plugin-card card-export-sm">
				<div class="plugin-card-top">
					<img src="<?php echo esc_url( SM_URL ); ?>assets/images/export-sm.jpg" class="plugin-icon"
							alt="<?php esc_attr_e( 'Export to file', 'sermon-works' ); ?>">
					<div class="name column-name">
						<h3>
							<?php esc_html_e( 'Export to file', 'sermon-works' ); ?>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'doimport', 'exsm' ), 'sm_import_export_exsm' ) ); ?>"
										class="button activate-now" id="sm-export-content"
										aria-label="<?php esc_attr_e( 'Export to file', 'sermon-works' ); ?>">
									<?php esc_html_e( 'Export', 'sermon-works' ); ?>
								</a>
							</li>
							<li><a href="" class=""
										aria-label="<?php esc_attr_e( 'More Details', 'sermon-works' ); ?>">
									<?php esc_html_e( 'More Details', 'sermon-works' ); ?></a></li>
						</ul>
					</div>
					<div class="desc column-description">
						<p><?php esc_html_e( 'Create an export for the purpose of backup or migration to another website.', 'sermon-works' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wp-list-table widefat">
		<h2><?php esc_html_e( 'Import From 3rd Party Plugins', 'sermon-works' ); ?></h2>
		<p><?php esc_html_e( 'You can import sermons from the following plugins into Sermon Works', 'sermon-works' ); ?></p>
		<div id="the-list">
			<div class="plugin-card <?php echo SM_Import_SB::is_installed() ? '' : 'not-available'; ?>">
				<h2>Plugin not installed</h2>
				<div class="plugin-card-top">
					<img src="<?php echo esc_url( SM_URL ); ?>assets/images/import-sb.jpg" class="plugin-icon"
							alt="<?php esc_attr_e( 'Sermon Browser', 'sermon-works' ); ?>">
					<div class="name column-name">
						<h3>
							<?php esc_html_e( 'Sermon Browser', 'sermon-works' ); ?>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'doimport', 'sb' ), 'sm_import_export_sb' ) ); ?>"
										class="button activate-now <?php echo SM_Import_SB::is_installed() ? '' : 'disabled'; ?>"
										aria-label="<?php esc_attr_e( 'Import from Sermon Browser', 'sermon-works' ); ?>">
									<?php esc_html_e( 'Import', 'sermon-works' ); ?></a>
							</li>
						</ul>
					</div>
					<div class="desc column-description">
						<p>
							<?php
							// translators: %s Plugin name.
							echo esc_html( wp_sprintf( __( 'Import your existing %s sermon library into Sermon Works', 'sermon-works' ), 'Sermon Browser' ) );
							?>
						</p>
						<p class="import-note">
							<?php esc_html_e( 'Note: Some restrictions apply.', 'sermon-works' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="plugin-card <?php echo SM_Import_SE::is_installed() ? '' : 'not-available'; ?>">
				<h2>Plugin not installed</h2>
				<div class="plugin-card-top">
					<img src="<?php echo esc_url( SM_URL ); ?>assets/images/import-se.jpg" class="plugin-icon"
							alt="<?php esc_attr_e( 'Series Engine', 'sermon-works' ); ?>">
					<div class="name column-name">
						<h3>
							<?php esc_html_e( 'Series Engine', 'sermon-works' ); ?>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'doimport', 'se' ), 'sm_import_export_se' ) ); ?>"
										class="button activate-now <?php echo SM_Import_SE::is_installed() ? '' : 'disabled'; ?>"
										aria-label="<?php esc_attr_e( 'Import from Series Engine', 'sermon-works' ); ?>">
									<?php esc_html_e( 'Import', 'sermon-works' ); ?></a>
							</li>
						</ul>
					</div>
					<div class="desc column-description">
						<p>
							<?php
							// translators: %s Plugin name.
							echo esc_html( wp_sprintf( __( 'Import your existing %s sermon library into Sermon Works', 'sermon-works' ), 'Series Engine' ) );
							?>
						</p>
						<p class="import-note">
							<?php esc_html_e( 'Note: Some restrictions apply.', 'sermon-works' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<p class="description">
		<?php esc_html_e( 'Note: We recommend you create a backup of your current database just in case.', 'sermon-works' ); ?>
	</p>
</div>
