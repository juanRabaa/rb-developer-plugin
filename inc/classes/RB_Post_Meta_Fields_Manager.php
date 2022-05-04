<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Post_Meta_Field.php" );
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/Initializer.php" );

// TODO: This should also be able to show controls or information in the post list table
/**
*   Enqueues the needed scripts and proccess the meta values on post save
*/
class RB_Post_Meta_Fields_Manager{
    use Initializer;
    use RB_Meta_Field {
        add_field as base_add_field;
    }

    static protected function on_init(){
        self::generate_fields_manager();

        // Only on screen with gutenberg editor
        add_action( 'admin_enqueue_scripts', array(self::class, "enqueue_scripts") );
        self::manage_regular_post_update();
        self::manage_attachment();
    }

    static protected function manage_regular_post_update(){
        add_action( 'save_post', array(self::class, "save_post_metas"), 10, 3 );
    }

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

    static public function enqueue_media_popup_scripts(){
        wp_enqueue_script( "rb-media-popup-fields", RB_DEVELOPER_PLUGIN_DIST_SCRIPTS . "/rb-media-popup-fields/index.min.js", ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-plugins', 'wp-edit-post'], false );
    }

    static public function add_attachment_fields_data($response, $attachment, $meta){
        $placeholder = "";
        $attachment_fields = self::$fields_manager->get_subtype_fields("attachment");
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

    static public function save_metas_on_attachment_update($post_ID, $post_before, $post_after){
        self::save_post_metas($post_ID, $post_after, $_POST);
    }

    static public function save_metas_on_attachment_creation($post_ID){
        self::save_post_metas($post_ID, null, $_POST);
    }

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

    static protected function get_field_manager_config(){
        return array(
            "object_type"                  => "post",
            "object_subtype"               => "post_type",
            "default_object_subtype"       => "post",
            "rest_vars"                    => array(
                "namespace"             => "postsMetaFields",
                "object_subtype"        => "postType",
            ),
            "filter_field_config"       => array(self::class, "filter_field_config"),
        );
    }

    static public function filter_field_config($field_config){
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

    static public function add_field($args){
        $field_config = self::base_add_field($args);
        $field = new RB_Post_Meta_Field($field_config);
    }

    /**
    *   Creates fields to modify meta values on the post edition screen.
    *   For the register_meta fields, see https://developer.wordpress.org/reference/functions/register_meta/
    *   The rest are explained bellow.
    *   @param string meta_key                                                  Meta key which value will be controlled by this field. It is required.
    *   @param string|string[] post_type                                        The post or array of post types that utilises this meta. Defaults to `post`
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
}
