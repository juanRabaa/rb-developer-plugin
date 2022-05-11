<?php
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Post_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Object_Type_Fields_Manager.php" );

/**
*   Enqueues the needed scripts and proccess the meta values on post save
*   Manages the general proccesses for all the fields
*/
class RB_Post_Meta_Fields_Manager {
    use RB_Object_Type_Fields_Manager {
        on_init as base_on_init;
        add_field as base_add_field;
        filter_field_config as base_filter_field_config;
    }

    static protected function on_init(){
        self::base_on_init();

        // Only on screen with gutenberg editor
        add_action( 'admin_enqueue_scripts', array(self::class, "enqueue_scripts") );
        self::manage_regular_post_update();
        self::manage_attachment();
    }

    static public function get_object_type(){
        return "post";
    }

    static public function get_object_subtype(){
        return "post_type";
    }

    static public function get_default_object_subtype(){
        return "post";
    }

    static public function get_kinds(){
        return get_post_types();
    }

    static public function filter_field_config($field_config){
        $field_config = self::base_filter_field_config($field_config);
        $default_args = array(
            "panel"                 => array(),
        );

        $panel_args = array(
            "position"  => "document-settings-panel",
            "title"     => "Meta",
            "icon"      => "plugins"
        );

        $field_config = array_merge($default_args, $field_config);
        $field_config['panel']  = array_merge($panel_args, $field_config["panel"]);

        return $field_config;
    }

    static public function add_field($field_args){
        $field_data = self::base_add_field($field_args);
        extract($field_data); //$field_config, $field_schema
        if($field_config)
            new RB_Post_Meta_Field($field_config);

        return $field_config;
    }

    // only post
    static protected function manage_regular_post_update(){
        add_action( 'save_post', array(self::class, "save_metas_on_regular_post_update"), 10, 3 );
    }

    // only post
    static public function save_metas_on_regular_post_update($post_ID, $post, $update ){
        self::save_post_metas($post_ID, $post, $_POST);
    }

    // only post
    /**
    *   attachment may look like a post_type, but it doesnt use the `save_post`
    *   action. Instead we need to hook the meta update logic in the `attachment_updated`
    *   and the `add_attachment` actions.
    */
    static protected function manage_attachment(){
        add_action( "attachment_updated", array(self::class, "save_metas_on_attachment_update"), 10, 3 );
        add_action( "add_attachment", array(self::class, "save_metas_on_attachment_creation"), 10, 3 );
        add_filter( "wp_prepare_attachment_for_js", array(self::class, "add_attachment_fields_data"), null, 3);
        // enqueue media popup field scripts only when the media script is enqueued
        add_action( "wp_enqueue_media", array(self::class, "enqueue_media_popup_scripts"), null, 0 );
    }

    // only post
    static public function enqueue_media_popup_scripts(){
        wp_enqueue_script( "rb-media-popup-fields", RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-media-popup-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
    }

    // only post
    static public function add_attachment_fields_data($response, $attachment, $meta){
        $placeholder = "";
        $attachment_fields = self::get_kind_fields_manager("attachment")?->get_registered_fields() ?? [];
        $values = array();

        foreach ($attachment_fields as $meta_key => $field_config) {
            $meta_val = get_post_meta($attachment->ID, $meta_key, true);
            $values[$meta_key] = $meta_val;
            ob_start();
            ?>
            <div id="rb-media-field-placeholder__<?php echo esc_attr($meta_key); ?>">
                <p><span class="spinner is-active"></span>Loading</p>
            </div>
            <?php
            $placeholder .= ob_get_clean();
        }

        $response["rbfields"] = array(
            "fields"        => $attachment_fields,
            "placeholder"   => $placeholder,
            "values"        => $values,
        );

        return $response;
    }

    // only post
    static public function save_metas_on_attachment_update($post_ID, $post_before, $post_after){
        self::save_post_metas($post_ID, $post_after, $_POST);
    }

    // only post
    static public function save_metas_on_attachment_creation($post_ID){
        self::save_post_metas($post_ID, null, $_POST);
    }

    // only post
    static public function save_post_metas($post_ID, $post = null, $values = null){
        // The only hook available after the post is succesfully inserted in the
        // db in the `wp_insert_post` function doesn't receive the $data used
        // in the creation, so we pass as $args to `rb_update_post_meta` the $_POST
        // variable that contains every field in the form, including custom
        // fields.
        $values = $values ?? $_POST;
        $post = $post ?? get_post($post_ID);
        rb_update_post_meta($post_ID, $post->post_type, $values);
    }

    // only post
    static public function enqueue_scripts($hook){
        if ( $hook !== "post.php" && $hook !== "post-new.php" )
                return;

        wp_enqueue_media();

        if(get_current_screen()->is_block_editor()){
            wp_enqueue_script( 'rb-post-meta-fields', RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-post-meta-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
        }
        else {
            wp_enqueue_script( 'rb-post-no-editor-fields', RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-post-no-editor-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
            wp_localize_script( 'rb-post-no-editor-fields', "RBPlugin", array(
                "current_post_type"     => get_current_screen()->post_type,
            ));
        }
    }

}
