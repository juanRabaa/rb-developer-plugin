<?php
trait RB_WP_Object_Field {
    protected $object_type = "";
    protected $object_subtype = "";
    protected $subtype_kinds = [];
    protected $quick_edit = array();

    abstract protected function setup_list_column();

    /**
    *   Returns based on a WP_Object its id
    *   @param WP_Object $object                                                Some of the possible types could be WP_Post WP_Term, WP_User.
    *   @return int
    */
    abstract public function get_object_id($object);

    /**
    *   Must return a bool that indicates based on the parameters passed from
    *   the quick_edit_custom_box action if we are about to render a quick edit field
    *   for the right object
    *   @see https://developer.wordpress.org/reference/hooks/quick_edit_custom_box/
    *   @return bool
    */
    abstract public function is_own_quick_edit($post_type, $taxonomy);

    public function setup_wp_object_field($args){
        $this->object_type = $args["object_type"];
        $this->object_subtype = $args["object_subtype"];
        $this->subtype_kinds = is_array($this->field_config[$this->object_subtype]) ? $this->field_config[$this->object_subtype] : [$this->field_config[$this->object_subtype]];
        $this->setup_list_column();
        $this->set_quick_edit_config();
        add_action('quick_edit_custom_box', array($this, "render_quick_edit_field"), 10, 3);
    }

    public function get_meta_key(){
        return $this->field_config["meta_key"];
    }

    public function get_value($object_id){
        return get_metadata( $this->object_type, $object_id, $this->get_meta_key(), true );
    }

    public function get_field_config_kinds(){
        $subtype = $this->object_subtype;
        $field_config = $this->field_config;

        if(isset($field_config[$subtype]))
            return is_array($field_config[$subtype]) ? $field_config[$subtype] : [$field_config[$subtype]];
        return [];
    }

    protected function set_quick_edit_config(){
        $this->quick_edit = null;
        if(isset($this->field_config["quick_edit"]) && $this->field_config["quick_edit"]){
            $this->quick_edit = array(
                "hide_column"   => false,
            );

            if(is_array($this->field_config["quick_edit"]))
                $this->quick_edit = array_merge($this->quick_edit, $this->field_config["quick_edit"]);
        }
    }

    public function get_column_config(){
        $config = null;
        if(isset($this->field_config["column"]) && $this->field_config["column"]){
            $config = array(
                "title"             => $this->field_config["panel"]["title"] ?? "",
                "content"           => null,
                "position"          => null,
                "render_callback"   => null,
            );

            if(is_array($this->field_config["column"]))
                $config = array_merge($config, $this->field_config["column"]);
        }
        return $config;
    }

    public function render_field_column_content($column, $wp_object){
        $column = $this->get_column_config();
        $object_id = $this->get_object_id($wp_object);
        $meta_val = $this->get_value($object_id);
        ?>
        <div class="rb-object-col-field"
        data-metakey="<?php echo esc_attr($this->get_meta_key()); ?>"
        data-objectid="<?php echo esc_attr($object_id); ?>"
        data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>"
        >
            <?php
            if(is_callable($column["render_callback"])):
                call_user_func($column["render_callback"], $column, $wp_object, $this);
            else:
            ?>
            <div class="rb-field-col-placeholder">
                <p><span class="spinner is-active"></span><?php echo esc_attr(__("Loading", "rb-development-plugin")); ?></p>
            </div>
            <?php endif;?>
        </div>
        <?php
    }

    public function render_quick_edit_field( $column_name, $post_type, $taxonomy ) {
        if(!$this->quick_edit)
            return;

        if(!self::is_own_quick_edit($post_type, $taxonomy))
            return;

        if($column_name !== $this->field_config["meta_key"])
            return;

        $title = $this->field_config["panel"]["title"] ?? "";
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <div class="inline-edit-group wp-clearfix">
                    <label class="alignleft">
                        <span class="title"><?php echo _e($title); ?></span>
                        <span class="input-text-wrap">
                            <div class="rb-quick-edit-field-placeholder"
                            data-metakey="<?php echo esc_attr($this->get_meta_key()); ?>">
                                <p><span class="spinner is-active"></span>Loading</p>
                            </div>
                        </span>
                    </label>
                </div>
            </div>
        </fieldset>
        <?php
    }
}
