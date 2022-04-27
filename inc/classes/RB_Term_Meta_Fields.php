<?php
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Term_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

// TODO: This should also be able to show controls or information in the terms list table
// TODO: Check why MediaUploadCheck doesnt allow the RBAttachmentControl to work in term form, as if the user had no media permissions
class RB_Term_Meta_Fields{
    use Initializer;
    use RB_Meta_Field {
        add_field as base_add_field;
    }

    static protected function on_init(){
        self::generate_fields_manager();

        add_action( 'admin_enqueue_scripts', array(self::class, "enqueue_admin_scripts") );
        add_filter( 'wp_update_term_data', array(self::class, "save_term_metas"), 10, 4 );
    }

    static public function enqueue_admin_scripts($hook){
        if ( $hook !== "term.php" )
                return;
        wp_enqueue_script( "rb-term-edit-form-fields", RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-term-edit-form-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
    	wp_enqueue_style("wp-components");
    	wp_enqueue_style("wp-editor");
    }

    static public function save_term_metas($data, $term_id, $taxonomy, $args){
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

        if ( ! empty( $schema['properties']['meta'] ) && isset( $meta_args ) ) {
            $meta_update = $meta->update_value( $meta_args, $term_id );

            if ( is_wp_error( $meta_update ) ) {
                // return $meta_update;
            }
        }

        return $data;
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

    static public function add_field($args){
        $field_config = self::base_add_field($args);
        $field = new RB_Term_Meta_Field($field_config);
    }
}
