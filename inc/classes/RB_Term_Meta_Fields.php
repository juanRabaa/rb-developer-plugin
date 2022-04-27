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
        add_filter( 'wp_update_term_data', array(self::class, "update_meta_on_term_update"), 10, 4 );
        add_action( 'created_term', array(self::class, "set_new_term_meta"), 10, 3 );
    }

    static public function enqueue_admin_scripts($hook){
        wp_enqueue_style("wp-components");
        wp_enqueue_style("wp-editor");

        if ( $hook === "term.php" ){
            wp_enqueue_script( "rb-term-edit-form-fields", RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-term-edit-form-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
        }
        else if ( $hook === "edit-tags.php" ){
            wp_enqueue_script( "rb-term-creation-form-fields", RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-term-creation-form-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
        }
    }

    static public function set_new_term_meta($term_id, $tt_id, $taxonomy){
        // The only hook available after the term is succesfully inserted in the
        // db in the `wp_insert_term` function doesn't receive the $data used
        // in the creation, so we pass as $args to `self::update_meta` the $_POST
        // variable that contains every field in the creation form, including custom
        // fields.
        self::update_meta($term_id, $taxonomy, $_POST);
    }

    static public function update_meta_on_term_update($data, $term_id, $taxonomy, $args){
        self::update_meta($term_id, $taxonomy, $args);
        return $data;
    }

    static public function update_meta($term_id, $taxonomy, $args){
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
