<?php

/* A plugin to use BWP reCAPTCHA in Contact Form 7.
 * This class needs BWP reCAPTCHA and Contact Form 7 plugin installed and activated.
 * http://wordpress.org/extend/plugins/bwp-recaptcha/
 * http://wordpress.org/extend/plugins/contact-form-7/
 */


require_once( 'WPASDPlugin.class.php' );

if ( ! class_exists( 'CF7bwpCAPT' ) ) {

	class CF7bwpCAPT extends WPASDPlugin {

		const recaptcha_options_name = 'bwp_capt_theme';

		// member variables
		private $is_useable;

		// php4 Constructor
		function CF7bwpCAPT( $options_name, $textdomain_name ) {
			$args = func_get_args();
			call_user_func_array( array( &$this, "__construct" ), $args );
		}

		// php5 Constructor
		function __construct( $options_name, $textdomain_name ) {
			parent::__construct( $options_name, $textdomain_name );
		}

		function getClassFile() {
			return __FILE__;
		}

		function pre_init() {
			// require the libraries
			$this->require_library();
		}

		function post_init() {
			// register CF7 hooks
			$this->register_cf7();
		}

		// set the default options
		function register_default_options() {
			if ( is_array( $this->options ) && isset( $this->options[ 'reset_on_activate' ] ) && $this->options[ 'reset_on_activate' ] !== 'on')
				return;	

			$default_options = array();

			// reset on activate
			$default_options[ 'reset_on_activate' ] = 'on';

			// one of {'bwp_capt', 'cf7'}
			$default_options[ 'select_theme' ] = 'bwp_capt';
		
			// one of {'red', 'white', 'blackglass', 'clean'}
			$default_options[ 'cf7_theme' ] = 'red';
		
			// one of {'bwp_capt', 'cf7'}
			$default_options[ 'select_lang' ] = 'bwp_capt';
		
			// one of {'en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr' }
			$default_options[ 'cf7_lang' ] = 'en';

			// add the options based on the environment
			WPASDPlugin::update_options( $this->options_name, $default_options );
		}

		function add_settings() {

			// Theme Options Section
			add_settings_section( 'cf7_bwp_capt_ext_theme_section', __( 'Theme Options', $this->textdomain_name ), array( &$this, 'echo_theme_section_info' ), $this->options_name . '_page' );
			add_settings_field( 'cf7_bwp_capt_ext_theme_preselection', __( 'Theme Preselection', $this->textdomain_name ), array( &$this, 'echo_theme_selection_radio' ), $this->options_name . '_page', 'cf7_bwp_capt_ext_theme_section' );
			add_settings_field( 'cf7_bwp_capt_ext_own_theme', __( 'Own Theme (<i>if selected</i>)', $this->textdomain_name ), array( &$this, 'echo_theme_dropdown' ), $this->options_name . '_page', 'cf7_bwp_capt_ext_theme_section' );

			// General Options Section
			add_settings_section( 'cf7_bwp_capt_ext_general_section', __( 'General Options', $this->textdomain_name ), array( &$this, 'echo_general_section_info' ), $this->options_name . '_page' );
			add_settings_field( 'cf7_bwp_capt_ext_language_preselection', __( 'Language Preselection', $this->textdomain_name ), array( &$this, 'echo_language_selection_radio' ), $this->options_name . '_page', 'cf7_bwp_capt_ext_general_section' );
			add_settings_field( 'cf7_bwp_capt_ext_own_language', __( 'Own Language (<i>if selected</i>)', $this->textdomain_name ), array( &$this, 'echo_language_dropdown' ), $this->options_name . '_page', 'cf7_bwp_capt_ext_general_section' );

			// Debug Settings Section
			add_settings_section( 'cf7_bwp_capt_ext_debug_section', __( 'DEBUG Options', $this->textdomain_name ), array( &$this, 'echo_debug_section_info' ), $this->options_name . '_page' );
			add_settings_field( 'cf7_bwp_capt_ext_reset_on_activate', __( 'Reset on Activate', $this->textdomain_name ), array( &$this, 'echo_reset_on_activate_option' ), $this->options_name . '_page', 'cf7_bwp_capt_ext_debug_section' );
		}

		function echo_theme_section_info() {
			echo '<p>' . __( 'Here you can set which options to use for the themes option of the BWP reCAPTCHA forms in the Contact Form 7 forms.', $this->textdomain_name ) . "</p>\n";
		}

		function echo_general_section_info() {
			echo '<p>' . __( 'Here you can do the same with some of the general options of BWP reCAPTCHA.', $this->textdomain_name ) . "</p>\n";
		}

		function echo_debug_section_info() {
			echo '<p>' . __( 'Some debug options.', $this->textdomain_name ) . "</p>\n";
		}
	
		function echo_reset_on_activate_option() {
			$checked = ( $this->options[ 'reset_on_activate' ] === 'on' ) ? ' checked="checked" ' : '';
			echo '<input type="checkbox" id="' . $this->options_name. '[reset_on_activate]" name="' . $this->options_name. '[reset_on_activate]" value="on"' . $checked . '/>'; 
		}

		function validate_options( $input ) {

			$theme_selections = array(
				'bwp_capt', // if the theme for better recaptcha should be used
				'cf7'		// if an own theme should be used
			);

			$validated[ 'select_theme' ] = $this->validate_dropdown(
				$theme_selections, 
				'theme_selection', 
				$input[ 'select_theme' ]
			);

			if ( $validated[ 'select_theme' ] === 'cf7' ) {
	    
				$themes = array(
					'red',
					'white',
					'blackglass',
					'clean'
				);

				$validated[ 'cf7_theme' ] = $this->validate_dropdown(
					$themes,
					'cf7_theme',
					$input[ 'cf7_theme' ]
				);
			} else {
				$validated[ 'cf7_theme' ] = $this->options[ 'cf7_theme' ];
			}	    

			$language_selections = array (
				'bwp_capt',
				'cf7'
			);

			$validated[ 'select_lang' ] = $this->validate_dropdown(
				$language_selections,
				'select_lang',
				$input[ 'select_lang' ]
			);

			if ($validated[ 'select_lang' ] === 'cf7') {

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

				$validated[ 'cf7_lang' ] = $this->validate_dropdown(
					$recaptcha_languages,
					'cf7_lang',
					$input[ 'cf7_lang' ]
				);
			} else {
				$validated[ 'cf7_lang' ] = $this->options['cf7_lang'];
			}

			$validated[ 'reset_on_activate' ] = ( $input[ 'reset_on_activate' ] === 'on' ) ? 'on' : 'off';

			return $validated;
		}

		function require_library() {}

		function register_scripts() {
			$use_ssl = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on';
			if ( $use_ssl ) {
				$server = RECAPTCHA_API_SECURE_SERVER;
			} else {
				$server = RECAPTCHA_API_SERVER;
			}
		}

		function register_actions() {
			global $wp_version;
			add_action( 'admin_notices', array( &$this, 'admin_notice' ) );
			if ($this->useable()) {
				add_action( 'admin_init', array( &$this, 'tag_generator_recaptcha' ), 46 );
				add_action( 'admin_init', array( &$this, 'cf7_bwp_capt_register_styles' ) );
			}
		}

		function register_filters() {
			if ( $this->useable() ) {
				add_filter( 'wpcf7_validate_recaptcha', array( &$this, 'recaptcha_validation_filter' ), 10, 2 );
				add_filter( 'wpcf7_ajax_json_echo', array( &$this, 'ajax_json_echo_filter' ) );
			}
		}

		function register_cf7() {
			// CF7 Shortcode Handler
			if ( function_exists( 'wpcf7_add_shortcode' ) && $this->useable() ) {
				wpcf7_add_shortcode( 'recaptcha', array( &$this, 'shortcode_handler' ), true );	
			}
		}

		function useable() {
			if ( ! isset( $this->is_useable ) ) {
				$this->is_useable = $this->is_bwp_capt_active() && $this->is_cf7_active();
			}

			return $this->is_useable;
		}

		function is_bwp_capt_active() {
			return in_array( 
				'bwp-recaptcha/bwp-recaptcha.php', 
				apply_filters( 
					'active_plugins', 
					get_option( 'active_plugins' )
				)
			);
		}

		function is_cf7_active() {
			return in_array( 
				'contact-form-7/wp-contact-form-7.php', 
				apply_filters(
					'active_plugins', 
					get_option( 'active_plugins' )
				)
			);
		}

		function register_settings_page() {
			$page = add_submenu_page( BWP_CAPT_OPTION_GENERAL, __( 'CF7 BWP reCAPTCHA Extension Options', $this->textdomain_name ), __( 'CF7 Options', $this->textdomain_name ), BWP_CAPT_CAPABILITY, BWP_CAPT_OPTION_CF7, array( $this, 'show_settings_page' ) );
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

		function echo_theme_selection_radio() {

			$recaptcha_options = WPASDPlugin::retrieve_options( CF7bwpCAPT::recaptcha_options_name );

			$themes = array (
				'red'        => __( 'Red',         $this->textdomain_name ),
				'white'      => __( 'White',       $this->textdomain_name ),
				'blackglass' => __( 'Black Glass', $this->textdomain_name ),
				'clean'      => __( 'Clean',       $this->textdomain_name )
			);

			$bwp_capt_theme = ( is_array( $recaptcha_options ) && isset( $recaptcha_options[ 'select_theme' ] ) ) ? ' (' . __( 'currently', $this->textdomain_name ) . ': <i>' . $themes[ $recaptcha_options[ 'select_theme' ] ] . '</i>)' : '';

			$theme_options = array (
				__( 'BWP reCAPTCHA Theme', $this->textdomain_name ) . $bwp_capt_theme => 'bwp_capt', 		
				__( 'Own Theme' , $this->textdomain_name ) . ' (<i>' . __( 'select below', $this->textdomain_name ) . '</i>)' => 'cf7'
			);

			$this->echo_radios( $this->options_name . '[select_theme]', $theme_options, $this->options[ 'select_theme' ] );
		}

		function echo_theme_dropdown() {
			$themes = array (
				__( 'Red',         $this->textdomain_name ) => 'red',
				__( 'White',       $this->textdomain_name ) => 'white',
				__( 'Black Glass', $this->textdomain_name ) => 'blackglass',
				__( 'Clean',       $this->textdomain_name ) => 'clean'
			);

			echo '<label for="' . $this->options_name . '[cf7_theme]">' . __( 'Theme', $this->textdomain_name ) . ":</label>\n";     
			$this->echo_dropdown( $this->options_name . '[cf7_theme]', $themes, $this->options[ 'cf7_theme' ] );
		}

		function echo_language_selection_radio() {

			$recaptcha_options = WPASDPlugin::retrieve_options( CF7bwpCAPT::recaptcha_options_name );

			$languages = array (
				'en' => __( 'English',    $this->textdomain_name ),
				'nl' => __( 'Dutch',      $this->textdomain_name ),
				'fr' => __( 'French',     $this->textdomain_name ),
				'de' => __( 'German',     $this->textdomain_name ),
				'pt' => __( 'Portuguese', $this->textdomain_name ),
				'ru' => __( 'Russian',    $this->textdomain_name ),
				'es' => __( 'Spanish',    $this->textdomain_name ),
				'tr' => __( 'Turkish',    $this->textdomain_name )
			);

			$bwp_capt_lang = ( is_array( $recaptcha_options ) && isset( $recaptcha_options[ 'select_lang' ] ) ) ? ' (' . __( 'currently', $this->textdomain_name ) . ': <i>' . $languages[ $recaptcha_options[ 'select_lang' ] ] . '</i>)' : '';
    
			$language_options = array (
				__( 'BWP reCAPTCHA Language', $this->textdomain_name ) . $bwp_capt_lang => 'bwp_capt',
				__( 'Own Language', $this->textdomain_name ) . ' (<i>' . __( 'select below', $this->textdomain_name ) . '</i>)' => 'cf7'
			);

			$this->echo_radios( $this->options_name . '[select_lang]', $language_options, $this->options[ 'select_lang' ]);
		}

		function echo_language_dropdown() {
			$languages = array(
				__( 'English',    $this->textdomain_name ) => 'en',
				__( 'Dutch',      $this->textdomain_name ) => 'nl',
				__( 'French',     $this->textdomain_name ) => 'fr',
				__( 'German',     $this->textdomain_name ) => 'de',
				__( 'Portuguese', $this->textdomain_name ) => 'pt',
				__( 'Russian',    $this->textdomain_name ) => 'ru',
				__( 'Spanish',    $this->textdomain_name ) => 'es',
				__( 'Turkish',    $this->textdomain_name ) => 'tr'
			);
	    
			echo '<label for="' . $this->options_name . '[cf7_lang]">' . __( 'Language', $this->textdomain_name ) . ":</label>\n";
			$this->echo_dropdown( $this->options_name . '[cf7_lang]', $languages, $this->options[ 'cf7_lang' ] );
		}

		function ajax_json_echo_filter( $items ) {
			if ( ! is_array( $items[ 'onSubmit' ] ) )
				$items[ 'onSubmit' ] = array();

			$items[ 'onSubmit' ][] = 'if (typeof Recaptcha != "undefined") { Recaptcha.reload(); }';

			return $items;
		}

		function recaptcha_validation_filter( $result, $tag ) {

			global $bwp_capt;

			$name = $tag[ 'name' ];

			// if(!$this->is_multi_blog()) {

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
		
		    // } else {
		        //$recaptcha->validate_recaptcha_response_wpmu($result);
		    // }

			return $result;
		}

		function tag_generator_recaptcha() {
			if ( function_exists( 'wpcf7_add_tag_generator' ) && $this->useable() ) {
				wpcf7_add_tag_generator(
					'recaptcha',
					'reCAPTCHA',
					'cf7recaptcha-tg-pane',
					array( &$this, 'tag_pane' )
				);
			}
		}

		function shortcode_handler( $tag ) {

			global $wpcf7_contact_form, $bwp_capt;

			if ( $bwp_capt->user_can_bypass() ) return '';

			$name = $tag[ 'name' ];

			$recaptcha_options = WPASDPlugin::retrieve_options( self::recaptcha_options_name );

			$used_theme = '';

			if ( $this->options[ 'select_theme' ] === 'bwp_capt' 
			&& isset( $recaptcha_options[ 'select_theme' ] ) ) {
				$used_theme = $recaptcha_options[ 'select_theme' ];
			} elseif ( $this->options[ 'select_theme' ] === 'cf7' 
			&& isset( $this->options[ 'cf7_theme' ] ) ) {
				$used_theme = $this->options[ 'cf7_theme' ];
			} else {
				$used_theme = 'red';
			}

			$used_language = '';

			if ( $this->options[ 'select_lang' ] === 'bwp_capt' 
			&& isset( $recaptcha_options[ 'select_lang' ] ) ) {
				$used_language = $recaptcha_options[ 'select_lang' ];
			} elseif ( $this->options[ 'select_lang' ] === 'cf7' 
			&& isset( $this->options[ 'cf7_lang' ] ) ) {
				$used_language = $this->options[ 'cf7_lang' ];
			} else {
				$used_language = 'en';
			}

			$js_options = <<<JSOPTS
<script type='text/javascript'>
var RecaptchaOptions = { theme : '{$used_theme}', lang : '{$used_language}'};
</script>
JSOPTS;

			$html = $js_options;

			require_once( dirname(__FILE__) . '/recaptcha/recaptchalib.php' );

			if ( function_exists( 'recaptcha_get_html' ) && !defined( 'BWP_CAPT_ADDED' ) ) {

				// make sure we add only one recaptcha instance
				define( 'BWP_CAPT_ADDED', true );

				$captcha_error = '';
				if ( ! empty( $_GET[ 'cerror' ] ) && 'incorrect-captcha-sol' == $_GET[ 'cerror' ] )
					$captcha_error = $_GET[ 'cerror' ];

				if ( ! empty( $_SESSION[ 'bwp_capt_akismet_needed' ]) && 'yes' == $_SESSION[ 'bwp_capt_akismet_needed' ] ) {
					$html .= '<p class="bwp-capt-spam-identified">' . _e( 'Your comment was identified as spam, please complete the CAPTCHA below:', 'bwp-recaptcha' ) . '</p>';
				}

				do_action( 'bwp_capt_before_add_captcha' );

				if ( 'redirect' == $bwp_capt->options[ 'select_response' ]  && ! is_admin() ) {
					$html .= '<input type="hidden" name="error_redirect_to" value="' . esc_attr_e( $bwp_capt->get_current_comment_page_link() ) . '" />';
				}

				$use_ssl = ( isset( $_SERVER[ 'HTTPS' ]) && 'on' == $_SERVER[ 'HTTPS' ] ) ? true : false;
				if ( ! empty( $bwp_capt->options[ 'input_pubkey' ] ) )
					$html .= recaptcha_get_html( $bwp_capt->options[ 'input_pubkey' ], $captcha_error, $use_ssl );
				elseif ( current_user_can( 'manage_options' ) )
					$html .= _e( "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>", 'bwp-recaptcha' );
			}

			$validation_error = '';
			if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) )
			$validation_error = $wpcf7_contact_form->validation_error( $name );

			$html .= '<span class="wpcf7-form-control-wrap ' . $name . '">' . $validation_error . '</span>';

			return $html;
		}

		function tag_pane( &$contact_form ) {
?>
			<div id="cf7recaptcha-tg-pane" class="hidden">
				<form action="">
					<table>

					<?php if ( ! $this->useable() ) : ?>
						<tr>
							<td colspan="2">
								<strong style="color: #e6255b">you need reCAPTCHA</strong>
								<br />
							</td>
						</tr>
					<?php endif; ?>

						<tr>
							<td>
								<?php _e( 'Name', $this->textdomain_name ); ?>
								<br />
								<input type="text" name="name" class="tg-name oneline" />
							</td>
							<td></td>
						</tr>
					</table>

					<div class="tg-tag">
						<?php _e( "Copy this code and paste it into the form left.", $this->textdomain_name ); ?>
						<br />
						<input type="text" name="recaptcha" class="tag" readonly="readonly" onfocus="this.select()" />
					</div>
				</form>
			</div>
<?php
		}

		function admin_notice() {
			global $plugin_page;

			if ( ! $this->is_cf7_active() ) :
?>

				<div id="message" class="updated fade">
					<p>
						<?php _e( "You are using Contact Form 7 Better WordPress reCAPTCHA Extension." , $this->textdomain_name); ?> 
						<?php _e( "This works with the Contact Form 7 plugin, but the Contact Form 7 plugin is not activated.", $this->textdomain_name ); ?>
						&mdash; Contact Form 7 <a href="http://wordpress.org/extend/plugins/contact-form-7/">http://wordpress.org/extend/plugins/contact-form-7/</a>
					</p>
				</div>
<?php
			endif;

			if ( ! $this->is_bwp_capt_active() ) :

?>

				<div id="message" class="updated fade">
					<p>
						<?php _e( "You are using Contact Form 7 Better WordPress reCAPTCHA Extension." , $this->textdomain_name); ?> 
						<?php _e( "This works with the Better WordPress reCAPTCHA plugin, but the Better WordPress reCAPTCHA plugin is not activated.", $this->textdomain_name ); ?>
						&mdash; WP-reCAPTCHA <a href="http://wordpress.org/extend/plugins/bwp-recaptcha/">http://wordpress.org/extend/plugins/bwp-recaptcha/</a>
					</p>
				</div>
<?php
			endif;

		}

	} // end of class declaration

} // end of class exists clause

?>