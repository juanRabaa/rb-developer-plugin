<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://genosha.com.ar
 * @since             1.0.0
 * @package           Gen_Wp_Post_Views_Count_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       RB Developer Plugin
 * Plugin URI:        https://genosha.com.ar
 * Description:       Count views for posts
 * Version:           1.0.0
 * Author:            Juan Cruz Rabaglia
 * Author URI:        https://genosha.com.ar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       posts-views
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// SlotFill (portal rendering) https://developer.wordpress.org/block-editor/reference-guides/components/slot-fill/
// https://developer.wordpress.org/block-editor/reference-guides/slotfills/
// Post meta: https://developer.wordpress.org/reference/functions/register_post_meta/
// Other meta: https://developer.wordpress.org/reference/functions/register_meta/
// TODO: Conditionally show/hide fields.
// TODO: Allow to pass static html string as component to show information instead of controls
// TODO: Documentation. Repeater (collapsible/accordion/title/dynamic title). Group (force group)
// TODO: Fix bug. Cant save repeater field with null value in any of the indexes.

// React meta values component (single, group, repeater)
// Generate posts meta boxes using react with wordpress hooks
// Hackly add metaboxes to other places (terms, customizer, menues, attachemnts, etc)

define("RB_DEVELOPER_PLUGIN_PATH", plugin_dir_path(__FILE__));
define("RB_DEVELOPER_PLUGIN_INC", plugin_dir_path(__FILE__) . "inc");
define("RB_DEVELOPER_PLUGIN_CLASSES", plugin_dir_path(__FILE__) . "inc/classes");
define("RB_DEVELOPER_PLUGIN_TRAITS", plugin_dir_path(__FILE__) . "inc/traits");
define("RB_DEVELOPER_PLUGIN_DIST_SCRIPTS", plugin_dir_url(__FILE__) . "js/dist/scripts");

require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Filters_Manager.php" );
require_once( RB_DEVELOPER_PLUGIN_INC . "/functions.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Custom_Fields.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Post_Meta_Fields_Manager.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Term_Meta_Fields_Manager.php" );

// Columns
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Objects_List_Column.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Posts_List_Column.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Terms_List_Column.php" );


RB_Post_Meta_Fields_Manager::init();
RB_Term_Meta_Fields_Manager::init();
require_once( RB_DEVELOPER_PLUGIN_PATH . "/tests/post-meta-fields.php" );
require_once( RB_DEVELOPER_PLUGIN_PATH . "/tests/term-meta-fields.php" );
