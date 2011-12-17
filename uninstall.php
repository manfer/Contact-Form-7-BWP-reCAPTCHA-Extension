<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

require_once( 'includes/CF7bwpCAPT.class.php' );

if ( class_exists( 'CF7bwpCAPT' ) ) {
	CF7bwpCAPT::uninstall( 'cf7_bwp_capt_options' );
}

?>