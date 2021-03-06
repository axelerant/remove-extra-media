<?php
/**
 * Plugin Name: Remove Extra Media
 * Plugin URI: http://wordpress.org/extend/plugins/remove-extra-media/
 * Description: Use Remove Extra Media to remove extra media attachments from your selected post types.
 * Version: 1.1.1
 * Author: Axelerant
 * Author URI: https://axelerant.com/
 * License: GPLv2 or later
 * Text Domain: remove-extra-media
 * Domain Path: /languages
 */


/**
 * Copyright 2015 Axelerant
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
class Remove_Extra_Media {
	const ID          = 'remove-extra-media';
	const PLUGIN_FILE = 'remove-extra-media/remove-extra-media.php';
	const VERSION     = '1.1.1';

	private static $base;
	private static $post_types;

	public static $donate_button;
	public static $menu_id;
	public static $post_id;
	public static $settings_link;


	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'init', array( __CLASS__, 'init' ) );
		self::$base = plugin_basename( __FILE__ );
	}


	public static function admin_init() {
		self::update();
		add_filter( 'plugin_action_links', array( __CLASS__, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		self::$donate_button = <<<EOD
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="WM4F995W9LHXE">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
EOD;

		self::$settings_link = '<a href="' . get_admin_url() . 'options-general.php?page=' . Remove_Extra_Media_Settings::ID . '">' . __( 'Settings', 'remove-extra-media' ) . '</a>';
	}


	public static function admin_menu() {
		self::$menu_id = add_management_page( esc_html__( 'Remove Extra Media Processer', 'remove-extra-media' ), esc_html__( 'Remove Extra Media Processer', 'remove-extra-media' ), 'manage_options', self::ID, array( __CLASS__, 'user_interface' ) );

		add_action( 'admin_print_scripts-' . self::$menu_id, array( __CLASS__, 'scripts' ) );
		add_action( 'admin_print_styles-' . self::$menu_id, array( __CLASS__, 'styles' ) );

		add_screen_meta_link(
			'rmem_settings_link',
			esc_html__( 'Remove Extra Media Settings', 'remove-extra-media' ),
			admin_url( 'options-general.php?page=' . Remove_Extra_Media_Settings::ID ),
			self::$menu_id,
			array( 'style' => 'font-weight: bold;' )
		);
	}


	public static function init() {
		add_action( 'wp_ajax_ajax_process_post', array( __CLASS__, 'ajax_process_post' ) );
		load_plugin_textdomain( self::ID, false, 'remove-extra-media/languages' );
		self::set_post_types();
	}


	public static function plugin_action_links( $links, $file ) {
		if ( $file == self::$base ) {
			array_unshift( $links, self::$settings_link );

			$link = '<a href="' . get_admin_url() . 'tools.php?page=' . self::ID . '">' . esc_html__( 'Process', 'remove-extra-media' ) . '</a>';
			array_unshift( $links, $link );
		}

		return $links;
	}


	public static function activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}


	public static function deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}


	public static function uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		global $wpdb;

		require_once 'lib/class-remove-extra-media-settings.php';

		$delete_data = rmem_get_option( 'delete_data', false );
		if ( $delete_data ) {
			delete_option( Remove_Extra_Media_Settings::ID );
			$wpdb->query( 'OPTIMIZE TABLE `' . $wpdb->options . '`' );
		}
	}


	public static function plugin_row_meta( $input, $file ) {
		if ( $file != self::$base ) {
			return $input;
		}

		$disable_donate = rmem_get_option( 'disable_donate' );
		if ( $disable_donate ) {
			return $input;
		}

		$links = array(
			'<a href="https://axelerant.com/about-axelerant/donate/"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" alt="PayPal - The safer, easier way to pay online!" /></a>',
		);

		$input = array_merge( $input, $links );

		return $input;
	}


	public static function set_post_types() {
		$post_type        = rmem_get_option( 'post_type' );
		self::$post_types = array( $post_type );
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function user_interface() {
		// Capability check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( self::$post_id, esc_html__( "Your user account doesn't have permission to access this.", 'remove-extra-media' ) );
		}

?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap wpsposts">
	<div class="icon32" id="icon-tools"></div>
	<h2><?php _e( 'Remove Extra Media Processer', 'remove-extra-media' ); ?></h2>

<?php
if ( rmem_get_option( 'debug_mode' ) ) {
	$posts_to_import = rmem_get_option( 'posts_to_import' );
	$posts_to_import = explode( ',', $posts_to_import );
	foreach ( $posts_to_import as $post_id ) {
		self::$post_id = $post_id;
		self::ajax_process_post();
	}

	exit( __LINE__ . ':' . basename( __FILE__ ) . " DONE<br />\n" );
}

		// If the button was clicked
if ( ! empty( $_POST[ self::ID ] ) || ! empty( $_REQUEST['posts'] ) ) {
	// Form nonce check
	check_admin_referer( self::ID );

	// Create the list of image IDs
	if ( ! empty( $_REQUEST['posts'] ) ) {
		$posts = explode( ',', trim( $_REQUEST['posts'], ',' ) );
		$posts = array_map( 'intval', $posts );
	} else {
		$posts = self::get_posts_to_process();
	}

	$count = count( $posts );
	if ( ! $count ) {
		echo '	<p>' . _e( 'All done. No posts needing processing found.', 'remove-extra-media' ) . '</p></div>';
		return;
	}

	$posts = implode( ',', $posts );
	self::show_status( $count, $posts );
} else {
	// No button click? Display the form.
	self::show_greeting();
}
?>
	</div>
<?php
	}


	public static function get_posts_to_process() {
		global $wpdb;

		$query = array(
			'post_status' => array( 'publish', 'private' ),
			'post_type' => self::$post_types,
			'orderby' => 'post_modified',
			'order' => 'DESC',
		);

		$include_ids = rmem_get_option( 'posts_to_import' );
		if ( $include_ids ) {
			$query['post__in'] = str_getcsv( $include_ids );
		}

		$skip_ids = rmem_get_option( 'skip_importing_post_ids' );
		if ( $skip_ids ) {
			$query['post__not_in'] = str_getcsv( $skip_ids );
		}

		$results  = new WP_Query( $query );
		$query_wp = $results->request;

		$limit = rmem_get_option( 'limit' );
		if ( $limit ) {
			$query_wp = preg_replace( '#\bLIMIT 0,.*#', 'LIMIT 0,' . $limit, $query_wp );
		}
		else {
			$query_wp = preg_replace( '#\bLIMIT 0,.*#', '', $query_wp );
		}

		$posts = $wpdb->get_col( $query_wp );

		return $posts;
	}


	public static function show_greeting() {
?>
	<form method="post" action="">
<?php wp_nonce_field( self::ID ); ?>

	<p><?php _e( 'Use this tool to remove extra media attachments from your selected post types.', 'remove-extra-media' ); ?></p>

	<p><?php _e( 'This processing is not reversible. Backup your database and files beforehand or be prepared to revert each transformed post manually.', 'remove-extra-media' ); ?></p>

	<p><?php printf( esc_html__( 'Please review your %s before proceeding.', 'remove-extra-media' ), self::$settings_link ); ?></p>

	<p><?php _e( 'To begin, just press the button below.', 'remove-extra-media' ); ?></p>

	<p><input type="submit" class="button hide-if-no-js" name="<?php echo self::ID; ?>" id="<?php echo self::ID; ?>" value="<?php _e( 'Process Remove Extra Media', 'remove-extra-media' ) ?>" /></p>

	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'remove-extra-media' ) ?></em></p></noscript>

	</form>
<?php
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function show_status( $count, $posts ) {
		echo '<p>' . esc_html__( 'Please be patient while this script run. This can take a while, up to a minute per post. Do not navigate away from this page until this script is done or the import will not be completed. You will be notified via this page when the import is completed.', 'remove-extra-media' ) . '</p>';

		echo '<p>' . sprintf( esc_html__( 'Estimated time required to import is %1$s minutes.', 'remove-extra-media' ), ( $count / 12 ) ) . '</p>';

		$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', 'remove-extra-media' ), 'javascript:history.go(-1)' ) : '';

		$text_failures = sprintf( __( 'All done! %1$s posts were successfully processed in %2$s seconds and there were %3$s failures. To try importing the failed posts again, <a href="%4$s">click here</a>. %5$s', 'remove-extra-media' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=' . self::ID . '&goback=1' ) ) . '&posts=' ) . "' + rt_failedlist + '", $text_goback );

		$text_nofailures = sprintf( esc_html__( 'All done! %1$s posts were successfully processed in %2$s seconds and there were no failures. %3$s', 'remove-extra-media' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>

	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'remove-extra-media' ) ?></em></p></noscript>

	<div id="wpsposts-bar" style="position:relative;height:25px;">
		<div id="wpsposts-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="wpsposts-stop" id="wpsposts-stop" value="<?php _e( 'Abort Processing Posts', 'remove-extra-media' ) ?>" /></p>

	<h3 class="title"><?php _e( 'Debugging Information', 'remove-extra-media' ) ?></h3>

	<p>
		<?php printf( esc_html__( 'Total Postss: %s', 'remove-extra-media' ), $count ); ?><br />
		<?php printf( esc_html__( 'Posts Processed: %s', 'remove-extra-media' ), '<span id="wpsposts-debug-successcount">0</span>' ); ?><br />
		<?php printf( esc_html__( 'Process Failures: %s', 'remove-extra-media' ), '<span id="wpsposts-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="wpsposts-debuglist">
		<li style="display:none"></li>
	</ol>

		<script type="text/javascript">
		// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_posts = [<?php echo esc_attr( $posts ); ?>];
			var rt_total = rt_posts.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$( "#wpsposts-bar" ).progressbar();
			$( "#wpsposts-bar-percent" ).html( "0%" );

			// Stop button
			$( "#wpsposts-stop" ).click(function() {
				rt_continue = false;
				$( '#wpsposts-stop' ).val( "<?php echo esc_html__( 'Stopping, please wait a moment.', 'remove-extra-media' ); ?>" );
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$( "#wpsposts-debuglist li" ).remove();

			// Called after each import. Updates debug information and the progress bar.
			function WPSPostsUpdateStatus( id, success, response ) {
				$( "#wpsposts-bar" ).progressbar( "value", ( rt_count / rt_total ) * 100 );
				$( "#wpsposts-bar-percent" ).html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$( "#wpsposts-debug-successcount" ).html(rt_successes);
					$( "#wpsposts-debuglist" ).append( "<li>" + response.success + "</li>" );
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$( "#wpsposts-debug-failurecount" ).html(rt_errors);
					$( "#wpsposts-debuglist" ).append( "<li>" + response.error + "</li>" );
				}
			}

			// Called when all posts have been processed. Shows the results and cleans up.
			function WPSPostsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$( '#wpsposts-stop' ).hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo $text_failures; ?>';
				} else {
					rt_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$( "#message" ).html( "<p><strong>" + rt_resulttext + "</strong></p>" );
				$( "#message" ).show();
			}

			// Regenerate a specified image via AJAX
			function WPSPosts( id ) {
				$.ajax({
					type: 'POST',
						url: ajaxurl,
						data: {
							action: "ajax_process_post",
								id: id
						},
						success: function( response ) {
							if ( response.success ) {
								WPSPostsUpdateStatus( id, true, response );
							}
							else {
								WPSPostsUpdateStatus( id, false, response );
							}

							if ( rt_posts.length && rt_continue ) {
								WPSPosts( rt_posts.shift() );
							}
							else {
								WPSPostsFinishUp();
							}
						},
							error: function( response ) {
								WPSPostsUpdateStatus( id, false, response );

								if ( rt_posts.length && rt_continue ) {
									WPSPosts( rt_posts.shift() );
								}
								else {
									WPSPostsFinishUp();
								}
							}
				});
			}

			WPSPosts( rt_posts.shift() );
		});
		// ]]>
		</script>
<?php
	}


	/**
	 * Process a single post ID (this is an AJAX handler)
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function ajax_process_post() {
		if ( ! rmem_get_option( 'debug_mode' ) ) {
			error_reporting( 0 ); // Don't break the JSON result
			header( 'Content-type: application/json' );
			self::$post_id = intval( $_REQUEST['id'] );
		}

		$post = get_post( self::$post_id );
		if ( ! $post || ! in_array( $post->post_type, self::$post_types )  ) {
			die( json_encode( array( 'error' => sprintf( esc_html__( 'Failed Processing: %s is incorrect post type.', 'remove-extra-media' ), esc_html( self::$post_id ) ) ) ) );
		}

		$count_removed = self::do_remove_extra_media( self::$post_id, $post );
		if ( empty( $count_removed ) ) {
			$count_removed = __( 'No', 'remove-extra-media' );
		}

		die( json_encode( array( 'success' => sprintf( __( '&quot;<a href="%1$s" target="_blank">%2$s</a>&quot; Post ID %3$s was successfully processed in %4$s seconds. <strong>%5$s</strong> extra media removed.', 'remove-extra-media' ), get_permalink( self::$post_id ), esc_html( get_the_title( self::$post_id ) ), self::$post_id, timer_stop(), $count_removed ) ) ) );
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public static function do_remove_extra_media( $post_id, $post ) {
		global $wpdb;

		$featured_id = get_post_thumbnail_id( $post_id );
		$media_count = 1;
		$media_limit = rmem_get_option( 'media_limit' );;
		$query       = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = {$post_id}";

		$medias       = $wpdb->get_col( $query );
		$count_medias = count( $medias );
		if ( $media_limit >= $count_medias ) {
			return;
		}

		$featured_key = array_search( $featured_id, $medias );
		if ( $featured_key ) {
			unset( $medias[ $featured_key ] );
			array_unshift( $medias, $featured_id );
		}

		foreach ( $medias as $media_id ) {
			if ( $media_limit < $media_count ) {
				$args = array(
					'ID' => $media_id,
					'post_parent' => 0,
				);
				wp_update_post( $args );

				if ( $featured_id == $media_id ) {
					delete_post_meta( $post_id, '_thumbnail_id' );
				}
			}

			$media_count++;
		}

		$count_removed = $count_medias - $media_limit;

		return $count_removed;
	}


	public static function admin_notices_0_0_1() {
		$content  = '<div class="updated fade"><p>';
		$content .= sprintf( __( 'If your Remove Extra Media display has gone to funky town, please <a href="%s">read the FAQ</a> about possible CSS fixes.', 'remove-extra-media' ), 'https://nodedesk.zendesk.com/hc/en-us/articles/202244392-Major-Changes-Since-2-10-0' );
		$content .= '</p></div>';

		echo $content;
	}


	public static function admin_notices_donate() {
		$content  = '<div class="updated fade"><p>';
		$content .= sprintf( __( 'Please donate $5 towards development and support of this Remove Extra Media plugin. %s', 'remove-extra-media' ), self::$donate_button );
		$content .= '</p></div>';

		echo $content;
	}


	public static function update() {
		$prior_version = rmem_get_option( 'admin_notices' );
		if ( $prior_version ) {
			if ( $prior_version < '0.0.1' ) {
				add_action( 'admin_notices', array( __CLASS__, 'admin_notices_0_0_1' ) );
			}

			rmem_set_option( 'admin_notices' );
		}

		// display donate on major/minor version release
		$donate_version = rmem_get_option( 'donate_version', false );
		if ( ! $donate_version || ( $donate_version != self::VERSION && preg_match( '#\.0$#', self::VERSION ) ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices_donate' ) );
			rmem_set_option( 'donate_version', self::VERSION );
		}
	}


	public static function scripts() {
		if ( is_admin() ) {
			wp_enqueue_script( 'jquery' );

			wp_register_script( 'jquery-ui-progressbar', plugins_url( 'js/jquery.ui.progressbar.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ), '1.10.3' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
		}
	}


	public static function styles() {
		wp_register_style( 'jquery-ui-progressbar', plugins_url( 'css/redmond/jquery-ui-1.10.3.custom.min.css', __FILE__ ), false, '1.10.3' );
		wp_enqueue_style( 'jquery-ui-progressbar' );
	}


}


register_activation_hook( __FILE__, array( 'Remove_Extra_Media', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'Remove_Extra_Media', 'deactivation' ) );
register_uninstall_hook( __FILE__, array( 'Remove_Extra_Media', 'uninstall' ) );


add_action( 'plugins_loaded', 'rmem__init', 99 );


/**
 *
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
function rmem__init() {
	if ( ! is_admin() ) {
		return;
	}

	if ( ! function_exists( 'add_screen_meta_link' ) ) {
		require_once 'lib/screen-meta-links.php';
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( Remove_Extra_Media::PLUGIN_FILE ) ) {
		require_once 'lib/class-remove-extra-media-settings.php';

		global $Remove_Extra_Media;
		if ( is_null( $Remove_Extra_Media ) ) {
			$Remove_Extra_Media = new Remove_Extra_Media();
		}

		global $Remove_Extra_Media_Settings;
		if ( is_null( $Remove_Extra_Media_Settings ) ) {
			$Remove_Extra_Media_Settings = new Remove_Extra_Media_Settings();
		}
	}
}


?>
