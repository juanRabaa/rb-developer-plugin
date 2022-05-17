<?php

class RB_Fields_Manager {
    protected $registered_fields = array();

    /**
    *   Stablishes the values for the required static properties. These are:
    *   @property string $rest_vars                                             The REST API is used to fetch the fields registered in this class
    *                                                                           in the React front end. The following variables need to be defined
    *                                                                           in order to create the neccessary routes.
    *   {
    *       @property string namespace                                          "rb/$namespace/v1"
    *       @property string object_subtype                                     "rb/$namespace/v1/$kind/(?P<object_subtype_val>.+)"
    *   }
    */
    public function __construct($config = array()){
        $this->filter_field_config = $config["filter_field_config"] ?? null;
    }

    public function get_registered_fields(){
        return $this->registered_fields;
    }

    public function get_field($key){
        $field = $this->field_exists($key);
        return $field ? $field : null;
    }

    /**
    *   Check if a field exists
    *   @param string $key                                                 Name of the field to find
    *   @return bool
    */
    protected function field_exists($key){
        return $this->registered_fields[$key] ?? false;
    }

    /**
    *   Stores the config used to generate a field into the static variable $registered_fields
    *   @param mixed[] config                                                   See `add_fields` method
    */
    public function register_field($field_config){
        if($this->field_exists($field_config["meta_key"]))
            wp_die( "A field with the name <b>{$field_config["meta_key"]}</b> already exists.</>.", "Error while registering field" );
        $this->registered_fields[$field_config["meta_key"]] = $field_config;
    }

    protected function generate_field_config($args){
        $default_args = array(
            "meta_key"                   => "",
            "field"                 => array(
                "type"                  => "string",
            ),
        );

        $config = array_merge($default_args, $args);
        $config["field"]["name"] = $config["meta_key"];
        // $config = $this->parse_object_type_args($config);
        $class_name = static::class;

        if(!$config["meta_key"])
            wp_die( "A `meta_key` must be passed though the arguments of `$class_name::add_field` method", "Error while registering meta field" );

        if(is_callable($this->filter_field_config))
            $config = call_user_func($this->filter_field_config, $config);

        return $config;
    }

    protected function generate_schema($field_config){
        return RB_Fields_Manager::generate_field_schema($field_config);
    }

    /**
    *   Create a field to modify meta values on the post edition screen.
    *   For the register_meta fields, see https://developer.wordpress.org/reference/functions/register_meta/
    *   The rest are explained bellow.
    *   @param string meta_key                                                  Meta key which value will be controlled by this field. It is required.
    *   @param mixed[] field                                                    Configuration of the field that manages the mata value.
    *                                                                           See RB_Fields_Manager::generate_field_schema
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
    public function add_field($args){
        $field_data = $this->generate_field_data($args);
        $this->register_field($field_data["field_config"]);
        return $field_data;
    }

    public function generate_field_data($args){
        $field_config = $this->generate_field_config($args);
        $field_schema = $this->generate_schema($field_config["field"]);
        $field_config["type"] = $field_schema["type"];

        return array(
            "field_config"  => $field_config,
            "field_schema"  => $field_schema,
        );
    }

    /**
    *   Automates the schema for groups and repeaters.
    *   Is independent of the object type managed (post, term, customizer, etc).
    */
    static public function generate_field_schema($field_data){

        $common_props = array(
            "label"                     => "",
            "description"               => "",
        );

        $single_field_props = array_merge(
            $common_props,
            array(
                "type"                  => "string",
                // Only if type of field is array (component passed)
                "items"                 => null,
                // Only if type of field is object (component passed)
                "properties"            => null,
                // If type of field is object, stablishes the accepted types of properties
                // not present in the `properties` option (component passed)
                "additionalProperties"  => null,
                // If passed, it behaves as a single field
                "component"             => "",
            ),
        );

        $field_data = array_merge(
            array(
                "name"                  => "",
                "type"                  => "string",
                "label"                 => "",
                "description"           => "",
                // Specify this field as a repeater of the field or fields in the ´fields´ array
                "repeater"              => false,
                // Forces the field to store the value as a JSON. The field needs to have a `name` set
                "force_group"           => false,
                // Only passed if the field is supposed to be a repeater or a group
                "fields"                => null,
            ),
            $common_props,
            $single_field_props,
            $field_data,
        );

        extract($field_data);
        $schema = $item_schema = array();

        if($component){
            // TODO: Es necesario? lo unico que hace es filtrar los keys que no importan, pero ya lo que importa esta guardado en $field_data, se podria pasar directo
            foreach ($single_field_props as $field_key => $field_default) {
                $item_schema[$field_key] = $field_data[$field_key] ?? $field_default;
            }

            if($force_group && $name){
                $field_config = array(
                    "label"             => "",
                    "description"       => "",
                    "fields"            => array($item_schema),
                );
                $item_schema = RB_Fields_Manager::generate_field_schema($field_config);
            }
        }
        else if(is_array($fields)){
            // If there is only one field in the fields array. If the field has the `name` key, it will be forced into a group in the next condition
            if( count($fields) === 1 && !isset($fields[0]["name"]) ){
                $field_config = $fields[0];
                $item_schema = RB_Fields_Manager::generate_field_schema($field_config);
            }
            // If multiple fields, or only one with the `name` key, that forces it to a group
            else if( count($fields) > 1 || ( count($fields) === 1 && isset($fields[0]["name"]) ) ){
                $properties = array();

                foreach ($fields as $field_config) {
                    $properties[$field_config["name"]] = RB_Fields_Manager::generate_field_schema($field_config);
                }

                $item_schema = array(
                    "type"                      => "object",
                    "properties"                => $properties,
                    // "additionalProperties"      => $additionalProperties,
                );
            }
        }


        if($field_data["repeater"]){
            $schema = array(
                "type"                      => "array",
                "items"                     => $item_schema,
            );
        }
        else
            $schema = $item_schema;

        return $schema;
    }
}
