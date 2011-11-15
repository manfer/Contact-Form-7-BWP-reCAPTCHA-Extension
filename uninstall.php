<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

require_once('includes/WPASDPlugin.class.php');

if (class_exists('WPASDPlugin')) {

    WPASDPlugin::uninstall_options('cf7_bwp_recapt_options');
    
}


?>