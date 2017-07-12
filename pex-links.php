<?php
/**
 * Plugin Name: PEX for INDG
 * Plugin URI:  http://macrossys.com/
 * Description: Assimilate PEX content easily into your WP site
 * Version:     1.0
 * Author:      Macrossys Ltd
 * Author URI:  https://www.macrossys.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pex-links
 * Domain Path: /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'PEX_LINKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PEX_LINKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PEX_LINKS_FILE', __FILE__ );
define( 'PEX_LINKS_BASENAME', plugin_basename( PEX_LINKS_FILE ) );

require_once PEX_LINKS_PLUGIN_DIR . 'includes/class-pex-links.php';

/**
 * Begins execution of the plugin.
 */
$Pex_Links = new Pex_Links();

/*
* Activation/deactivation/deletion stuff
*/
register_activation_hook( __FILE__, array ( $Pex_Links, 'activation_hook') );
register_deactivation_hook( __FILE__, array ( $Pex_Links, 'deactivation_hook') );
//deletion stuff is placed in uninstall.php