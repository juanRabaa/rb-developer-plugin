<?php

/**
*   Renders the field on the a menu item in the nav-menus screen.
*/
class RB_Menu_Item_Field{
    protected $field_config;
    /**
    *   If null, its added to every menu item
    */
    protected $object_kind = null;

    public function __construct($field_config){
        $this->field_config = $field_config;
        $this->object_kind = $field_config["object_kind"] ?? null;
        add_action( 'wp_nav_menu_item_custom_fields', array($this, 'render_field'), 10, 4 );
        /* The save proccess happens in the RB_Post_Meta_Fields_Manager */
    }

    public function get_object_kinds(){
        if(!isset($this->object_kind))
            return null;
        if(is_array($this->object_kind))
            return $this->object_kind;
        if(is_string($this->object_kind))
            return array( $this->object_kind );
        return null;
    }

    public function get_meta_key(){
        return $this->field_config["meta_key"];
    }

    /* Renders the metabox */
    public function render_field($item_id, $item, $depth, $args){
        if(!$this->is_on_object_kind($item->object))
            return false;
        $meta_val = get_post_meta($item->ID, $this->get_meta_key(), true);
        // pre_print($item->object);
        ?>
        <div class="widefat rb-metabox-placeholder">
            <div data-field="rb-menu-item-field__<?php echo esc_attr($this->get_meta_key()); ?>" data-itemid=<?php echo esc_attr($item_id); ?> data-value="<?php echo esc_attr( json_encode($meta_val) ); ?>">
                <p><span class="spinner is-active"></span>Loading</p>
            </div>
        </div>
        <?php
    }

    /**
    *   Returns if the current menu item is of any of the types wanted.
    *   @param string $item_object                                              An object type from a menu item
    */
    public function is_on_object_kind($item_object){
        $object_kinds = $this->get_object_kinds();
        if(!$object_kinds)
            return true;

        foreach($object_kinds as $object_kind){
            if($item_object == $object_kind)
                return true;
        }

        return false;
    }

}
