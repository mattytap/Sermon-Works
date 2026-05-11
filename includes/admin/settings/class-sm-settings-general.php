<?php
/**
 * General settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_General extends SM_Settings_Page {
	/**
	 * SM_Settings_General constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'mattytap-sermons' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_general_settings', array(

			array(
				'title' => __( 'General Settings', 'mattytap-sermons' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'general_settings',
			),
			array(
				'title'   => __( 'Audio & Video Player', 'mattytap-sermons' ),
				'type'    => 'select',
				'desc'    => __( 'Select which player to use for playing Sermons.', 'mattytap-sermons' ),
				'id'      => 'player',
				'options' => array(
					'plyr'         => 'Plyr',
					'mediaelement' => 'Mediaelement',
					'WordPress'    => 'Old WordPress player',
					'none'         => 'Browser HTML5',
				),
				'default' => 'plyr',
			),
			array(
				'title'   => __( 'Sermon Date Format', 'mattytap-sermons' ),
				'type'    => 'select',
				'desc'    => __( '(used only in admin area, when creating a new Sermon)', 'mattytap-sermons' ),
				'id'      => 'date_format',
				'options' => array(
					'0' => 'mm/dd/YY',
					'1' => 'dd/mm/YY',
					'2' => 'YY/mm/dd',
					'3' => 'YY/dd/mm',
				),
				'default' => '0',
			),
			array(
				'title'   => __( 'Sermons Per Page (default)', 'mattytap-sermons' ),
				'type'    => 'number',
				'desc'    => __( '(Affects only the default number, other settings will override it)', 'mattytap-sermons' ),
				'id'      => 'sermon_count',
				'default' => get_option( 'posts_per_page' ),
			),
			array(
				'title' => __( 'Links', 'mattytap-sermons' ),
				'type'  => 'separator_title',
			),
			array(
				'title'       => __( 'Archive Page Slug', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'archive_slug',
				// translators: %s: Archive page title, default: "Sermons".
				'placeholder' => wp_sprintf( __( 'e.g. %s', 'mattytap-sermons' ), sanitize_title( __( 'Sermons', 'mattytap-sermons' ) ) ),
				// translators: %1$s Default archive path, effectively <code>/sermons</code>.
				// translators: %2$s Example single sermon path, effectively <code>/sermons/god</code>.
				'desc'        => wp_sprintf( __( 'This controls the page where sermons will be located, which includes single sermons. For example, by default, all sermons would be located under %1$s, and a single sermon with slug “god” would be under %2$s. Does not apply if "pretty permalinks" are not turned on.', 'mattytap-sermons' ), '<code>' . __( '/sermons', 'mattytap-sermons' ) . '</code>', '<code>' . __( '/sermons/god', 'mattytap-sermons' ) . '</code>' ),
				'default'     => 'sermons',
			),
			array(
				'title'    => __( 'Common Base Slug', 'mattytap-sermons' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable a common base slug across all taxonomies.', 'mattytap-sermons' ),
				// translators: %1$s Example series path, effectively <code>/sermons/series/jesus</code>.
				// translators: %2$s Example preacher path, effectively <code>/sermons/preacher/mark</code>.
				'desc_tip' => wp_sprintf( __( 'If this option is checked, the taxonomies would also be under the slug set above, for example, by default, series named “Jesus” would be under %1$s, preacher “Mark” would be under %2$s, and so on.', 'mattytap-sermons' ), '<code>' . __( '/sermons/series/jesus', 'mattytap-sermons' ) . '</code>', '<code>' . __( '/sermons/preacher/mark', 'mattytap-sermons' ) . '</code>' ),
				'id'       => 'common_base_slug',
				'default'  => 'no',
			),
			array(
				'title'       => __( '&ldquo;Preacher&rdquo; Label', 'mattytap-sermons' ),
				'type'        => 'text',
				'placeholder' => 'Preacher', // Do not use translation here.
				// translators: %1$s Default preacher slug/path. Effectively <code>/preacher/mark</code>.
				// translators: %2$s Example changed slug/path. Effectively <code>/speaker/mark</code>.
				'desc'        => wp_sprintf( __( 'Put the label in singular form. It will change the default Preacher to anything you wish. ("Speaker", for example). Note: it will also change the slugs. For example, %1$s would become %2$s.', 'mattytap-sermons' ), '<code>' . __( '/preacher/mark', 'mattytap-sermons' ) . '</code>', '<code>' . __( '/speaker/mark', 'mattytap-sermons' ) . '</code>' ),
				'id'          => 'preacher_label',
				'default'     => '',
			),
			array(
				'title'       => __( '&ldquo;Service Type&rdquo; Label', 'mattytap-sermons' ),
				'type'        => 'text',
				'placeholder' => 'Service Type', // Do not use translation here.
				// translators: %1$s Default slug/path. Effectively <code>/service-type/mark</code>.
				// translators: %2$s Example changed slug/path. Effectively <code>/service-type/mark</code>.
				'desc'        => wp_sprintf( __( 'Put the label in singular form. It will change the default Service Type label to anything you wish. ("Congregation", for example). Note: it will also change the slugs. For example, %1$s would become %2$s.', 'mattytap-sermons' ), '<code>' . __( '/service-type/mark', 'mattytap-sermons' ) . '</code>', '<code>' . __( '/congregation/mark', 'mattytap-sermons' ) . '</code>' ),
				'id'          => 'service_type_label',
				'default'     => '',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_General();
