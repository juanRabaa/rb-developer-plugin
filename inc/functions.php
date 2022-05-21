<?php

function rb_array_every($array, $cb){
    $index = 0;
    foreach ($array as $key => $value) {
        if(!call_user_func($cb, $value, $key, $index))
            return false;
        $index++;
    }
    return true;
}

function rb_array_any($array, $cb){
    $index = 0;
    foreach ($array as $key => $value) {
        if(call_user_func($cb, $value, $key, $index))
            return true;
        $index++;
    }
    return false;
}

function pre_print(){
    echo "<pre>";
    foreach (func_get_args() as $value) {
        var_dump($value); echo "<br>";
    }
    echo "</pre>";
}

function rb_update_object_type_meta($args){
    $required_args = array(
        "object_id"                         => "",
        "object_type_rest_manager"          => null,
        "object_meta_rest_manager"          => null,
        "values"                            => array(), // meta values to save
    );
    extract(array_merge($required_args, $args));

    $schema = $object_type_rest_manager->get_item_schema();
    $meta_schema = $schema["properties"]["meta"]["properties"];
    $meta_args = array_filter($values, function($input_name) use ($meta_schema){
        return isset($meta_schema[$input_name]);
    }, ARRAY_FILTER_USE_KEY);

    foreach ($meta_args as $meta_key => $meta_value) {
        $meta_type = $meta_schema[$meta_key]["type"];
        // in some places (like menu items) the same meta name exists for multiple objects,
        // so the meta value it gets stored in an array in which the keys are the object ids.
        if(is_array($meta_value) && isset($meta_value[$object_id]))
            $meta_value = $meta_value[$object_id];

        //if($meta_type === "array" || $meta_type === "object") // TODO: Este check no es necesario, siempre se guarda como encoded json
        $meta_value = json_decode(wp_unslash($meta_value), true);

        $meta_args[$meta_key] = $meta_value;
    }

    if ( ! empty( $schema['properties']['meta'] ) && isset( $meta_args ) ) {
        $meta_update = $object_meta_rest_manager->update_value( $meta_args, $object_id );
        if ( is_wp_error( $meta_update ) ) {
            // return $meta_update;
        }
    }
}

function rb_update_term_meta($term_id, $taxonomy, $values){
    rb_update_object_type_meta(array(
        "object_id"                         => $term_id,
        "object_type_rest_manager"          => new WP_REST_Terms_Controller($taxonomy),
        "object_meta_rest_manager"          => new WP_REST_Term_Meta_Fields($taxonomy),
        "values"                            => $values, // meta values to save
    ));
}

function rb_update_post_meta($post_id, $post_type, $values){
    $object_type_rest_manager = null;
    if( $post_type === "nav_menu_item" )
        $object_type_rest_manager = new WP_REST_Menu_Items_Controller($post_type);
    else
        $object_type_rest_manager = new WP_REST_Posts_Controller($post_type);

    rb_update_object_type_meta(array(
        "object_id"                         => $post_id,
        "object_type_rest_manager"          => $object_type_rest_manager,
        "object_meta_rest_manager"          => new WP_REST_Post_Meta_Fields($post_type),
        "values"                            => $values, // meta values to save
    ));
}

function rb_update_user_meta($user_ID, $values){
    rb_update_object_type_meta(array(
        "object_id"                         => $user_ID,
        "object_type_rest_manager"          => new WP_REST_Users_Controller(),
        "object_meta_rest_manager"          => new WP_REST_User_Meta_Fields(),
        "values"                            => $values, // meta values to save
    ));
}

/**
*   Object lists columns
*   @see RB_Objects_List_Column::__construct
*   @param string $id                                                           Id of the column
*
*   @param string[]|string $admin_pages                                         String or array of strings representing the slug of the
*                                                                               wp object screens where to add the new column
*
*   @param string $title                                                        Title to show on the column header
*
*   @param callback $render_callback                                            The callback that renders the column content. This arguments
*                                                                               are passed through
*   {
*       @param string $column                                                   The column id
*       @param mixed|int|null $wp_object                                        The wordpress object
*   }
*
*   @param mixed[] $args                                                        Extra arguments. Accepts the following params
*   {
*       @param string $cell_class                                               Class for the div containing the cell content
*
*       @param int $position                                                    Position of the column on the list. Defaults to the last position
*                                                                               posible during runtime. First position is 0.
*
*       @param callback|null $should_add                                        A function that returns a bool indicating wheter the column should
*                                                                               be added or not. If the value is null, the column will always be added.
*   }
*/

function rb_add_posts_list_column($id, $admin_pages, $title, $render_callback, $args = array()){
    return new RB_Posts_List_Column($id, $admin_pages, $title, $render_callback, $args);
}

function rb_add_terms_list_column($id, $admin_pages, $title, $render_callback, $args = array()){
    return new RB_Terms_List_Column($id, $admin_pages, $title, $render_callback, $args);
}

function rb_add_users_list_column($id, $title, $render_callback, $args = array()){
    return new RB_Users_List_Column($id, $title, $render_callback, $args);
}


/**
*   @see RB_Objects_List_Column::remove
*/
function rb_remove_posts_list_column($filter_id, $posts_types, $columns_remove, $args = array()){
    return RB_Posts_List_Column::remove($filter_id, $posts_types, $columns_remove, $args);
}

function rb_remove_terms_list_column($filter_id, $taxonomies_names, $columns_remove, $args = array()){
    return RB_Terms_List_Column::remove($filter_id, $taxonomies_names, $columns_remove, $args);
}

function rb_remove_users_list_column($filter_id, $columns_remove, $args = array()){
    return RB_Users_List_Column::remove($filter_id, $columns_remove, $args);
}

/**
*   Puts a value into an array as its only item. If the value already is an array
*   it returns it.
*   @param mixed $value
*   @param bool $null_on_empty                                                  Wheter to return null if the value passed
*                                                                               is empty. If not, it will return an empty array.
*
*/
function rb_force_array($value, $null_on_empty = false){
    if(is_array($value))
        return $value;
    if(empty($value)){
        return $null_on_empty ? null : [];
    }
    return [$value];
}

/**
*   Returns true if a user has any of the roles passed.
*   @param WP_user $user
*   @param string[] $roles
*   @return bool
*/
function rb_user_has_any_role($user, $roles){
    return empty($roles) || rb_array_any( $roles, fn($role) => in_array($role, $user->roles) );
}

/**
*   Returns true if a user has all of the capabilities passed.
*   @param WP_user $user
*   @param string[] $capabilities
*   @return bool
*/
function rb_user_has_capabilities($user, $capabilities){
    return empty($capabilities) || rb_array_every($capabilities, fn($cap) => $user->has_cap($cap));
}
