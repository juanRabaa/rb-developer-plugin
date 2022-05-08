<?php
require_once( RB_DEVELOPER_PLUGIN_TRAITS . "/RB_WP_Object_Field.php" );

/**
*   Renders the field placeholder in every place a post needs it to be
*   Manages the specific proccesses for a specific field
*/
class RB_Post_Meta_Field{
    use RB_WP_Object_Field;
    protected $field_config;

    public function __construct($field_config){
        $this->field_config = $field_config;

        $this->setup_wp_object_field( array(
            "object_type"       => "post",
            "object_subtype"    => "post_type",
        ));

        /**
        *   @deprecated
        *   Not used. Attachment metaboxes are added directly from the RB_Post_Meta_Fields_Manager class
        *   This version conflicts with the render of filds in the media popup via the extension of the
        *   wp media backbone api in the rb-media-popup-fields script
        */
        // self::attachment_metaboxes();
        add_action("current_screen", array($this, "on_not_gutenberg") );
    }

    protected function setup_list_column(){
        $column = $this->get_column_config();
        if($column){
            rb_add_posts_list_column($this->get_meta_key(), $this->subtype_kinds, $column["title"], array($this, "render_field_column_content"), array(
                "position"  => $column["position"],
            ));
        }
    }

    public function get_object_id($post){
        return $post->ID ?? null;
    }

    // Meta fields are added directly with the wp api on gutenberg, so on no gutenberg
    // metaboxes must be added the classic way (add_meta_box)
    public function on_not_gutenberg(){
        if(!get_current_screen()->is_block_editor()){
            add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        }
    }

    /**
    *   @deprecated
    */
    protected function attachment_metaboxes(){
        if(!in_array("attachment", $this->subtype_kinds))
            return;
        add_filter('attachment_fields_to_edit', array($this, "add_media_popup_metaboxes"), null, 2);
    }

    /**
    *   @deprecated
    */
    public function add_media_popup_metaboxes( $form_fields, $post ){
        $meta_value = get_post_meta($post->ID, $this->field_config["meta_key"], true);
        $form_fields[$this->field_config["meta_key"]] = array(
            'label' => 'Custom text field',
            'input' => 'text', // you may alos use 'textarea' field
            'value' => $meta_value,
            'helps' => 'This is help text',
            'extra_rows'    => array(),
            'show_in_edit'  => true,
            'show_in_modal' => false,
        );
        return $form_fields;
        // if( get_current_screen()->is_block_editor() )
    }

    public function add_metabox(){
        $title = $this->field_config["panel"]["title"] ?? "";
        $context = $this->field_config["panel"]["context"] ?? "advanced";

        foreach( $this->subtype_kinds as $post_type ){
            add_meta_box( "{$this->field_config["meta_key"]}-metabox__$post_type", $title, array($this, "render_metabox"), $post_type, $context );
        }
    }

    public function render_metabox($post){
        $meta_val = get_post_meta($post->ID, $this->field_config["meta_key"], true);
        ?>
        <div class="rb-metabox-placeholder">
            <div id="rb-field-placeholder__<?php echo esc_attr($this->field_config["meta_key"]); ?>" data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>">
                <p><span class="spinner is-active"></span>Loading</p>
            </div>
        </div>
        <?php
    }
}
