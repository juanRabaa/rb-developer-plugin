<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Fields_Manager.php" );

/**
*   Functionalities that are used by managers of wordpress object fields.
*/
trait RB_Object_Type_Fields_Manager{
    use Initializer;
    static protected $kind_fields_manager = array();

    /**
    *   @property RB_Fields_Manager $fields_generator                           This instance of RB_Fields_Manager is used
    *                                                                           only to generate the field config, and avoid generating
    *                                                                           it for every field kind, since it doesn't vary between them
    */
    static protected $fields_generator = null;

    static protected function on_init(){
        // This RB_
        self::$fields_generator = self::generate_fields_manager();
        // should hook to action?
        add_action('wp_loaded', array(self::class, "generate_kinds_fields_manager"));
        add_action( 'rest_api_init', array(self::class, "register_rest_routes"));
    }

    abstract static public function get_object_type(); // post, term...

    abstract static public function get_object_subtype(); // post_type, taxonomy...

    abstract static public function get_default_object_subtype();

    abstract static public function get_kinds(); // get_post_types

    static public function register_rest_routes(){
        $object_subtype = self::get_object_subtype();
        if($object_subtype){
            register_rest_route( "rb-fields/v1", "/wp-object/$object_subtype/(?P<kind>.+)", array(
                'methods'   => 'GET',
                'callback'  => function ( $data ) {
                    return self::get_kind_fields($data["kind"]);
                },
                'permission_callback'   => "__return_true", // TODO: check permissions
            ) );
        }
    }

    static public function filter_field_config($field_config){
        $object_subtype = self::get_object_subtype();
        $default_args = array(
            $object_subtype         => self::get_default_object_subtype(),
            "register"              => true,
            "single"                => true,
        );
        $field_config = array_merge($default_args, $field_config);

        if(self::field_exists($field_config["meta_key"], $field_config[$object_subtype]))
            wp_die( "A field with the name <b>{$field_config["meta_key"]}</b> already exists", "Error while registering post meta field" );

        return $field_config;
    }

    static protected function generate_fields_manager(){
        return new RB_Fields_Manager(array(
            "filter_field_config"   => array(self::class, "filter_field_config"),
        ));
    }

    // REVIEW: Is this needed? cant the field manager be created on the fly when adding a new field
    // without knowing if the subtype kind actually exists?
    static public function generate_kinds_fields_manager(){
        $kinds = self::get_kinds();
        $object_type = self::get_object_type();
        foreach ($kinds as $kind) {
            self::$kind_fields_manager[$kind] = self::generate_fields_manager();
        }
        do_action("rb-fields-object-{$object_type}-ready");
    }

    static protected function get_kind_fields_manager($kind_name){
        return self::$kind_fields_manager[$kind_name];
    }

    static public function get_kind_fields($kind_name){
        return self::get_kind_fields_manager($kind_name)?->get_registered_fields() ?? [];
    }

    static public function get_kind_field($kind_name, $meta_key){
        return self::get_kind_fields($kind_name)[$meta_key] ?? null;
    }

    /**
    *   Return whether a field with the meta_key exists in any of the provided kinds
    *   @param string|string[] $kinds
    *   @param string $meta_key
    *   @return bool
    */
    static public function field_exists($meta_key, $kinds){
        $kinds = is_array($kinds) ? $kinds : [$kinds];
        foreach ($kinds as $kind_name) {
            if( self::get_kind_field($kind_name, $meta_key) )
                return true;
        }
        return false;
    }

    static public function add_field($field_args){
        $field_subtype_kinds = $field_args[self::get_object_subtype()] ?? [];

        // TODO: use default object subtype kind
        if(!$field_subtype_kinds)
            return;
        $field_subtype_kinds = is_array($field_subtype_kinds) ? $field_subtype_kinds : [$field_subtype_kinds];
        $field_data = self::$fields_generator->generate_field_data($field_args);

        foreach ($field_subtype_kinds as $kind) {
            self::add_field_to_kind($field_data, $kind);
        }

        return $field_data;
    }

    /**
    *   Create a field to modify meta values on the post edition screen.
    *   For the register_meta fields, see https://developer.wordpress.org/reference/functions/register_meta/
    *   The rest are explained bellow.
    *   @param string meta_key                                                  Meta key which value will be controlled by this field. It is required.
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
    static public function add_field_to_kind($field_data, $kind){
        $kind_fields_manager = self::get_kind_fields_manager($kind);
        if(!$kind_fields_manager || !$field_data)
            return;
        $kind_fields_manager->register_field($field_data["field_config"]);

        if($field_data){
            extract($field_data); //field_config, field_schema

            if(!$field_config["register"])
                return $field_data;

            $object_subtype = self::get_object_subtype();
            // The object subtypes the field is releated to (page, post, post_tag, etc) as an array
            $object_subtypes_vals = is_array($field_config[$object_subtype]) ? $field_config[$object_subtype] : [$field_config[$object_subtype]];

            foreach ($object_subtypes_vals as $object_subtype_val) {
                register_meta(
                    self::get_object_type(),
                    $field_config["meta_key"],
                    array(
                        // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
                        "object_subtype"        => $object_subtype_val,
                        'single'                => $field_config["single"],
                        'type'                  => $field_schema['type'],
                        'show_in_rest'          => array(
                            'schema'    => $field_schema,
                        ),
                    ),
                );
            }
        }

        return $field_data;
    }
}
