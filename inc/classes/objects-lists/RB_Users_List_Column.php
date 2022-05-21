<?php

class RB_Users_List_Column extends RB_Objects_List_Column{
    protected $capabilities = [];
    protected $roles = [];

    public function __construct($id, $title, $render_callback, $args = array()) {
        parent::__construct($id, array("users"), $title, $render_callback, $args);
        $this->capabilities = $args["capabilities"] ?? [];
        $this->roles = $args["roles"] ?? [];
    }

    static protected function manage_column_base_filter_tag($admin_page = null){
        return "manage_users_columns";
    }

    static protected function manage_column_content_filter_tag($admin_page = null){
        return "manage_users_custom_column";
    }

    /**
    *   Sets up the column to show on the posts list.
    */
    protected function setup_screen_column($admin_page){
        RB_Filters_Manager::add_filter( "rb_user-{$this->id}-column_base", self::manage_column_base_filter_tag(), array($this, 'add_column_base') );
        RB_Filters_Manager::add_filter( "rb_user-{$this->id}-column_content", self::manage_column_content_filter_tag(), array($this, 'add_column_content'), array(
            'accepted_args'  => 3,
        ));
    }

    public function should_show_content($column, $user){
        if( !rb_user_has_capabilities($user, $this->capabilities) || !rb_user_has_any_role($user, $this->roles) )
            return false;
        return parent::should_show_content($column, $user);
    }

    /**
    *   Adds content to the metabox column cell on the posts list page
    *   @param string $output                               Custom column output.
    *   @param string $columns                              Column name
    *   @param WP_User|int|null $user                       ID or instances of the user.
    */
    public function add_column_content($output, $column, $user){
        if($column != $this->id)
            return '';
        ob_start();
        $this->render_content($column, $user);
        return ob_get_clean();
    }

    public function get_object($user){
        return is_int($user) ? get_user_by( "ID", $user ) : $user;
    }

    static public function remove($filter_id,  $admin_pages, $columns_remove, $args = array()){
        parent::remove($filter_id, array("users"), $columns_remove, $args);
    }
}
