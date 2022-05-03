<?php

/**
*   Renders the field placeholder in every place a post needs it to be
*/
class RB_Post_Meta_Field{
    protected $field_config;

    public function __construct($field_config){
        $this->field_config = $field_config;
        $this->post_types = is_array($this->field_config["post_type"]) ? $this->field_config["post_type"] : [$this->field_config["post_type"]];

        add_action("current_screen", array($this, "on_not_gutenberg") );
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

        foreach( $this->post_types as $post_type ){
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
