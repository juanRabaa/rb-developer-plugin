<?php

/**
*   Enqueues the needed scripts and proccess the meta values on post save
*   Manages the general proccesses for all the fields
*/
class RB_User_Meta_Fields_Manager {
    use RB_Object_Type_Fields_Manager {
        on_init as base_on_init;
        filter_field_config as base_filter_field_config;
    }

    static protected function on_init(){
        self::base_on_init();

        add_action( 'admin_enqueue_scripts', array(self::class, "enqueue_scripts") );
        self::manage_user_update();
    }

    static public function get_object_type(){
        return "user";
    }

    static public function get_object_subtype(){
        return "root";
    }

    static public function get_default_object_subtype(){
        return "user";
    }

    static public function get_kinds(){
        return ["user"];
    }

    static protected function generate_field_instance($field_data){
        extract($field_data);
        return new RB_User_Meta_Field($field_config);
    }

    /**
    *   The user object type doesn't have subtypes, so the kind the only kind that exists
    *   is a fake one called user.
    */
    static protected function get_kind_fields_manager(){
        return self::$kind_fields_manager["user"];
    }

    // TODO: Add filter by roles
    static public function filter_field_config($field_config){
        $field_config = self::base_filter_field_config($field_config);
        $field_config['user'] = "user"; // hardcoded kind
        $default_args = array(
            "panel"                 => array(),
        );

        $panel_args = array(
            // "position"  => "document-settings-panel",
            "title"     => "",
            "icon"      => ""
        );

        $field_config = array_merge($default_args, $field_config);
        $field_config['panel']  = array_merge($panel_args, $field_config["panel"]);

        return $field_config;
    }

    static protected function manage_user_update(){
        add_action( 'personal_options_update', array(self::class, "save_user_metas_on_update"), 10, 1 );
        add_action( 'edit_user_profile_update', array(self::class, "save_user_metas_on_update"), 10, 1 );
    }

    static public function save_user_metas_on_update($user_ID){
        self::save_user_metas($user_ID, $_POST);
    }

    static public function save_user_metas($user_ID, $values = null){
        $values = $values ?? $_POST;
        rb_update_user_meta($user_ID, $values);
    }

    static public function enqueue_scripts($hook){
        if ( $hook !== "user-edit.php" && $hook !== "profile.php" )
                return;

        wp_enqueue_media();
        wp_enqueue_script( 'rb-user-fields', RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-user-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
        wp_localize_script( 'rb-user-fields', "RBUserScript", array(
            "fields"	=> self::get_kind_fields_manager()?->get_registered_fields(),
        ));
    }

}
