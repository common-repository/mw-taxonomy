<?php
/*
Plugin Name: MW Taxonomy
Plugin URI: https://wordpress.org/plugins/mw-taxonomy/
Description: Makes it possible to add any custom taxonomy to your wp website
Version: 1.1.4
Auther Mats Westholm
Licens: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mw-taxonomy
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'plugins_loaded', 'mw_taxonomy_load_textdomain' );
function mw_taxonomy_load_textdomain(){
	load_plugin_textdomain( 'mw-taxonomy', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

require 'includes/class-mw-taxonomy.php';
$obj = new MW_taxonomy();
$obj->run();

?>
