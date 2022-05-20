<?php

require_once( plugin_dir_path(__FILE__) . "/RB_Fields_Manager.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_WP_Object_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Object_Type_Fields_Manager.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_Menu_Item_Field.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_Post_Meta_Fields_Manager.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_Post_Meta_Field.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_Term_Meta_Fields_Manager.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_Term_Meta_Field.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_User_Meta_Fields_Manager.php" );
require_once( plugin_dir_path(__FILE__) . "/RB_User_Meta_Field.php" );

add_action( "customize_register", function( $wp_customize ) {
	require_once( plugin_dir_path(__FILE__) . "/RB_Field_Customizer_Control.php" );
});
