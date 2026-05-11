<?php
/**
 * Podcast settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_Podcast extends SM_Settings_Page {
	/**
	 * SM_Settings_Podcast constructor.
	 */
	public function __construct() {
		$this->id    = 'podcast';
		$this->label = __( 'Podcast', 'mattytap-sermons' );
		add_action( 'sm_settings_podcast_settings_after', array( $this, 'after' ) );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_podcast_settings', array(
			array(
				'title' => __( 'Podcast Settings', 'mattytap-sermons' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'podcast_settings',
			),
			array(
				'title'       => __( 'Title', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'title',
				'placeholder' => get_bloginfo( 'name' ),
			),
			array(
				'title'       => __( 'Description', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'description',
				'placeholder' => get_bloginfo( 'description' ),
			),
			array(
				'title'       => __( 'Website Link', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'website_link',
				'placeholder' => home_url(),
			),
			array(
				'title'       => __( 'Language', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'language',
				'placeholder' => get_bloginfo( 'language' ),
			),
			array(
				'title'       => __( 'Copyright', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'copyright',
				// translators: %s: blog name.
				'placeholder' => wp_sprintf( __( 'Copyright &copy; %s', 'mattytap-sermons' ), get_bloginfo( 'name' ) ),
				// translators: %s: copyright symbol HTML entitiy (&copy;).
				'desc'        => wp_sprintf( esc_html__( 'Tip: Use %s to generate a copyright symbol.', 'mattytap-sermons' ), '<code>' . htmlspecialchars( '&copy;' ) . '</code>' ),
			),
			array(
				'title'       => __( 'Webmaster Name', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'webmaster_name',
				'placeholder' => __( 'e.g. Your Name', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Webmaster Email', 'mattytap-sermons' ),
				'type'        => 'email',
				'id'          => 'webmaster_email',
				'placeholder' => __( 'e.g. Your Email', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Author', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'itunes_author',
				'placeholder' => __( 'e.g. Primary Speaker or Church Name', 'mattytap-sermons' ),
				'desc'        => __( 'This will display at the &ldquo;Artist&rdquo; in the iTunes Store.', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Subtitle', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'itunes_subtitle',
				// translators: %s: blog name.
				'placeholder' => wp_sprintf( __( 'e.g. Preaching and teaching audio from %s', 'mattytap-sermons' ), get_bloginfo( 'name' ) ),
				'desc'        => __( 'Your subtitle should briefly tell the listener what they can expect to hear.', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Summary', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'itunes_summary',
				// translators: %s: blog name.
				'placeholder' => wp_sprintf( __( 'e.g. Weekly teaching audio brought to you by %s in City, State.', 'mattytap-sermons' ), get_bloginfo( 'name' ) ),
				'desc'        => __( 'Keep your Podcast Summary short, sweet and informative. Be sure to include a brief statement about your mission and in what region your audio content originates.', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Owner Name', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'itunes_owner_name',
				'placeholder' => get_bloginfo( 'name' ),
				'desc'        => __( 'This should typically be the name of your Church.', 'mattytap-sermons' ),
			),
			array(
				'title'       => __( 'Owner Email', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'itunes_owner_email',
				'placeholder' => __( 'e.g. Your Email', 'mattytap-sermons' ),
				'desc'        => __( 'Use an email address that you don&rsquo;t mind being made public. If someone wants to contact you regarding your Podcast this is the address they will use.', 'mattytap-sermons' ),
			),
			array(
				'title' => __( 'Cover Image', 'mattytap-sermons' ),
				'type'  => 'image',
				'id'    => 'itunes_cover_image',
				'desc'  => __( 'This JPG will serve as the Podcast artwork in the iTunes Store. The image must be between 1,400px by 1,400px and 3,000px by 3,000px or else iTunes will not accept your feed.', 'mattytap-sermons' ),
			),
			array(
				'title'   => __( 'Sub Category', 'mattytap-sermons' ),
				'type'    => 'select',
				'id'      => 'itunes_sub_category',
				'options' => array(
					'0' => __( 'Sub Category', 'mattytap-sermons' ),
					'1' => __( 'Buddhism', 'mattytap-sermons' ),
					'2' => __( 'Christianity', 'mattytap-sermons' ),
					'3' => __( 'Hinduism', 'mattytap-sermons' ),
					'4' => __( 'Islam', 'mattytap-sermons' ),
					'5' => __( 'Judaism', 'mattytap-sermons' ),
					'6' => __( 'Other', 'mattytap-sermons' ),
					'7' => __( 'Spirituality', 'mattytap-sermons' ),
				),
			),
			array(
				'title'    => __( 'PodTrac Tracking', 'mattytap-sermons' ),
				'type'     => 'checkbox',
				'id'       => 'podtrac',
				'desc'     => __( 'Enables PodTrac tracking.', 'mattytap-sermons' ),
				// translators: %s <a href="http://podtrac.com">podtrac.com</a>.
				'desc_tip' => wp_sprintf( __( 'For more info on PodTrac or to sign up for an account, visit %s', 'mattytap-sermons' ), '<a href="http://podtrac.com">podtrac.com</a>' ),
				'default'  => 'no',
			),
			array(
				'title'    => __( 'HTML in description', 'mattytap-sermons' ),
				'type'     => 'checkbox',
				'id'       => 'enable_podcast_html_description',
				'desc'     => __( 'Enables showing of HTML in iTunes description field. Uncheck if description looks messy.', 'mattytap-sermons' ),
				'desc_tip' => __( 'It is recommended to leave it unchecked. Uncheck if the feed does not validate.', 'mattytap-sermons' ),
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Redirect', 'mattytap-sermons' ),
				'type'     => 'checkbox',
				'id'       => 'enable_podcast_redirection',
				'desc'     => __( 'Enables redirection of podcast from old to new URL.', 'mattytap-sermons' ),
				'desc_tip' => __( 'You can use relative or absolute URLs.', 'mattytap-sermons' ),
			),
			array(
				'title' => __( 'Old URL', 'mattytap-sermons' ),
				'type'  => 'text',
				'id'    => 'podcast_redirection_old_url',
			),
			array(
				'title' => __( 'New URL', 'mattytap-sermons' ),
				'type'  => 'text',
				'id'    => 'podcast_redirection_new_url',
			),
			array(
				'title'       => __( 'Number of podcasts to show', 'mattytap-sermons' ),
				'type'        => 'number',
				'id'          => 'podcasts_per_page',
				'placeholder' => get_option( 'posts_per_rss' ),
			),
			array(
				'title'       => __( 'iTunes Podcast URL', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'podcast_url_itunes',
				'placeholder' => 'pcast://itunes.apple.com/us/podcast/…/id…',
				'desc'        => 'URL to use for the iTunes link in the <code>[list_podcasts]</code> shortcode. Change “https” to “pcast” to make the link open directly into the Apple Podcasts app. Shortcode key to include/exclude: <code>itunes</code>.',
				'desc_tip'    => 'Leave empty to disable.',
			),
			array(
				'title'       => __( 'Android Podcast URL', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'podcast_url_android',
				'placeholder' => 'https://subscribeonandroid.com/' . str_replace( 'https://', '', get_site_url( null, '?feed=rss2&post_type=wpfc_sermon', 'https' ) ),
				'desc'        => 'URL to use for the Android link in the <code>[list_podcasts]</code> shortcode. Shortcode key to include/exclude: <code>android</code>.',
				'desc_tip'    => 'Leave empty to disable.',
			),
			array(
				'title'       => __( 'Overcast Podcast URL', 'mattytap-sermons' ),
				'type'        => 'text',
				'id'          => 'podcast_url_overcast',
				'placeholder' => 'https://overcast.fm/…',
				'desc'        => 'URL to use for the Overcast link in the <code>[list_podcasts]</code> shortcode.  Shortcode key to include/exclude: <code>overcast</code>.',
				'desc_tip'    => 'Leave empty to disable.',
			),
			array(
				'title'    => __( 'Sermon Image', 'mattytap-sermons' ),
				'type'     => 'checkbox',
				'id'       => 'podcast_sermon_image_series',
				'desc'     => __( 'Fallback to series image if sermon does not have its own image.', 'mattytap-sermons' ),
				'desc_tip' => __( 'Default disabled.', 'mattytap-sermons' ),
				'default'  => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'podcast_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}

	/**
	 * Additional HTML after the settings form.
	 */
	public function after() {
		?>
		<div>
			<p>
				<label for="feed_url"><?php echo esc_html__( 'Feed URL to Submit to iTunes', 'mattytap-sermons' ); ?></label>
				<input type="text" disabled="disabled"
						value="<?php echo esc_url( site_url( '/' ) . '?feed=rss2&post_type=wpfc_sermon' ); ?>" id="feed_url">
			</p>
			<p>
				<?php
				// translators: %s Feed Validator link, see msgid "Feed Validator".
				echo wp_kses_post( wp_sprintf( esc_html__( 'Use the %s to diagnose and fix any problems before submitting your Podcast to iTunes.', 'mattytap-sermons' ), '<a href="http://www.feedvalidator.org/check.cgi?url=' . site_url( '/' ) . SermonManager::getOption( 'archive_slug', 'sermons' ) . '/feed/" target="_blank">' . esc_html__( 'Feed Validator', 'mattytap-sermons' ) . '</a>' ) );
				?>
			</p>
			<p>
				<?php
				// translators: %s see msgid "Submit Your Podcast".
				echo wp_sprintf( esc_html__( 'Once your Podcast Settings are complete and your Sermons are ready, it&rsquo;s time to %s to the iTunes Store!', 'mattytap-sermons' ), '<a href="https://www.apple.com/itunes/podcasts/specs.html#submitting" target="_blank">' . esc_html__( 'Submit Your Podcast', 'mattytap-sermons' ) . '</a>' );
				?>
			</p>
			<p>
				<?php
				// translators: %s see msgid "FeedBurner".
				echo wp_sprintf( esc_html__( 'Alternatively, if you want to track your Podcast subscribers, simply pass the Podcast Feed URL above through %s. FeedBurner will then give you a new URL to submit to iTunes instead.', 'mattytap-sermons' ), '<a href="http://feedburner.google.com/" target="_blank">' . esc_html__( 'FeedBurner', 'mattytap-sermons' ) . '</a>' );
				?>
			</p>
			<p>
				<?php
				// translators: %s see msgid "iTunes FAQ for Podcast Makers".
				echo wp_sprintf( esc_html__( 'Please read the %s for more information.', 'mattytap-sermons' ), '<a href="https://www.apple.com/itunes/podcasts/creatorfaq.html" target="_blank">' . esc_html__( 'iTunes FAQ for Podcast Makers', 'mattytap-sermons' ) . '</a>' );
				?>
			</p>
		</div>
		<?php
	}
}

return new SM_Settings_Podcast();
