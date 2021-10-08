<?php
/**
 * Plugin Name: WPForms BigMailer Integration
 * Plugin URI: https://#
 * Description: Enables you to easily send WPForms subscribers to your BigMailer account.
 * Version: 1.0.0
 * Author: Eric Mathison
 * Author URI: https://#
 * Text Domain: wpforms-bigmailer-integration
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package WPForms_BigMailer_Integration
 * @since 1.0.0
 * @copyright Copyright (c) 2021, Eric Mathison
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WPFORMS_BIGMAILER_INTEGRATION_VERSION', '1.0.0' );

/**
 * Load plugin files.
 */
function wpforms_bigmailer_integration() {
    load_plugin_textdomain( 'wpforms-bigmailer-integration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wpforms-bigmailer-integration.php' );
}

add_action('wpforms_loaded', 'wpforms_bigmailer_integration');