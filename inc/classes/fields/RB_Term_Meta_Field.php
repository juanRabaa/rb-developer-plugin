<?php

/**
*   Renders the field placeholder in every place a term needs it to be
*/
class RB_Term_Meta_Field{
    use RB_WP_Object_Field;
    protected $field_config;

    public function __construct($field_config){
        $this->field_config = $field_config;
        $this->setup_wp_object_field( array(
            "object_type"       => "term",
            "object_subtype"    => "taxonomy",
        ));
        $this->add_edit_form_field();
        $this->add_creation_form_field();
    }

    public function get_object_id($term){
        return $term->term_id ?? null;
    }

    public function is_own_quick_edit($post_type, $taxonomy){
        $field_kinds = $this->get_field_config_kinds();
        if(!empty($field_kinds) && !in_array($taxonomy, $field_kinds))
            return false;
        return true;
    }

    protected function setup_list_column(){
        $column = $this->get_column_config();
        if($column){
            rb_add_terms_list_column($this->get_meta_key(), $this->subtype_kinds, $column["title"], array($this, "render_field_column_content"), array(
                "position"  => $column["position"],
            ));
        }
    }

    protected function add_edit_form_field(){
        foreach( $this->subtype_kinds as $taxonomy_slug ){
            add_action( "${taxonomy_slug}_edit_form_fields", array($this, "render_edit_form_row"));
        }
    }

    protected function add_creation_form_field(){
        foreach( $this->subtype_kinds as $taxonomy_slug ){
            add_action( "${taxonomy_slug}_add_form_fields", array($this, "render_add_form_row"));
        }
    }

    public function render_edit_form_row($term){
        $title = $this->field_config["panel"]["title"] ?? "";
        $meta_val = get_term_meta($term->term_id, $this->field_config["meta_key"], true);
        ?>
        <tr class="form-field rb-tax-form-field">
            <th scope="row" valign="top"><label for="meta_key"><?php echo $title; ?></label></th>
            <td>
                <div id="rb-field-placeholder__<?php echo esc_attr($this->field_config["meta_key"]); ?>" data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>">
                    <p><span class="spinner is-active"></span>Loading</p>
                </div>
            </td>
        </tr>
        <?php
    }

    public function render_add_form_row($taxonomy){
        $title = $this->field_config["panel"]["title"] ?? "";
        ?>
        <div class="form-field rb-tax-form-field">
            <label for="tag-description"><?php echo $title; ?></label>
            <div id="rb-tax-field-placeholder__<?php echo esc_attr($this->field_config["meta_key"]); ?>">
                <p><span class="spinner is-active"></span>Loading</p>
            </div>
        </div>
        <?php
    }
}
