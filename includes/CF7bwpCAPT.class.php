<?php

/* A plugin to use BWP reCAPTCHA in Contact Form 7.
 * This class needs BWP reCAPTCHA and Contact Form 7 plugin installed and activated.
 * http://wordpress.org/extend/plugins/bwp-recaptcha/
 * http://wordpress.org/extend/plugins/contact-form-7/
 */
define('BWP_CAPT_OPTION_CF7', 'bwp_capt_cf7');

if ( ! class_exists( 'CF7bwpCAPT' ) ) {

	class CF7bwpCAPT {

		// member variables
		private $textdomain;
		private $options_name;
		private $options;

		// php4 Constructor
		function CF7bwpCAPT( $options, $textdomain ) {
			$args = func_get_args();
			call_user_func_array( array( &$this, "__construct" ), $args );
		}

		// php5 Constructor
		function __construct( $options, $textdomain ) {

			// load localization strings
			load_plugin_textdomain(
				$textdomain,
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);

			$this->options_name = $options;
			$this->options = get_option( $options );

			// register settings page
			add_action( 'admin_init', array( &$this, 'register_settings_group' ) );
			add_action( 'admin_menu', array( &$this, 'register_settings_page' ) );

			// check requirements
			global $wp_version;
			add_action( 'admin_notices', array( &$this, 'admin_notice' ) );

			// register tag generator
			if ( $this->meets_requirements() ) {
				add_action( 'admin_init', array( &$this, 'tag_generator_recaptcha' ), 46 );
				add_action( 'admin_init', array( &$this, 'cf7_bwp_capt_register_styles' ) );
			}

			// register validation filter
			if ( $this->meets_requirements() ) {
				add_filter( 'wpcf7_validate_recaptcha', array( &$this, 'recaptcha_validation_filter' ), 10, 2 );
				add_filter( 'wpcf7_ajax_json_echo', array( &$this, 'ajax_json_echo_filter' ) );
			}

			// register CF7 Shortcode
			add_action( 'plugins_loaded', array( &$this, 'register_cf7_shortcode' ) );

		}

		/**
		 * Register CF7 recaptcha shortcode
		 */
		function register_cf7_shortcode() {
			if ( function_exists( 'wpcf7_add_shortcode' ) && $this->meets_requirements() ) {
				wpcf7_add_shortcode( 'recaptcha', array( &$this, 'shortcode_handler' ), true );	
			}
		}

		function getClassFile() {
			return __FILE__;
		}

		/*
		 * SETTINGS PAGE GENERATION AND VALIDATION
		 */

		/**
		 * Registering the settings
		 */
		function register_settings_group() {
			register_setting( $this->options_name . "_group", $this->options_name, array( &$this, 'validate_options' ) );
			$this->add_settings();
		}

		function register_settings_page() {
			$page = add_submenu_page( BWP_CAPT_OPTION_GENERAL, __( 'CF7 BWP reCAPTCHA Extension Options', $this->textdomain ), __( 'CF7 Options', $this->textdomain ), BWP_CAPT_CAPABILITY, BWP_CAPT_OPTION_CF7, array( &$this, 'show_settings_page' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'cf7_bwp_capt_admin_styles' ) );
		}

		function cf7_bwp_capt_register_styles() {
			wp_register_style( 'cf7_bwp_capt_donate', plugins_url( 'includes/css/donate.css' , dirname(__FILE__) ) );
		}

		function cf7_bwp_capt_admin_styles() {
			wp_enqueue_style( 'cf7_bwp_capt_donate' );
		}

		function show_settings_page() {
			include( 'settings.php' );
		}

		/**
		 * Generate settings page
		 */
		function add_settings() {
			// Theme Options
			add_settings_section(
				'cf7_bwp_capt_theme',
				__( 'Theme Options', $this->textdomain ),
				array( &$this, 'echo_theme_info' ),
				$this->options_name . '_page'
			);
			add_settings_field(
				'cf7_bwp_capt_theme_preselection',
				__( 'Theme Preselection', $this->textdomain ),
				array( &$this, 'echo_theme_radio' ),
				$this->options_name . '_page',
				'cf7_bwp_capt_theme'
			);
			add_settings_field(
				'cf7_bwp_capt_own_theme',
				__( 'Own Theme (<i>if selected</i>)', $this->textdomain ),
				array( &$this, 'echo_theme_dropdown' ),
				$this->options_name . '_page', 'cf7_bwp_capt_theme'
			);

			// Language Options
			add_settings_section(
				'cf7_bwp_capt_lang',
				__( 'Language Options', $this->textdomain ),
				array( &$this, 'echo_language_info' ),
				$this->options_name . '_page'
			);
			add_settings_field(
				'cf7_bwp_capt_language_preselection',
				__( 'Language Preselection', $this->textdomain ),
				array( &$this, 'echo_language_radio' ),
				$this->options_name . '_page',
				'cf7_bwp_capt_lang'
			);
			add_settings_field(
				'cf7_bwp_capt_own_language',
				__( 'Own Language (<i>if selected</i>)', $this->textdomain ),
				array( &$this, 'echo_language_dropdown' ),
				$this->options_name . '_page',
				'cf7_bwp_capt_lang'
			);
		}

		/**
		 * Theme Options Output
		 */
		function echo_theme_info() {
			echo '<p>' . __( 'Here you can set which options to use for the themes option of the BWP reCAPTCHA forms in the Contact Form 7 forms.', $this->textdomain ) . "</p>\n";
		}

		function echo_theme_radio() {

			// Get Better Wordpress Recaptcha options to obtain current theme
			$bwp_capt_options = get_option( 'bwp_capt_theme' );

			$available_themes = array (
				'red'        => __( 'Red',         $this->textdomain ),
				'white'      => __( 'White',       $this->textdomain ),
				'blackglass' => __( 'Black Glass', $this->textdomain ),
				'clean'      => __( 'Clean',       $this->textdomain ),
				'custom'     => __( 'Custom',      $this->textdomain )
			);

			$bwp_capt_theme = ( is_array( $bwp_capt_options ) && isset( $bwp_capt_options[ 'select_theme' ] ) ) ? ' (' . __( 'currently', $this->textdomain ) . ': <i>' . $available_themes[ $bwp_capt_options[ 'select_theme' ] ] . '</i>)' : '';


			// Generate radio buttons
			$theme_options = array (
				__( 'BWP reCAPTCHA Theme', $this->textdomain ) . $bwp_capt_theme => 'bwp_capt', 		
				__( 'Own Theme' , $this->textdomain ) . ' (<i>' . __( 'select below', $this->textdomain ) . '</i>)' => 'cf7'
			);

			foreach( $theme_options as $label => $item ) {
				$checked = ( $this->options['select_theme'] == $item ) ? ' checked="checked" ' : '';
				echo "<label><input " . $checked . " value='$item' name='". $this->options_name ."[select_theme]' type='radio' /> $label</label><br />";
			}

		}

		function echo_theme_dropdown() {
			$available_themes = array (
				__( 'Red',         $this->textdomain ) => 'red',
				__( 'White',       $this->textdomain ) => 'white',
				__( 'Black Glass', $this->textdomain ) => 'blackglass',
				__( 'Clean',       $this->textdomain ) => 'clean',
				__( 'Custom',      $this->textdomain ) => 'custom'
			);

			echo '<label for="' . $this->options_name . '[cf7_theme]">' . __( 'Theme', $this->textdomain ) . ":</label>\n";     

			echo "<select id='cf7_theme' name='" . $this->options_name . "[cf7_theme]'>";
			foreach($available_themes as $label => $item) {
				$selected = ( $this->options['cf7_theme'] == $item ) ? 'selected="selected"' : '';
				echo "<option value='$item' $selected>$label</option>";
			}
			echo "</select>";

		}

		/**
		 * General Options Output
		 */
		function echo_language_info() {
			echo '<p>' . __( 'Here you can set which options to use for the language option of the BWP reCAPTCHA forms in the Contact Form 7 forms.', $this->textdomain ) . "</p>\n";
		}

		function echo_language_radio() {

			// Get Better Wordpress Recaptcha options to obtain current language
			$bwp_capt_options = get_option( 'bwp_capt_theme' );

			$available_languages = array (
				'en' => __( 'English',    $this->textdomain ),
				'nl' => __( 'Dutch',      $this->textdomain ),
				'fr' => __( 'French',     $this->textdomain ),
				'de' => __( 'German',     $this->textdomain ),
				'pt' => __( 'Portuguese', $this->textdomain ),
				'ru' => __( 'Russian',    $this->textdomain ),
				'es' => __( 'Spanish',    $this->textdomain ),
				'tr' => __( 'Turkish',    $this->textdomain )
			);

			$bwp_capt_lang = ( is_array( $bwp_capt_options ) && isset( $bwp_capt_options[ 'select_lang' ] ) ) ? ' (' . __( 'currently', $this->textdomain ) . ': <i>' . $available_languages[ $bwp_capt_options[ 'select_lang' ] ] . '</i>)' : '';
    
			// Generate radio buttons
			$language_options = array (
				__( 'BWP reCAPTCHA Language', $this->textdomain ) . $bwp_capt_lang => 'bwp_capt',
				__( 'Own Language', $this->textdomain ) . ' (<i>' . __( 'select below', $this->textdomain ) . '</i>)' => 'cf7'
			);

			foreach( $language_options as $label => $item ) {
				$checked = ( $this->options['select_lang'] == $item ) ? ' checked="checked" ' : '';
				echo "<label><input " . $checked . " value='$item' name='". $this->options_name ."[select_lang]' type='radio' /> $label</label><br />";
			}

		}

		function echo_language_dropdown() {
			$available_languages = array(
				__( 'English',    $this->textdomain ) => 'en',
				__( 'Dutch',      $this->textdomain ) => 'nl',
				__( 'French',     $this->textdomain ) => 'fr',
				__( 'German',     $this->textdomain ) => 'de',
				__( 'Portuguese', $this->textdomain ) => 'pt',
				__( 'Russian',    $this->textdomain ) => 'ru',
				__( 'Spanish',    $this->textdomain ) => 'es',
				__( 'Turkish',    $this->textdomain ) => 'tr'
			);
	    
			echo '<label for="' . $this->options_name . '[cf7_lang]">' . __( 'Language', $this->textdomain ) . ":</label>\n";
			echo "<select id='cf7_lang' name='" . $this->options_name . "[cf7_lang]'>";
			foreach($available_languages as $label => $item) {
				$selected = ( $this->options['cf7_lang'] == $item ) ? 'selected="selected"' : '';
				echo "<option value='$item' $selected>$label</option>";
			}
			echo "</select>";

		}

		/*
		 * Options Validation
		 */
		function validate_options( $input ) {
			
			// Allowed values
			$theme_selections = array(
				'bwp_capt', // if the theme for better recaptcha is used
				'cf7'		// if own theme is used
			);

			$validated[ 'select_theme' ] = $this->validate_option(
				$theme_selections, 
				'theme_selection', 
				$input[ 'select_theme' ]
			);

			// Allowed values
			$themes = array(
				'red',
				'white',
				'blackglass',
				'clean',
				'custom'
			);

			$validated[ 'cf7_theme' ] = $this->validate_option(
				$themes,
				'cf7_theme',
				$input[ 'cf7_theme' ]
			);

			// Allowed values
			$language_selections = array (
				'bwp_capt',
				'cf7'
			);

			$validated[ 'select_lang' ] = $this->validate_option(
				$language_selections,
				'select_lang',
				$input[ 'select_lang' ]
			);

			// Allowed values
			$recaptcha_languages = array(
				'en',
				'nl',
				'fr', 
				'de',
				'pt',
				'ru',
				'es',
				'tr'
			);

			$validated[ 'cf7_lang' ] = $this->validate_option(
				$recaptcha_languages,
				'cf7_lang',
				$input[ 'cf7_lang' ]
			);

			return $validated;
		}

		// Check if option is valid
		protected function validate_option( $allowed_values, $key, $value ) {
			if ( in_array( $value, $allowed_values ) ) {
				return $value;
			} else {
				return $this->options[ $key ];
			}
		}

		/**
		 * FILTERS
		 */

		function ajax_json_echo_filter( $items ) {
			if ( ! isset( $items[ 'onSubmit' ] ) || ! is_array( $items[ 'onSubmit' ] ) )
				$items[ 'onSubmit' ] = array();

			$items[ 'onSubmit' ][] = 'if (typeof Recaptcha != "undefined") { Recaptcha.reload(); }';

			return $items;
		}

		/**
		 * Validation code
		 */
		function recaptcha_validation_filter( $result, $tag ) {

			global $bwp_capt;

			$name = $tag[ 'name' ];

			$errors = new WP_Error();

			$errors = $bwp_capt->check_reg_recaptcha( $errors );

			$error_list = $errors->get_error_messages( null );

			if ( ! empty( $error_list ) ) {

				$result[ 'valid' ] = false;
				$error_out = "";
				foreach ( $error_list as $value ) {
					$error_out .= $value;	
				}
				$result[ 'reason' ][ $name ] = $error_out;
			}

			return $result;
		}


		/**
		 * SHORTCODE
		 */

		/**
		 * Shortcode generator registration
		 */
		function tag_generator_recaptcha() {
			if ( function_exists( 'wpcf7_add_tag_generator' ) && $this->meets_requirements() ) {
				wpcf7_add_tag_generator(
					'recaptcha', // name
					'reCAPTCHA', // display name
					'cf7recaptcha-tag-pane', // layer id
					array( &$this, 'tag_pane' ) // shortcode generator handler
				);
			}
		}

		/**
		 * Recaptcha shortcode output
		 */
		function shortcode_handler( $tag ) {

			global $wpcf7_contact_form, $bwp_capt;

			if ( $bwp_capt->user_can_bypass() ) return '';

			$name = $tag[ 'name' ];

			// set bwp recaptcha options to cf7 bwp recaptcha options
			$bwp_capt_theme = $bwp_capt->options['select_theme'];
			$bwp_capt_lang  = $bwp_capt->options['select_lang'];

			// override bwp recaptcha options with cf7 bwp recaptcha ones when needed
			if ( $this->options[ 'select_theme' ] === 'cf7' 
			&& isset( $this->options[ 'cf7_theme' ] ) ) {
				$bwp_capt->options['select_theme'] = $this->options[ 'cf7_theme' ];
			}

			if ( $this->options[ 'select_lang' ] === 'cf7' 
			&& isset( $this->options[ 'cf7_lang' ] ) ) {
				$bwp_capt->options['select_lang'] = $this->options[ 'cf7_lang' ];
			}

			// load recaptcha library
			if ( ! defined( 'RECAPTCHA_API_SERVER' ) )
				require_once( plugin_dir_path( $bwp_capt->plugin_file ) . 'includes/recaptcha/recaptchalib.php' );

			// add_recaptcha echoes so we have to buffer its output
			// to store it in a variable instead.
			ob_start();
			$bwp_capt->add_recaptcha();
			$html = ob_get_contents();
			ob_end_clean();

			// restore bwp recaptcha options
			$bwp_capt->options['select_theme'] = $bwp_capt_theme;
			$bwp_capt->options['select_lang']  = $bwp_capt_lang;


			$validation_error = '';
			if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) )
				$validation_error = $wpcf7_contact_form->validation_error( $name );

			$html .= '<span class="wpcf7-form-control-wrap ' . $name . '">' . $validation_error . '</span>';
			return $html;

		}

		/**
		 * Contact Form 7 reCaptcha tag generator output
		 */
		function tag_pane( &$contact_form ) {
?>
			<div id="cf7recaptcha-tag-pane" class="hidden">
				<form action="">
					<table>

					<?php if ( ! $this->meets_requirements() ) : ?>
						<tr>
							<td colspan="2">
								<strong style="color: #e6255b">you need reCAPTCHA</strong>
								<br />
							</td>
						</tr>
					<?php endif; ?>

						<tr>
							<td>
								<?php _e( 'Name', $this->textdomain ); ?>
								<br />
								<input type="text" name="name" class="tag-name oneline" />
							</td>
							<td></td>
						</tr>
					</table>

					<div class="tg-tag">
						<?php _e( "Copy this code and paste it into the form left.", $this->textdomain ); ?>
						<br />
						<input type="text" name="recaptcha" class="tag" readonly="readonly" onfocus="this.select()" />
					</div>
				</form>
			</div>
<?php
		}


		/**
		 * REQUIREMENTS AND NOTICES
		 */

		/**
		 * Check if requirements are met
		 */
		function meets_requirements() {
			static $requirements_met;
			if ( ! isset( $requirements_met ) ) {
				$requirements_met = $this->check_bwp_capt() && $this->check_cf7();
			}
			return $requirements_met;
		}

		/**
		 * Check if BWP recaptcha plugin is active
		 */
		function check_bwp_capt() {
			static $is_bwp_capt_active;
			if ( ! isset( $is_bwp_capt_active ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is_bwp_capt_active = is_plugin_active('bwp-recaptcha/bwp-recaptcha.php');
			}
			return $is_bwp_capt_active;
		}

		/**
		 * Check if Contact Form 7 plugin is active
		 */
		function check_cf7() {
			static $is_cf7_active;
			if ( ! isset( $is_cf7_active ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$is_cf7_active = is_plugin_active('contact-form-7/wp-contact-form-7.php');
			}
			return $is_cf7_active;
		}

		/**
		 * Show a warning if any of the requirements is not met.
		 */
		function admin_notice() {
			global $plugin_page;

			if ( ! $this->check_cf7() ) :
?>

				<div id="message" class="updated fade">
					<p>
						<?php _e( "You are using Contact Form 7 Better WordPress reCAPTCHA Extension." , $this->textdomain); ?> 
						<?php _e( "This works with the Contact Form 7 plugin, but the Contact Form 7 plugin is not activated.", $this->textdomain ); ?>
						&mdash; Contact Form 7 <a href="http://wordpress.org/extend/plugins/contact-form-7/">http://wordpress.org/extend/plugins/contact-form-7/</a>
					</p>
				</div>
<?php
			endif;

			if ( ! $this->check_bwp_capt() ) :

?>

				<div id="message" class="updated fade">
					<p>
						<?php _e( "You are using Contact Form 7 Better WordPress reCAPTCHA Extension." , $this->textdomain); ?> 
						<?php _e( "This works with the Better WordPress reCAPTCHA plugin, but the Better WordPress reCAPTCHA plugin is not activated.", $this->textdomain ); ?>
						&mdash; WP-reCAPTCHA <a href="http://wordpress.org/extend/plugins/bwp-recaptcha/">http://wordpress.org/extend/plugins/bwp-recaptcha/</a>
					</p>
				</div>
<?php
			endif;

		}
		
		/**
		 * UNINSTALL HANDLING
		 */

		/**
		 * Uninstall this plugin
		 */
		function uninstall( $options ) {
			unregister_setting( "${options}_group", $options ); // unregister settings page
			delete_option( $options ); // delete stored options
		}

	} // end of class declaration

} // end of class exists clause

?>