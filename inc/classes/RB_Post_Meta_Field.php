<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Meta_Field.php" );

// TODO: This should also be able to show controls or information in the post list table
class RB_Post_Meta_Field{
    use RB_Meta_Fields;

    static protected function set_post_meta_field_props(){
        self::$object_type = "post";
        self::$object_subtype = "post_type";
        self::$default_object_subtype = "post";
        self::$rest_vars = array(
            "namespace"             => "postsMetaFields",
            "object_subtype"        => "postType",
        );
    }

    static protected function parse_object_type_args($args){
        $default_args = array(
            "panel"                 => array(),
        );

        $panel_args = array(
            "position"  => "document-settings-panel",
            "title"     => "Meta",
            "icon"      => "plugins"
        );

        $config = array_merge($default_args, $args);
        $config['panel']  = array_merge($panel_args, $config["panel"]);

        if(self::field_exists($config["meta_key"], $config["post_type"]))
            wp_die( "A field with the name <b>{$config["meta_key"]}</b> already exists", "Error while registering post meta field" );

        return $config;
    }

    /**
    *   Creates fields to modify meta values on the post edition screen.
    *   For the register_meta fields, see https://developer.wordpress.org/reference/functions/register_meta/
    *   The rest are explained bellow.
    *   @param string meta_key                                                  Meta key which value will be controlled by this field. It is required.
    *   @param string|string[] post_type                                        The post or array of post types that utilises this meta. Defaults to `post`
    *   @param mixed[] field                                                    Configuration of the field that manages the mata value.
    *                                                                           See RB_Custom_Fields::generate_field_schema
    *   @param mixed[] panel                                                    Sets data related to the slotfill component of the metabox in the editor.
    *   {
    *       @param string position                                              Slug that indicates the metabox position.
    *       @param string title                                                 Title for the component that manages the metabox position
    *       @param string icon                                                  Metabox icon
    *   }
    *   @param bool register                                                    Indicates whether to register or not the meta. Defaults to `true`.
    *                                                                           Not registering the meta in this proccess means to not automatically create
    *                                                                           and register the correct schema to support this field data, so make sure
    *                                                                           to have a matching schema for the value this field will store when you
    *                                                                           manually register the meta.
    */
}
