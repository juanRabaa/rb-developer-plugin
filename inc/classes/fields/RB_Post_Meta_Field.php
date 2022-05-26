<?php

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

    public function is_own_quick_edit($post_type, $taxonomy){
        $field_kinds = $this->get_field_config_kinds();
        if(!empty($field_kinds) && !in_array($post_type, $field_kinds))
            return false;
        return true;
    }

    // Meta fields are added directly with the wp api on gutenberg, so on no gutenberg
    // metaboxes must be added the classic way (add_meta_box)
    public function on_not_gutenberg(){
        if(!get_current_screen()->is_block_editor()){
            add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        }
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
