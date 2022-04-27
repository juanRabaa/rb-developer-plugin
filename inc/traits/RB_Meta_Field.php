<?php

require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Meta_Fields_Manager.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

trait RB_Meta_Field {
    use Initializer;
    static protected $fields_manager;

    abstract static protected function get_field_manager_config();

    protected static function generate_fields_manager(){
        self::$fields_manager = new RB_Meta_Fields_Manager( self::get_field_manager_config() );
    }

    static public function get_registered_fields(){
        return self::$fields_manager->get_registered_meta_fields();
    }

    static public function add_field($args){
        return self::$fields_manager->generate_field($args);
    }
}

// add_action( 'init', function (){
//     er();
// }, 10000);
