<?php

/**
*   Enqueues the needed scripts and proccess the meta values on term save
*/
class RB_Term_Meta_Fields_Manager{
    use RB_Object_Type_Fields_Manager {
        on_init as base_on_init;
    }

    static protected function on_init(){
        self::base_on_init();
        add_action( 'admin_enqueue_scripts', array(self::class, "enqueue_admin_scripts") );
        add_filter( 'wp_update_term_data', array(self::class, "update_meta_on_term_update"), 10, 4 );
        add_action( 'created_term', array(self::class, "set_new_term_meta"), 10, 3 );
    }

    static public function get_object_type(){
        return "term";
    }

    static public function get_object_subtype(){
        return "taxonomy";
    }

    static public function get_default_object_subtype(){
        return "taxonomy";
    }

    static public function get_kinds(){
        return get_taxonomies();
    }

    static protected function generate_field_instance($field_data){
        return new RB_Term_Meta_Field($field_data["field_config"]);
    }

    static public function enqueue_admin_scripts($hook){
        wp_enqueue_style("wp-components");
        wp_enqueue_style("wp-editor");
        wp_enqueue_media();

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

    static public function update_meta($term_id, $taxonomy, $values){
        rb_update_term_meta($term_id, $taxonomy, $values);
    }

}
