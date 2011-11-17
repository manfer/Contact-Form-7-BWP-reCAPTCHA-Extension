<?php

// just making sure the constant is defined
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
 
require_once( 'ASDEnvironment.class.php' );

if ( ! class_exists( 'WPASDPlugin' ) ) {

	abstract class WPASDPlugin {
 		protected $ASDEnvironment; // what ASDEnvironment are we in
		protected $options_name; // the name of the options associated with this plugin
		protected $textdomain_name; // name of the textdomain to load

		protected $options;

		function WPASDPlugin( $options_name, $textdomain_name ) {
			$args = func_get_args();
			call_user_func_array( array( &$this, "__construct" ), $args );
		}

		function __construct( $options_name, $textdomain_name ) {
			$this->ASDEnvironment = WPASDPlugin::determine_ASDEnvironment();
			$this->options_name = $options_name;
			$this->textdomain_name = $textdomain_name;

			$this->load_textdomain();

			$this->options = WPASDPlugin::retrieve_options( $this->options_name );

			$this->register_initialization();
		}

		protected function register_initialization() {

			add_action( 'plugins_loaded', array( &$this, 'initialize' ) );

		}

		protected function load_textdomain() {
			if ( ! isset( $this->textdomain_name ) || $this->textdomain_name == '' ) {
				return;
			}

			load_plugin_textdomain(
				$this->textdomain_name,
				false,
				dirname( plugin_basename( ASD_PLUGIN_FILE ) ) . '/languages'
			);
		}
	
		function initialize() {

			$this->pre_init();

			//register_activation_hook(ASD_PLUGIN_FILE, array($this, 'activate'));
			add_action( 'admin_init', array( &$this, 'register_settings_group' ) );
			add_action( 'admin_menu', array( &$this, 'register_settings_page' ) );

			$this->register_actions();
			$this->register_filters();
			$this->register_scripts();

			$this->post_init();

		}
	
		function register_settings_group() {
			register_setting( $this->options_name . "_group", $this->options_name, array( &$this, 'validate_options' ) );

			$this->add_settings();
		}

		abstract protected function add_settings();

		abstract protected function pre_init();
		abstract protected function post_init();

		// sub-classes determine what actions and filters to hook
		abstract protected function register_actions();
		abstract protected function register_filters();
		abstract protected function register_scripts();        

		// ASDEnvironment checking
		static function determine_ASDEnvironment() {
			global $wpmu_version;

			if ( function_exists( 'is_multisite' ) )
				if (is_multisite())
					return ASDEnvironment::WordPressMS;

			if ( ! empty( $wpmu_version ) )
				return ASDEnvironment::WordPressMU;

			return ASDEnvironment::WordPress;
		}

		// path finding
		static function plugins_directory() {
			if ( WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU )
				return WP_CONTENT_DIR . '/mu-plugins';
			else
				return WP_CONTENT_DIR . '/plugins';
		}

		static function plugins_url() {
			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU)
				return get_option('siteurl') . '/wp-content/mu-plugins';
			else*/
				return get_option( 'siteurl' ) . '/wp-content/plugins';
		}

		static function path_to_plugin_directory() {
			$current_directory = basename( dirname( ASD_PLUGIN_FILE ) );

			return WPASDPlugin::plugins_directory() . "/${current_directory}";
		}

		static function url_to_plugin_directory() {
			$current_directory = basename( dirname( ASD_PLUGIN_FILE ) );

			return WPASDPlugin::plugins_url() . "/${current_directory}";
		}

		static function path_to_plugin($file_path) {
			$file_name = basename( ASD_PLUGIN_FILE ); // /etc/blah/file.txt => file.txt

			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU)
				return WPASDPlugin::plugins_directory() . "/${file_name}";
			else*/
				return WPASDPlugin::path_to_plugin_directory() . "/${file_name}";
		}

		function activate() {
			$this->register_default_options();
		}

		// options
		abstract protected function register_default_options();
		abstract function validate_options( $input );

		abstract function register_settings_page();

		function add_options_page( $page_title, $menu_title ) {

			/**if ($this->ASDEnvironment == ASDEnvironment::WordPressMU && $this->is_authority()) {
				add_submenu_page('wpmu-admin.php', $page_title, $menu_title, 'manage_options', $this->getClassFile(), array(&$this, 'show_page_settings') );
			}
    	    
			if ($this->ASDEnvironment == ASDEnvironment::WordPressMS && $this->is_authority()) {
				add_submenu_page('ms-admin.php', $page_title, $menu_title, 'manage_options', $this->getClassFile(), array(&$this, 'show_settings_page') );
			}*/

			add_options_page( $page_title, $menu_title, 'manage_options', $this->getClassFile(), array( &$this, 'show_settings_page' ) );
		}

		abstract protected function show_settings_page();

		function echo_dropdown( $name, $keyvalue, $checked_value ) {
			echo '<select name="' . $name . '" id="' . $name . '">' . "\n";

			foreach ($keyvalue as $key => $value) {
				$checked = ( $value == $checked_value ) ? ' selected="selected" ' : '';
    	
				echo "\t " . '<option value="' . $value . '"' . $checked . ">$key</option> \n";
				$checked = NULL;
			}

			echo "</select> \n";
		}

		function echo_radios( $name, $keyvalue, $checked_value ) {

			foreach ( $keyvalue as $key => $value ) {
				$checked = ( $value == $checked_value ) ? ' checked ' : '';

				echo "\t " . '<input type="radio" name="' . $name . '" id="' . $name . $value . '" value="' . $value . '"' . $checked . '>';
				echo '<label for="' . $name . $value . '">' . $key . "</label><br /> \n";
				$checked = NULL;
			}

		}

		// option retrieval
		static function retrieve_options( $options_name ) {
			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU || WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMS)
				return get_site_option($options_name);
			else*/
				return get_option( $options_name );
		}

		static function uninstall_options( $options_name ) {
			unregister_setting( "${options_name}_group", $options_name );
			WPASDPlugin::remove_options( $options_name );
		}

        static function remove_options( $options_name ) {
			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU || WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMS)
				return delete_site_option($options_name);
			else*/
				return delete_option( $options_name );
		}

		static function update_options( $options_name, $options ) {
			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU || WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMS) {
				return update_site_option($options_name, $options);
			} else{*/
				return update_option( $options_name, $options );
			//}
		}

		static function add_options( $options_name, $options ) {
			/**if (WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMU || WPASDPlugin::determine_ASDEnvironment() == ASDEnvironment::WordPressMS)
				return add_site_option($options_name, $options);
			else*/
				return add_option( $options_name, $options );
		}

		protected function is_multi_blog() {
			return $this->ASDEnvironment != ASDEnvironment::WordPress;
		}
        
		// calls the appropriate 'authority' checking function depending on the ASDEnvironment
		protected function is_authority() {
			if ( $this->ASDEnvironment == ASDEnvironment::WordPress )
				return is_admin();

			/**if ($this->ASDEnvironment == ASDEnvironment::WordPressMU)
				return is_site_admin();

			if ($this->ASDEnvironment == ASDEnvironment::WordPressMS)
				return is_super_admin();*/
		}

		protected function validate_dropdown( $array, $key, $value ) {
			// make sure that the capability that was wupplied is a valid capability from the drop-down list
			if ( in_array( $value, $array ) ) {
				return $value;
			} else {
				return $this->options[ $key ];
			}
		}

		abstract protected function getClassFile();
	}
}

?>