<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

// TODO: This should also be able to show controls or information in the post list table
class RB_Post_Meta_Field{
    use Initializer;
    use RB_Meta_Field;

    static protected function on_init(){
        self::generate_fields_manager();
    }

    static protected function get_field_manager_config(){
        return array(
            "object_type"                  => "post",
            "object_subtype"               => "post_type",
            "default_object_subtype"       => "post",
            "rest_vars"                    => array(
                "namespace"             => "postsMetaFields",
                "object_subtype"        => "postType",
            ),
            "filter_field_config"       => array(self::class, "filter_field_config"),
        );
    }

    static public function filter_field_config($field_config){
        $default_args = array(
            "panel"                 => array(),
        );

        $panel_args = array(
            "position"  => "document-settings-panel",
            "title"     => "Meta",
            "icon"      => "plugins"
        );

        $field_config = array_merge($default_args, $field_config);
        $field_config['panel']  = array_merge($panel_args, $field_config["panel"]);

        return $field_config;
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
