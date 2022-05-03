<?php

/**
*   Renders the field placeholder in every place a term needs it to be
*/
class RB_Term_Meta_Field{
    protected $field_config;

    public function __construct($field_config){
        $this->field_config = $field_config;
        $this->taxonomies = is_array($this->field_config["taxonomy"]) ? $this->field_config["taxonomy"] : [$this->field_config["taxonomy"]];
        $this->add_edit_form_field();
        $this->add_creation_form_field();
    }

    protected function add_edit_form_field(){
        foreach( $this->taxonomies as $taxonomy_slug ){
            add_action( "${taxonomy_slug}_edit_form_fields", array($this, "render_edit_form_row"));
        }
    }

    protected function add_creation_form_field(){
        foreach( $this->taxonomies as $taxonomy_slug ){
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
