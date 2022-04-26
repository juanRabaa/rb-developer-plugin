<?php


class RB_Custom_Fields{
    static private $initialized = false;

    static public function init(){
        if(self::$initialized)
            return;

        self::$initialized = true;
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
                $item_schema = RB_Custom_Fields::generate_field_schema($field_config);
            }
        }
        else if(is_array($fields)){
            // If there is only one field in the fields array. If the field has the `name` key, it will be forced into a group in the next condition
            if( count($fields) === 1 && !isset($fields[0]["name"]) ){
                $field_config = $fields[0];
                $item_schema = RB_Custom_Fields::generate_field_schema($field_config);
            }
            // If multiple fields, or only one with the `name` key, that forces it to a group
            else if( count($fields) > 1 || ( count($fields) === 1 && isset($fields[0]["name"]) ) ){
                $properties = array();

                foreach ($fields as $field_config) {
                    $properties[$field_config["name"]] = RB_Custom_Fields::generate_field_schema($field_config);
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
