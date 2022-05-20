<?php

/**
*   Renders the field placeholder in every place a post needs it to be
*   Manages the specific proccesses for a specific field
*/
class RB_User_Meta_Field{
    use RB_WP_Object_Field;
    protected $field_config;

    public function __construct($field_config){
        $this->field_config = $field_config;

        $this->setup_wp_object_field( array(
            "object_type"       => "user",
            "object_subtype"    => "user",
        ));

        add_action( 'show_user_profile', array($this, "render_metabox") );
        add_action( 'edit_user_profile', array($this, "render_metabox") );
    }

    // TODO: Implement user meta field column
    protected function setup_list_column(){
        // $column = $this->get_column_config();
        // if($column){
        //     rb_add_posts_list_column($this->get_meta_key(), $this->subtype_kinds, $column["title"], array($this, "render_field_column_content"), array(
        //         "position"  => $column["position"],
        //     ));
        // }
    }

    public function get_object_id($user){
        return $user->ID ?? null;
    }

    public function render_metabox($user){
        $meta_val = get_the_author_meta(  $this->field_config["meta_key"], $user->ID );
        $title = $this->field_config["panel"]["title"] ?? "";
        ?>
        <table class="form-table">
            <tr>
                <th><label for="<?php echo esc_attr($this->field_config["meta_key"]); ?>"><?php _e($title); ?></label></th>
                <td>
                    <div class="rb-metabox-placeholder">
                        <div id="rb-user-field-placeholder__<?php echo esc_attr($this->field_config["meta_key"]); ?>" data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>">
                            <p><span class="spinner is-active"></span>Loading</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }
}
