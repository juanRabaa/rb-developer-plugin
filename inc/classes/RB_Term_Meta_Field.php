<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

// TODO: This should also be able to show controls or information in the terms list table
// TODO: Check why MediaUploadCheck doesnt allow the RBAttachmentControl to work in term form, as if the user had no media permissions
class RB_Term_Meta_Field{
    use Initializer;
    use RB_Meta_Field {
        add_field as base_add_field;
    }

    static protected function on_init(){
        self::generate_fields_manager();
        add_filter( 'wp_update_term_data', function($data, $term_id, $taxonomy, $args){
            $meta_fields = self::get_registered_fields()[$taxonomy] ?? [];
            $term_meta_manager = new WP_REST_Terms_Controller($taxonomy);
            $meta = new WP_REST_Term_Meta_Fields( $taxonomy );
            $schema = $term_meta_manager->get_item_schema();
            // $meta_schema = $schema["properties"]["meta"]["properties"];

            $meta_args = array_filter($args, function($input_name) use ($meta_fields){
                return isset($meta_fields[$input_name]);
            }, ARRAY_FILTER_USE_KEY);
            $meta_args = array_map(function($meta_value){
                return json_decode(wp_unslash($meta_value), true);
            }, $meta_args);

            // echo "<pre>";
            // var_dump($meta_args);
            // echo "</pre>";
            // er();

    		if ( ! empty( $schema['properties']['meta'] ) && isset( $meta_args ) ) {
    			$meta_update = $meta->update_value( $meta_args, $term_id );

    			if ( is_wp_error( $meta_update ) ) {
    				// return $meta_update;
    			}
    		}

            // $term_meta_manager->update_item(array(
            //     "id"    => $term_id,
            //     "meta"  => $meta_args,
            // ));

            return $data;
        }, 10, 4 );
    }

    static protected function get_field_manager_config(){
        return array(
            "object_type"                  => "term",
            "object_subtype"               => "taxonomy",
            "default_object_subtype"       => "category",
            "rest_vars"                    => array(
                "namespace"             => "termsMetaFields",
                "object_subtype"        => "taxonomy",
            ),
            // "filter_field_config"       => array(self::class, "filter_field_config"),
        );
    }

    // TODO: should all this methods go in a class that receives the field_config
    // as construct param? there is a lot of referencing to $field_config in all
    // this methods. Maybe this class should be called RB_Term_Meta_Fields_Manager
    // and another class RB_Term_Meta_Field should manage this methods relative
    // to the actual field_config.


    static public function get_taxonomies($field_config){
        return is_array($field_config["taxonomy"]) ? $field_config["taxonomy"] : [$field_config["taxonomy"]];
    }

    static public function add_field($args){
        $field_config = self::base_add_field($args);
        self::add_edit_form($field_config);
    }

    static protected function add_edit_form($field_config){
        $taxonomies = self::get_taxonomies($field_config);
        foreach( $taxonomies as $taxonomy_slug ){
            add_action( "${taxonomy_slug}_edit_form_fields", function($term) use($field_config){
                self::render_edit_form_row($term, $field_config);
            });
        }
    }

    static protected function render_edit_form_row($term, $field_config){
        $title = $field_config["panel"]["title"] ?? "";
        $meta_val = get_term_meta($term->term_id, $field_config["meta_key"], true);

        ?>
        <tr class="form-field rb-tax-form-field">
            <th scope="row" valign="top"><label for="meta_key"><?php echo $title; ?></label></th>
            <td>
                <div id="rb-field-placeholder__<?php echo esc_attr($field_config["meta_key"]); ?>" data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>">
                    Loading...
                </div>
            </td>
        </tr>
        <?php
    }
}
