<?php
/*
Plugin Name: Contact Form 7 BWP reCAPTCHA Extension
Plugin URI: http://www.manfersite.tk/cf7bre
Description: Provides Better WordPress reCAPTCHA possibilities to the Contact Form 7 plugin. Requires both.
Version: 0.0.1
Author: Fernando San Julián
Email: manfer.site@gmail.com
Author URI: http://manfersite.tk
Text Domain: cf7_bwp_capt
Domain Path: /languages/
License: GPL2
*/

/*  Copyright 2011 Fernando San Julián (email: manfer.site@gmail.com)
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301
*/


// this is the 'driver' file that instantiates the objects and registers every hook

define('ALLOW_INCLUDE', true);
define('CF7BWPRECAPT_VERSION', '0.0.1');
define('CF7BWPRECAPT_URL', 'http://manfersite.tk');
define('CF7BWPRECAPT_TITLE', 'Contact Form 7 BWP reCAPTCHA Extension');

require_once('includes/CF7bwpCAPT.class.php');

define('ASD_PLUGIN_FILE', __FILE__ );

$cf7_bwp_capt = new CF7bwpCAPT('cf7_bwp_capt_options', 'cf7_bwp_capt');

register_activation_hook( __FILE__ , array($cf7_bwp_capt, 'activate'));

?>