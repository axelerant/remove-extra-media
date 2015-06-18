<?php
/*
	Copyright 2015 Axelerant

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Remove Extra Media settings class
 *
 * Based upon http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/
 */


class Remove_Extra_Media_Settings {
	const ID = 'remove-extra-media-settings';

	public static $admin_page = '';
	public static $default    = array(
		'backwards' => array(
			'version' => '', // below this version number, use std
			'std' => '',
		),
		'choices' => array(), // key => value
		'class' => '',
		'desc' => '',
		'id' => 'default_field',
		'section' => 'general',
		'std' => '', // default key or value
		'title' => '',
		'type' => 'text', // textarea, checkbox, radio, select, hidden, heading, password, expand_begin, expand_end
		'validate' => '', // required, term, slug, slugs, ids, order, single paramater PHP functions
		'widget' => 1, // show in widget options, 0 off
	);

	public static $defaults = array();
	public static $sections = array();
	public static $settings = array();
	public static $version  = null;


	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		// add_action( 'init', array( __CLASS__, 'init' ) );
		load_plugin_textdomain( 'remove-extra-media', false, '/remove-extra-media/languages/' );
	}


	public static function init() {}


		public static function admin_init() {
			$version       = rmem_get_option( 'version' );
			self::$version = Remove_Extra_Media::VERSION;
			self::$version = apply_filters( 'rmem__version', self::$version );

		if ( $version != self::$version ) {
			self::initialize_settings();
		}

		if ( ! self::do_load() ) {
			return;
		}

			self::sections();
			self::settings();

			self::register_settings();
		}


	public static function admin_menu() {
		self::$admin_page = add_options_page( esc_html__( 'Remove Extra Media Settings', 'remove-extra-media' ), esc_html__( 'Remove Extra Media', 'remove-extra-media' ), 'manage_options', self::ID, array( 'Remove_Extra_Media_Settings', 'display_page' ) );

		add_action( 'admin_print_scripts-' . self::$admin_page, array( __CLASS__, 'scripts' ) );
		add_action( 'admin_print_styles-' . self::$admin_page, array( __CLASS__, 'styles' ) );
		add_action( 'load-' . self::$admin_page, array( __CLASS__, 'settings_add_help_tabs' ) );

		add_screen_meta_link(
			'wsp_importer_link',
			esc_html__( 'Remove Extra Media Processer', 'remove-extra-media' ),
			admin_url( 'tools.php?page=' . Remove_Extra_Media::ID ),
			self::$admin_page,
			array( 'style' => 'font-weight: bold;' )
		);
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function do_load() {
		$do_load = false;
		if ( ! empty( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'edit.php', 'options.php', 'plugins.php' ) ) ) {
			$do_load = true;
		} elseif ( ! empty( $_REQUEST['page'] ) && self::ID == $_REQUEST['page'] ) {
			$do_load = true;
		} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$do_load = true;
		}

		return $do_load;
	}


	public static function sections() {
		self::$sections['general'] = esc_html__( 'General', 'remove-extra-media' );
		self::$sections['testing'] = esc_html__( 'Testing', 'remove-extra-media' );
		self::$sections['reset']   = esc_html__( 'Compatibility & Reset', 'remove-extra-media' );
		self::$sections['about']   = esc_html__( 'About Remove Extra Media', 'remove-extra-media' );

		self::$sections = apply_filters( 'rmem__sections', self::$sections );
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function settings() {
		// General
		$choices = self::get_post_types();

		self::$settings['post_type'] = array(
			'title' => esc_html__( 'Post Type', 'remove-extra-media' ),
			'desc' => esc_html__( 'Post type to remove excess media from.', 'remove-extra-media' ),
			'type' => 'select',
			'choices' => $choices,
			'widget' => 0,
		);

		self::$settings['media_limit'] = array(
			'title' => esc_html__( 'Media Limit', 'remove-extra-media' ),
			'desc' => esc_html__( 'Number of media items to limit selected post types to. Count includes featured image.', 'remove-extra-media' ),
			'std' => 1,
			'validate' => 'absint',
		);

		// Testing
		self::$settings['debug_mode'] = array(
			'section' => 'testing',
			'title' => esc_html__( 'Debug Mode?', 'remove-extra-media' ),
			'desc' => esc_html__( 'Bypass Ajax controller to handle posts_to_import directly for testing purposes.', 'remove-extra-media' ),
			'type' => 'checkbox',
			'std' => 0,
		);

		self::$settings['posts_to_import'] = array(
			'title' => esc_html__( 'Posts to Import', 'remove-extra-media' ),
			'desc' => esc_html__( "A CSV list of post ids to import, like '1,2,3'.", 'remove-extra-media' ),
			'std' => '',
			'type' => 'text',
			'section' => 'testing',
			'validate' => 'ids',
		);

		self::$settings['skip_importing_post_ids'] = array(
			'title' => esc_html__( 'Skip Importing Posts', 'remove-extra-media' ),
			'desc' => esc_html__( "A CSV list of post ids to not import, like '1,2,3'.", 'remove-extra-media' ),
			'std' => '',
			'type' => 'text',
			'section' => 'testing',
			'validate' => 'ids',
		);

		self::$settings['limit'] = array(
			'title' => esc_html__( 'Import Limit', 'remove-extra-media' ),
			'desc' => esc_html__( 'Useful for testing import on a limited amount of posts. 0 or blank means unlimited.', 'remove-extra-media' ),
			'std' => '',
			'type' => 'text',
			'section' => 'testing',
			'validate' => 'intval',
		);

		// Reset
		$options = get_option( self::ID );
		if ( ! empty( $options ) ) {
			$serialized_options = serialize( $options );
			$_SESSION['export'] = $serialized_options;

			self::$settings['export'] = array(
				'section' => 'reset',
				'title' => esc_html__( 'Export Settings', 'remove-extra-media' ),
				'type' => 'readonly',
				'desc' => esc_html__( 'These are your current settings in a serialized format. Copy the contents to make a backup of your settings.', 'remove-extra-media' ),
				'std' => $serialized_options,
				'widget' => 0,
			);
		}

		self::$settings['import'] = array(
			'section' => 'reset',
			'title' => esc_html__( 'Import Settings', 'remove-extra-media' ),
			'type' => 'textarea',
			'desc' => esc_html__( 'Paste new serialized settings here to overwrite your current configuration.', 'remove-extra-media' ),
			'widget' => 0,
		);

		self::$settings['delete_data'] = array(
			'section' => 'reset',
			'title' => esc_html__( 'Remove Plugin Data on Deletion?', 'remove-extra-media' ),
			'type' => 'checkbox',
			'class' => 'warning', // Custom class for CSS
			'desc' => esc_html__( 'Delete all Remove Extra Media data and options from database on plugin deletion', 'remove-extra-media' ),
			'widget' => 0,
		);

		self::$settings['reset_defaults'] = array(
			'section' => 'reset',
			'title' => esc_html__( 'Reset to Defaults?', 'remove-extra-media' ),
			'type' => 'checkbox',
			'class' => 'warning', // Custom class for CSS
			'desc' => esc_html__( 'Check this box to reset options to their defaults', 'remove-extra-media' ),
			'widget' => 0,
		);

		self::$settings = apply_filters( 'rmem__settings', self::$settings );

		foreach ( self::$settings as $id => $parts ) {
			self::$settings[ $id ] = wp_parse_args( $parts, self::$default );
		}
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function get_post_types() {
		$result = array( '' );
		$args   = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'objects' );
		foreach ( $post_types as $post_type ) {
			$result[ $post_type->name ] = $post_type->label;
		}

		return $result;
	}


	public static function get_defaults( $mode = null ) {
		if ( empty( self::$defaults ) ) {
			self::settings();
		}

		$do_backwards = false;
		if ( 'backwards' == $mode ) {
			$old_version = rmem_get_option( 'version' );
			if ( ! empty( $old_version ) ) {
				$do_backwards = true;
			}
		}

		foreach ( self::$settings as $id => $parts ) {
			$std = isset( $parts['std'] ) ? $parts['std'] : '';
			if ( $do_backwards ) {
				$version = ! empty( $parts['backwards']['version'] ) ? $parts['backwards']['version'] : false;
				if ( ! empty( $version ) ) {
					if ( $old_version < $version ) {
						$std = $parts['backwards']['std'];
					}
				}
			}

			self::$defaults[ $id ] = $std;
		}

		return self::$defaults;
	}


	public static function get_settings() {
		if ( empty( self::$settings ) ) {
			self::settings();
		}

		return self::$settings;
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public static function create_setting( $args = array() ) {
		extract( $args );

		if ( preg_match( '#(_expand_begin|_expand_end)#', $id ) ) {
			return;
		}

		$field_args = array(
			'type' => $type,
			'id' => $id,
			'desc' => $desc,
			'std' => $std,
			'choices' => $choices,
			'label_for' => $id,
			'class' => $class,
		);

		self::$defaults[$id] = $std;

		add_settings_field( $id, $title, array( __CLASS__, 'display_setting' ), self::ID, $section, $field_args );
	}


	public static function display_page() {
		echo '<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2>' . esc_html__( 'Remove Extra Media Settings', 'remove-extra-media' ) . '</h2>';

		echo '<form action="options.php" method="post">';

		settings_fields( self::ID );

		echo '<div id="' . self::ID . '">
			<ul>';

		foreach ( self::$sections as $section_slug => $section ) {
			echo '<li><a href="#' . $section_slug . '">' . $section . '</a></li>';
		}

		echo '</ul>';

		self::do_settings_sections( self::ID );

		echo '
			<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . esc_html__( 'Save Changes', 'remove-extra-media' ) . '" /></p>
			</form>
			</div>
			';

		$disable_donate = rmem_get_option( 'disable_donate' );
		if ( ! $disable_donate ) {
			echo '<p>' .
				sprintf(
					__( 'If you like this plugin, please <a href="%1$s" title="Donate for Good Karma"><img src="%2$s" border="0" alt="Donate for Good Karma" /></a> or <a href="%3$s" title="purchase Remove Extra Media Premium">purchase Remove Extra Media Premium</a> to help fund further development and <a href="%4$s" title="Support forums">support</a>.', 'remove-extra-media' ),
					esc_url( 'http://axelerant.com/about-axelerant/donate/' ),
					esc_url( 'https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' ),
					esc_url( 'http://axelerant.com/downloads/' ),
					esc_url( 'https://nodedesk.zendesk.com/hc/en-us/sections/200861112-WordPress-FAQs' )
				) .
				'</p>';
		}

		echo '<p class="copyright">' .
			sprintf(
				__( 'Copyright &copy;%1$s <a href="%2$s">Axelerant</a>.', 'remove-extra-media' ),
				date( 'Y' ),
				esc_url( 'http://axelerant.com' )
			) .
			'</p>';

		self::section_scripts();

		echo '</div>';
	}


	public static function section_scripts() {
		echo '
			<script type="text/javascript">
jQuery(document).ready(function($) {
	$( "#' . self::ID . '" ).tabs();
	// This will make the "warning" checkbox class really stand out when checked.
	$(".warning").change(function() {
		if ($(this).is(":checked"))
			$(this).parent().css("background", "#c00").css("color", "#fff").css("fontWeight", "bold");
		else
			$(this).parent().css("background", "inherit").css("color", "inherit").css("fontWeight", "inherit");
	});
	});
</script>
';
	}


	public static function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) ) {
				continue;
			}

			echo '<table id=' . $section['id'] . ' class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}


	public static function display_section() {}


	public function display_about() {
		$text  = __( '<img class="size-medium" src="%5$s" alt="Axelerant 2015 Retreat in Goa" width="640" height="327" /><p>Axelerant is a full-service software development company that focuses on open-source technologies. Top technical talent who are passionate, giving, and communicative demonstrates our backbone. We provide high-end Strategy, Implementation, and Support services for our clients and agencies with whom we partner.</p><p>Our team members span the world, and we follow agile delivery and working processes. Further, we’re actively giving back to many open-source communities and have fostered an innovative, incubator culture to give ideas a chance to succeed.</p><h2>Foundations of Axelerant</h2><ul><li><b>Passion</b> – Our passion is so strong, we’re self­directed to make the difficult easy.</li><li><b>Openness</b> – We’re so honest and painstaking in our discussions that there are no questions left, and standards are created.</li><li><b>Giving</b> – We’re excited to share our results to inspire all to surpass them.</li></ul><h3>Learn More About Axelerant</h3><ul><li><a href="%1$s">Axelerant Team</a></li><li><a href="%2$s">Giving Back</a></li><li><a href="%7$s">Inside Axelerant</a></li><li><a href="%3$s">Our Services</a></li><li><a href="%4$s">Testimonials</a></li><li><a href="%6$s">Careers</a></li></ul>', 'remove-extra-media' );

		echo '<div id="about" style="width: 70%; min-height: 225px;"><p>';
		echo sprintf(
			$text,
			esc_url( 'https://axelerant.com/about-axelerant/' ),
			esc_url( 'https://axelerant.com/drupalgive/' ),
			esc_url( 'https://axelerant.com/services/' ),
			esc_url( 'https://axelerant.com/about-axelerant/testimonials/' ),
			esc_url( 'https://axelerant.com/wp-content/uploads/2015/02/IGP7228-2015-01-22-at-05-18-02.jpg' ),
			esc_url( 'https://axelerant.com/careers/' ),
			esc_url( 'https://axelerant.com/open-policies-open-discussion/' )
		);
		echo '</p></div>';
	}


	public static function display_setting( $args = array(), $do_echo = true, $input = null ) {
		$content = '';

		extract( $args );

		if ( is_null( $input ) ) {
			$options = get_option( self::ID );
		} else {
			$options      = array();
			$options[$id] = $input;
		}

		if ( ! isset( $options[$id] ) && $type != 'checkbox' ) {
			$options[$id] = $std;
		} elseif ( ! isset( $options[$id] ) ) {
			$options[$id] = 0;
		}

		$field_class = '';
		if ( ! empty( $class ) ) {
			$field_class = ' ' . $class;
		}

		// desc isn't escaped because it's might contain allowed html
		$choices      = array_map( 'esc_attr', $choices );
		$field_class  = esc_attr( $field_class );
		$id           = esc_attr( $id );
		$options[$id] = esc_attr( $options[$id] );
		$std          = esc_attr( $std );

		switch ( $type ) {
			case 'checkbox':
				$content .= '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="' . self::ID . '[' . $id . ']" value="1" ' . checked( $options[$id], 1, false ) . ' /> ';

				if ( ! empty( $desc ) ) {
				$content .= '<label for="' . $id . '"><span class="description">' . $desc . '</span></label>';
				}

			break;

			case 'file':
				$content .= '<input class="regular-text' . $field_class . '" type="file" id="' . $id . '" name="' . self::ID . '[' . $id . ']" />';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'heading':
				$content .= '</td></tr><tr valign="top"><td colspan="2"><h4>' . $desc . '</h4>';
			break;

			case 'hidden':
				$content .= '<input type="hidden" id="' . $id . '" name="' . self::ID . '[' . $id . ']" value="' . $options[$id] . '" />';

			break;

			case 'password':
				$content .= '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="' . self::ID . '[' . $id . ']" value="' . $options[$id] . '" />';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'radio':
				$i             = 1;
				$count_choices = count( $choices );
				foreach ( $choices as $value => $label ) {
					$content .= '<input class="radio' . $field_class . '" type="radio" name="' . self::ID . '[' . $id . ']" id="' . $id . $i . '" value="' . $value . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';

					if ( $i < $count_choices ) {
					$content .= '<br />';
					}

					$i++;
				}

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'readonly':
				$content .= '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="' . self::ID . '[' . $id . ']" value="' . $options[$id] . '" readonly="readonly" />';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'select':
				$content .= '<select class="select' . $field_class . '" name="' . self::ID . '[' . $id . ']">';

				foreach ( $choices as $value => $label ) {
				$content .= '<option value="' . $value . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				}

				$content .= '</select>';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'text':
				$content .= '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="' . self::ID . '[' . $id . ']" placeholder="' . $std . '" value="' . $options[$id] . '" />';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			case 'textarea':
				$content .= '<textarea class="' . $field_class . '" id="' . $id . '" name="' . self::ID . '[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';

				if ( ! empty( $desc ) ) {
				$content .= '<br /><span class="description">' . $desc . '</span>';
				}

			break;

			default:
			break;
		}

		if ( ! $do_echo ) {
			return $content;
		}

		echo $content;
	}


	public static function initialize_settings() {
		$defaults                 = self::get_defaults( 'backwards' );
		$current                  = get_option( self::ID );
		$current                  = wp_parse_args( $current, $defaults );
		$current['admin_notices'] = rmem_get_option( 'version', self::$version );
		$current['version']       = self::$version;

		update_option( self::ID, $current );
	}


	public static function register_settings() {
		register_setting( self::ID, self::ID, array( __CLASS__, 'validate_settings' ) );

		foreach ( self::$sections as $slug => $title ) {
			if ( $slug == 'about' ) {
				add_settings_section( $slug, $title, array( __CLASS__, 'display_about' ), self::ID );
			}
			else {
				add_settings_section( $slug, $title, array( __CLASS__, 'display_section' ), self::ID );
			}
		}

		foreach ( self::$settings as $id => $setting ) {
			$setting['id'] = $id;
			self::create_setting( $setting );
		}
	}


	public static function scripts() {
		wp_enqueue_script( 'jquery-ui-tabs' );
	}


	public static function styles() {
		wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}


	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	public static function validate_settings( $input, $options = null, $do_errors = false ) {
		$errors = array();

		if ( is_null( $options ) ) {
			$options  = self::get_settings();
			$defaults = self::get_defaults();

			if ( is_admin() ) {
				if ( ! empty( $input['reset_defaults'] ) ) {
					foreach ( $defaults as $id => $std ) {
						$input[$id] = $std;
					}

					unset( $input['reset_defaults'] );
				}

				if ( ! empty( $input['import'] ) && $_SESSION['export'] != $input['import'] ) {
					$import       = $input['import'];
					$unserialized = unserialize( $import );
					if ( is_array( $unserialized ) ) {
						foreach ( $unserialized as $id => $std ) {
							$input[$id] = $std;
						}
					}
				}
			}
		}

		foreach ( $options as $id => $parts ) {
			$default     = $parts['std'];
			$type        = $parts['type'];
			$validations = ! empty( $parts['validate'] ) ? $parts['validate'] : array();
			if ( ! empty( $validations ) ) {
				$validations = explode( ',', $validations );
			}

			if ( ! isset( $input[ $id ] ) ) {
				if ( 'checkbox' != $type ) {
					$input[ $id ] = $default;
				}
				else {
					$input[ $id ] = 0;
				}
			}

			if ( $default == $input[ $id ] && ! in_array( 'required', $validations ) ) {
				continue;
			}

			if ( 'checkbox' == $type ) {
				if ( self::is_true( $input[ $id ] ) ) {
					$input[ $id ] = 1;
				}
				else {
					$input[ $id ] = 0;
				}
			} elseif ( in_array( $type, array( 'radio', 'select' ) ) ) {
				// single choices only
				$keys = array_keys( $parts['choices'] );

				if ( ! in_array( $input[ $id ], $keys ) ) {
					if ( self::is_true( $input[ $id ] ) ) {
						$input[ $id ] = 1;
					}
					else {
						$input[ $id ] = 0;
					}
				}
			}

			if ( ! empty( $validations ) ) {
				foreach ( $validations as $validate ) {
					self::validators( $validate, $id, $input, $default, $errors );
				}
			}
		}

		$input['version']        = self::$version;
		$input['donate_version'] = Remove_Extra_Media::VERSION;
		$input                   = apply_filters( 'rmem__validate_settings', $input, $errors );

		unset( $input['export'] );
		unset( $input['import'] );

		if ( empty( $do_errors ) ) {
			$validated = $input;
		} else {
			$validated = array(
				'input' => $input,
				'errors' => $errors,
			);
		}

		return $validated;
	}


	public static function validators( $validate, $id, &$input, $default, &$errors ) {
		switch ( $validate ) {
			case 'absint':
			case 'intval':
				if ( '' !== $input[ $id ] ) {
				$input[ $id ] = $validate( $input[ $id ] );
				}
				else {
				$input[ $id ] = $default;
				}
			break;

			case 'ids':
				$input[ $id ] = self::validate_ids( $input[ $id ], $default );
			break;

			case 'min1':
				$input[ $id ] = intval( $input[ $id ] );
				if ( 0 >= $input[ $id ] ) {
				$input[ $id ] = $default;
				}
			break;

			case 'nozero':
				$input[ $id ] = intval( $input[ $id ] );
				if ( 0 === $input[ $id ] ) {
				$input[ $id ] = $default;
				}
			break;

			case 'order':
				$input[ $id ] = self::validate_order( $input[ $id ], $default );
			break;

			case 'required':
				if ( empty( $input[ $id ] ) ) {
				$errors[ $id ] = esc_html__( 'Required', 'remove-extra-media' );
				}
			break;

			case 'slug':
				$input[ $id ] = self::validate_slug( $input[ $id ], $default );
				$input[ $id ] = strtolower( $input[ $id ] );
			break;

			case 'slugs':
				$input[ $id ] = self::validate_slugs( $input[ $id ], $default );
				$input[ $id ] = strtolower( $input[ $id ] );
			break;

			case 'term':
				$input[ $id ] = self::validate_term( $input[ $id ], $default );
				$input[ $id ] = strtolower( $input[ $id ] );
			break;

			default:
				$input[ $id ] = $validate( $input[ $id ] );
			break;
		}
	}


	public static function validate_ids( $input, $default ) {
		if ( preg_match( '#^\d+(,\s?\d+)*$#', $input ) ) {
			return preg_replace( '#\s#', '', $input );
		}

		return $default;
	}


	public static function validate_order( $input, $default ) {
		if ( preg_match( '#^desc|asc$#i', $input ) ) {
			return $input;
		}

		return $default;
	}


	public static function validate_slugs( $input, $default ) {
		if ( preg_match( '#^[\w-]+(,\s?[\w-]+)*$#', $input ) ) {
			return preg_replace( '#\s#', '', $input );
		}

		return $default;
	}


	public static function validate_slug( $input, $default ) {
		if ( preg_match( '#^[\w-]+$#', $input ) ) {
			return $input;
		}

		return $default;
	}


	public static function validate_term( $input, $default ) {
		if ( preg_match( '#^\w+$#', $input ) ) {
			return $input;
		}

		return $default;
	}


	/**
	 * Let values like "true, 'true', 1, and 'yes'" to be true. Else, false
	 */
	public static function is_true( $value = null, $return_boolean = true ) {
		if ( true === $value || 'true' == strtolower( $value ) || 1 == $value || 'yes' == strtolower( $value ) ) {
			if ( $return_boolean ) {
				return true;
			}
			else {
				return 1;
			}
		} else {
			if ( $return_boolean ) {
				return false;
			}
			else {
				return 0;
			}
		}
	}


	public static function settings_add_help_tabs() {
		$screen = get_current_screen();
		if ( self::$admin_page != $screen->id ) {
			return;
		}

		$screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'For more information:', 'remove-extra-media' ) . '</strong></p><p>' .
			esc_html__( 'These Remove Extra Media Settings establish the default option values for shortcodes, theme functions, and widget instances.', 'remove-extra-media' ) .
			'</p><p>' .
			sprintf(
				__( 'View the <a href="%s">Remove Extra Media documentation</a>.', 'remove-extra-media' ),
				esc_url( 'http://wordpress.org/extend/plugins/remove-extra-media/' )
			) .
			'</p>'
		);

		$screen->add_help_tab(
			array(
				'id'     => 'tw-general',
				'title'     => esc_html__( 'General', 'remove-extra-media' ),
				'content' => '<p>' . esc_html__( 'Show or hide optional fields.', 'remove-extra-media' ) . '</p>'
			)
		);

		do_action( 'rmem_settings_add_help_tabs', $screen );
	}


}


function rmem_get_options() {
	$options = get_option( Remove_Extra_Media_Settings::ID );

	if ( false === $options ) {
		$options = Remove_Extra_Media_Settings::get_defaults();
		update_option( Remove_Extra_Media_Settings::ID, $options );
	}

	return $options;
}


function rmem_get_option( $option, $default = null ) {
	$options = get_option( Remove_Extra_Media_Settings::ID, null );

	if ( isset( $options[$option] ) ) {
		return $options[$option];
	}
	else {
		return $default;
	}
}


function rmem_set_option( $option, $value = null ) {
	$options = get_option( Remove_Extra_Media_Settings::ID );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$options[$option] = $value;
	update_option( Remove_Extra_Media_Settings::ID, $options );
}


?>
