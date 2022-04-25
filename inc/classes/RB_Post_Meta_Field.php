<?php

// TODO: This should also be able to show controls or information in the post list table
class RB_Post_Meta_Field{
    static private $initialized = false;
    static private $meta_fields = array();

    static public function init(){
        if(self::$initialized)
            return;

        self::$initialized = true;
        self::register_rest_routes();
    }

    static public function get_registered_meta_fields(){
        return self::$meta_fields;
    }

    static private function register_rest_routes(){
        add_action( 'rest_api_init', function () {
            register_rest_route( 'rb/postsMetaFields/v1', '/postType/(?P<post_type>.+)', array(
                'methods'   => 'GET',
                'callback'  => function ( $data ) {
                    return RB_Post_Meta_Field::get_registered_meta_fields()[$data["post_type"]] ?? array();
                },
                'permission_callback'   => "__return_true", // TODO: check permissions
            ) );
        } );
    }

    /**
    *   Check if a field exists in any of the post types passed.
    *   @param string $meta_key                                                 Name of the field to find
    *   @param string|string[] $post_types                                      Post type or array of post type to check for.
    *   @return bool
    */
    static private function field_exists($meta_key, $post_types){
        if(!is_array($post_types)){
            $post_types = [$post_types];
        }

        foreach ($post_types as $post_type) {
            // Save the data to be passed to the script
            if(isset( self::$meta_fields[$post_type][$meta_key] ))
                return true;
        }
    }

    /**
    *   Stores the config used to generate a field into the static variable $meta_fields
    *   @param mixed[] config                                                   See `add_fields` method
    */
    static private function register_field($config){
        $post_types = $config["post_type"];

        if(!is_array($post_types)){
            $post_types = [$post_types];
        }

        foreach ($post_types as $post_type) {
            // Save the data to be passed to the script
            if(!isset(self::$meta_fields[$post_type]))
                self::$meta_fields[$post_type] = [];

            self::$meta_fields[$post_type][$config["meta_key"]] = $config;
        }
    }

    /**
    *   Creates fields to modify meta values on the post edition screen.
    *   @param mixed[] config                                                   See `add_fields` method
    */
    static public function add_field($args){
        // TODO: Throw error if no meta_key is passed
        $default_args = array(
            "meta_key"              => "",
            "post_type"             => "post",
            "register"              => true,
            "single"                => true,
            "panel"                 => array(),
            "fields"                => array(
                "type"                  => "string",
            ),
        );

        $panel_args = array(
            "position"  => "document-settings-panel",
            "title"     => "Meta",
            "icon"      => "plugins"
        );

        $config = array_merge($default_args, $args);
        $config['panel']  = array_merge($panel_args, $config["panel"]);
        $config["fields"]["name"] = $config["meta_key"];

        if(!$config["meta_key"])
            wp_die( "A `meta_key` must be passed though the arguments of `RB_Post_Meta_Field::add_field` method", "Error while registering post meta field" );

        if(self::field_exists($config["meta_key"], $config["post_type"]))
            wp_die( "A field with the name <b>{$config["meta_key"]}</b> already exists", "Error while registering post meta field" );

        add_action( 'init', function () use ($config){
            $schema = RB_Custom_Fields::generate_field_schema($config["fields"]);
            // Schema modifies the meta type according to the type of field it is (repetear => array, group => object)
            $config["type"] = $schema["type"];
            self::register_field($config);

            register_post_meta(
                $config["post_type"],
                $config["meta_key"],
                array(
                    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
                    'single'       => $config["single"],
                    'type'         => $schema['type'],
                    'show_in_rest' => array(
                        'schema' => $schema,
                    ),
                ),
            );
        });
    }
}
