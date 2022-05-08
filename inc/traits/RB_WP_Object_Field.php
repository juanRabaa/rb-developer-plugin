<?php

require_once( RB_DEVELOPER_PLUGIN_CLASSES . "/RB_Meta_Fields_Manager.php" );

trait RB_WP_Object_Field {
    protected $object_type = "";
    protected $object_subtype = "";
    protected $subtype_kinds = [];
    abstract protected function setup_list_column();
    abstract public function get_object_id($object);

    public function setup_wp_object_field($args){
        $this->object_type = $args["object_type"];
        $this->object_subtype = $args["object_subtype"];
        $this->subtype_kinds = is_array($this->field_config[$this->object_subtype]) ? $this->field_config[$this->object_subtype] : [$this->field_config[$this->object_subtype]];

        $this->setup_list_column();
    }

    public function get_meta_key(){
        return $this->field_config["meta_key"];
    }

    public function get_value($object_id){
        return get_metadata( $this->object_type, $object_id, $this->get_meta_key(), true );
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

            if(is_array($this->field_config["column"])){
                $config = array_merge($config, $this->field_config["column"]);
            }
        }
        return $config;
    }

    public function render_field_column_content($column, $wp_object){
        $column = $this->get_column_config();
        $object_id = $this->get_object_id($wp_object);
        if(is_callable($column["render_callback"])){
            call_user_func($column["render_callback"], $column, $wp_object, $this);
            return;
        }
        ?>
        <div class="rb-field-col-placeholder"
        data-metakey="<?php echo esc_attr($this->get_meta_key()); ?>"
        data-objectid="<?php echo esc_attr($object_id); ?>">
            <p><span class="spinner is-active"></span><?php echo esc_attr(__("Loading", "rb-development-plugin")); ?></p>
        </div>
        <?php
    }
}
