<?php

require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

trait RB_Meta_Fields {
    use Initializer;

    static protected $meta_fields = array();
    static protected $object_type = ""; //post, term, etc
    static protected $object_subtype = ""; //post_type, taxonomy, etc
    static protected $default_object_subtype = ""; // post, porfolio, etc
    static protected $rest_vars = array(
        "namespace"             => "",
        "object_subtype"        => "",
    );

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
    abstract static protected function set_object_type_field_props();

    static protected function on_init(){
        self::set_object_type_field_props();
        self::register_rest_routes();
    }

    static public function get_registered_meta_fields(){
        return self::$meta_fields;
    }

    static protected function register_rest_routes(){
        if(!self::$rest_vars || !isset(self::$rest_vars["namespace"]))
            return;

        $namespace = self::$rest_vars["namespace"];
        $object_subtype = self::$rest_vars["object_subtype"];

        add_action( 'rest_api_init', function () use ($namespace, $object_subtype){
            if($object_subtype){
                register_rest_route( "rb/$namespace/v1", "/$object_subtype/(?P<object_subtype_val>.+)", array(
                    'methods'   => 'GET',
                    'callback'  => function ( $data ) {
                        return self::get_registered_meta_fields()[$data["object_subtype_val"]] ?? array();
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
    static protected function field_exists($meta_key, $object_subtypes){
        if(!is_array($object_subtypes)){
            $object_subtypes = [$object_subtypes];
        }

        foreach ($object_subtypes as $object_subtype) {
            // Save the data to be passed to the script
            if(isset( self::$meta_fields[$object_subtype][$meta_key] ))
                return true;
        }

        return false;
    }

    /**
    *   Stores the config used to generate a field into the static variable $meta_fields
    *   @param mixed[] config                                                   See `add_fields` method
    */
    static protected function register_field($config){
        $object_subtypes = $config[self::$object_subtype];

        if(!is_array($object_subtypes)){
            $object_subtypes = [$object_subtypes];
        }

        if(self::field_exists($config["meta_key"], $object_subtypes))
            wp_die( "A field with the name <b>{$config["meta_key"]}</b> already exists for the object subtype <b>{$config[self::$object_subtype]}</>.", "Error while registering post meta field" );


        foreach ($object_subtypes as $object_subtype) {
            // Save the data to be passed to the script
            if(!isset(self::$meta_fields[$object_subtype]))
                self::$meta_fields[$object_subtype] = [];

            self::$meta_fields[$object_subtype][$config["meta_key"]] = $config;
        }
    }

    protected function generate_field_config($args){
        $default_args = array(
            self::$object_subtype   => self::$default_object_subtype,
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

        if(self::field_exists($config["meta_key"], $config[self::$object_subtype]))
            wp_die( "A field with the name <b>{$config["meta_key"]}</b> already exists", "Error while registering post meta field" );

        $this->field_config = $config;
    }

    protected function generate_field_schema(){
        $this->field_schema = RB_Custom_Fields::generate_field_schema($this->field_config["field"]);
        $this->field_config["type"] = $this->field_schema["type"];
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
    protected function generate_field($args){
        $this->generate_field_config($args);
        $this->generate_field_schema();

        add_action( 'init', function (){
            self::register_field($this->field_config);

            register_meta(
                self::$object_type,
                $this->field_config["meta_key"],
                array(
                    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
                    "object_subtype"        => $this->field_config[self::$object_subtype],
                    'single'                => $this->field_config["single"],
                    'type'                  => $this->field_schema['type'],
                    'show_in_rest'          => array(
                        'schema'    => $this->field_schema,
                    ),
                ),
            );
        });
    }
}

// add_action( 'init', function (){
//     er();
// }, 10000);
