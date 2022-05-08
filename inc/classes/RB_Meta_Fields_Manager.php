<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );
/**
*   Takes a series of arguments to create a fields manager.
*   Generates fields and stores them in $meta_fields
*   Registers the neccessary rest routes
*   The render and data proccesing is not done here, but where an instance of
*   this class is used.
*/
class RB_Meta_Fields_Manager{
    use Initializer;
    static protected $fields_managers = array();
    protected $meta_fields = array();
    protected $object_type = ""; //post, term, etc
    protected $object_subtype = ""; //post_type, taxonomy, etc
    protected $default_object_subtype = ""; // post, porfolio, etc
    protected $rest_vars = array(
        "namespace"             => "",
        "object_subtype"        => "",
    );

    static protected function on_init(){
        // REVIEW: Should the objects_list_scripts enqueue be done here?
        add_action( 'admin_enqueue_scripts', array(self::class, "objects_list_scripts"));
    }

    /**
    *   Stablishes the values for the required static properties. These are:
    *   @property string $object_type                                           The Wordpress object type (post, term, etc)
    *   @property string $object_subtype                                        The object subtype declaration name (post_type, taxonomy, etc)
    *   @property string|string[] $default_object_subtype                       Default object subtype value to use when registering the field
    *                                                                           (for example, if object_subtype is post_type, then post, page, etc)
    *   @property string $rest_vars                                             The REST API is used to fetch the fields registered in this class
    *                                                                           in the React front end. The following variables need to be defined
    *                                                                           in order to create the neccessary routes.
    *   {
    *       @property string namespace                                          "rb/$namespace/v1"
    *       @property string object_subtype                                     "rb/$namespace/v1/$object_subtype/(?P<object_subtype_val>.+)"
    *   }
    */
    public function __construct($config){
        $this->object_type = $config["object_type"]; //post, term, etc
        $this->object_subtype = $config["object_subtype"]; //post_type, taxonomy, etc
        $this->default_object_subtype = $config["default_object_subtype"]; // post, porfolio, etc
        $this->rest_vars = array_merge( $this->rest_vars, $config["rest_vars"] ?? [] );
        $this->filter_field_config = $config["filter_field_config"] ?? null;
        // $this->get_object = $config["get_object"] ?? null;
        // $this->get_object_subtype = $config["get_object_subtype"] ?? null;
        $this->register_rest_routes();
        self::$fields_managers[$config["object_type"]] = $this;
        self::init();
    }

    static public function objects_list_scripts(){
        $current_screen = get_current_screen();
        $object_type = "";
        $object_subtype = "";
        $subtype_kind = "";

        // REVIEW: Is there a way to make it more dynamic?
        if($current_screen->base === "edit"){
            $object_type = "post";
            $object_subtype = "post_type";
            $subtype_kind = $current_screen->post_type;
        }
        else if($current_screen->base === "edit-tags"){
            $object_type = "term";
            $object_subtype = "taxonomy";
            $subtype_kind = $current_screen->taxonomy;
        }

        if($object_subtype){
            // wp_enqueue_media();
            wp_enqueue_script( 'rb-object-list-column-fields', RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-object-list-column-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
            wp_localize_script( "rb-object-list-column-fields", "RBObjectsList", array(
                "objectType"            => $object_type,
                "objectSubtype"         => $object_subtype,
                "subtypeKind"           => $subtype_kind,
            ));
        }
    }

    // TODO: there should be a wrapper around the get_meta function that fetched custom
    // metas created with this functionalities, that way we can return, for example, default
    // values.
    // https://developer.wordpress.org/reference/functions/get_metadata_default/
    // https://developer.wordpress.org/reference/functions/get_metadata/
    // protected function filter_meta_value(){
    //     add_filter( "default_{$this->object_type}_metadata", function($value, $object_id, $meta_key, $single, $meta_type){
    //         if(!is_callable($this->get_object) || !is_callable($this->get_object_subtype))
    //             return;
    //         $object = call_user_func($this->get_object, $object_id);
    //         $object = call_user_func($this->get_object_subtype, $object_id);
    //         return $value;
    //     } );
    // }

    static public function get_metadata($object_type, $object_subtype, $meta_key, $default, $single){
    }

    public function get_registered_meta_fields(){
        return $this->meta_fields;
    }

    public function get_subtype_fields($object_subtype){
        return $this->meta_fields[$object_subtype] ?? [];
    }

    protected function register_rest_routes(){
        if(!$this->rest_vars || !isset($this->rest_vars["namespace"]))
            return;

        $namespace = $this->rest_vars["namespace"];
        $object_subtype = $this->rest_vars["object_subtype"];

        add_action( 'rest_api_init', function () use ($namespace, $object_subtype){
            if($object_subtype){
                register_rest_route( "rb-fields/v1", "/$object_subtype/(?P<object_subtype_val>.+)", array(
                    'methods'   => 'GET',
                    'callback'  => function ( $data ) {
                        return $this->get_registered_meta_fields()[$data["object_subtype_val"]] ?? array();
                    },
                    'permission_callback'   => "__return_true", // TODO: check permissions
                ) );
            }
        } );
    }

    /**
    *   Check if a field exists in any of the post types passed.
    *   @param string $meta_key                                                 Name of the field to find
    *   @param string|string[] $object_subtypes                                 Post type or array of post type to check for.
    *   @return bool
    */
    protected function field_exists($meta_key, $object_subtypes){
        if(!is_array($object_subtypes)){
            $object_subtypes = [$object_subtypes];
        }

        foreach ($object_subtypes as $object_subtype) {
            // Save the data to be passed to the script
            if(isset( $this->meta_fields[$object_subtype][$meta_key] ))
                return true;
        }

        return false;
    }

    /**
    *   Stores the config used to generate a field into the static variable $meta_fields
    *   @param mixed[] config                                                   See `add_fields` method
    */
    protected function register_field($field_config){
        $object_subtypes = $field_config[$this->object_subtype];

        if(!is_array($object_subtypes)){
            $object_subtypes = [$object_subtypes];
        }

        if($this->field_exists($field_config["meta_key"], $object_subtypes))
            wp_die( "A field with the name <b>{$field_config["meta_key"]}</b> already exists for the object subtype <b>{$field_config[$this->object_subtype]}</>.", "Error while registering post meta field" );


        foreach ($object_subtypes as $object_subtype) {
            // Save the data to be passed to the script
            if(!isset($this->meta_fields[$object_subtype]))
                $this->meta_fields[$object_subtype] = [];

            $this->meta_fields[$object_subtype][$field_config["meta_key"]] = $field_config;
        }
    }

    protected function generate_field_config($args){
        $default_args = array(
            $this->object_subtype   => $this->default_object_subtype,
            "meta_key"              => "",
            "register"              => true,
            "single"                => true,
            "field"                => array(
                "type"                  => "string",
            ),
        );

        $config = array_merge($default_args, $args);
        $config["field"]["name"] = $config["meta_key"];
        // $config = $this->parse_object_type_args($config);
        $class_name = static::class;

        if(!$config["meta_key"])
            wp_die( "A `meta_key` must be passed though the arguments of `$class_name::add_field` method", "Error while registering meta field" );

        if($this->field_exists($config["meta_key"], $config[$this->object_subtype]))
            wp_die( "A field with the name <b>{$config["meta_key"]}</b> already exists", "Error while registering post meta field" );

        if(is_callable($this->filter_field_config))
            $config = call_user_func($this->filter_field_config, $config);

        return $config;
    }

    protected function generate_field_schema($field_config){
        return RB_Custom_Fields::generate_field_schema($field_config);
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
    public function generate_field($args){
        $field_config = $this->generate_field_config($args);
        $field_schema = $this->generate_field_schema($field_config["field"]);
        $field_config["type"] = $field_schema["type"];

        $this->register_field($field_config);

        add_action( 'init', function () use ($field_config, $field_schema){

            if(!$field_config["register"])
                return;

            // The object subtypes the field is releated to (page, post, post_tag, etc) as an array
            $object_subtypes_vals = is_array($field_config[$this->object_subtype]) ? $field_config[$this->object_subtype] : [$field_config[$this->object_subtype]];

            foreach ($object_subtypes_vals as $object_subtype_val) {
                register_meta(
                    $this->object_type,
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

        });

        return $field_config;
    }
}
