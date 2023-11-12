<?php
/**
 * Plugin Name: WPForms Mailblast IOStack Integration
 * Plugin URI: https://#
 * Description: Add users to a Mailblast list and send transactional emails from the IOStack API.
 * Version: 1.1.0
 * Author: Eric Mathison
 * Author URI: https://#
 * Text Domain: wpforms-mailblast-iostack-integration
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package WPForms_Mailblast_IOStack_Integration
 * @since 1.1.0
 * @copyright Copyright (c) 2023, Eric Mathison
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WPFORMS_MAILBLAST_IOSTACK_INTEGRATION_VERSION', '1.1.0' );

/**
 * Load plugin files.
 */
function wpforms_mailblast_iostack_integration() {
    load_plugin_textdomain( 'wpforms-mailblast-iostack-integration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wpforms-mailblast-iostack-integration.php' );
}

add_action('wpforms_loaded', 'wpforms_mailblast_iostack_integration');