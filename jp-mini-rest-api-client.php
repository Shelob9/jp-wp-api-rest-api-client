<?php
/*
Plugin Name: JP REST API  Client
Plugin URI:
Description: A mini client for using the WordPress REST API inside WordPress
Version: 0.1.2
Author: Josh Pollock
Author URI: http://JoshPress.net
License: GPL v2 or later
*/

/**
 * Based on an article I wrote for Torque: http://torquemag.io/?p=72403 but improved.
 */


/**
 * Include client if JSON API is installed and of sufficient version
 */
if ( defined( 'JSON_API_VERSION' ) && version_compare( JSON_API_VERSION, '1.1.1') >= 0 ) {
	include_once( dirname( __FILE__ ) . 'client.php' );
}

