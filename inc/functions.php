<?php

function rb_update_object_type_meta($args){
    $required_args = array(
        "object_id"                         => "",
        "object_type_rest_manager"          => null,
        "object_meta_rest_manager"     => null,
        "values"                            => array(), // meta values to save
    );
    extract(array_merge($required_args, $args));

    $schema = $object_type_rest_manager->get_item_schema();
    $meta_schema = $schema["properties"]["meta"]["properties"];

    $meta_args = array_filter($values, function($input_name) use ($meta_schema){
        return isset($meta_schema[$input_name]);
    }, ARRAY_FILTER_USE_KEY);
    $meta_args = array_map(function($meta_value){
        return json_decode(wp_unslash($meta_value), true);
    }, $meta_args);

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
    rb_update_object_type_meta(array(
        "object_id"                         => $post_id,
        "object_type_rest_manager"          => new WP_REST_Posts_Controller($post_type),
        "object_meta_rest_manager"          => new WP_REST_Post_Meta_Fields($post_type),
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


/**
*   @see RB_Objects_List_Column::remove
*/
function rb_remove_posts_list_column($filter_id, $posts_types, $columns_remove, $args = array()){
    return RB_Posts_List_Column::remove($filter_id, $posts_types, $columns_remove, $args);
}

function rb_remove_terms_list_column($filter_id, $taxonomies_names, $columns_remove, $args = array()){
    return RB_Terms_List_Column::remove($filter_id, $taxonomies_names, $columns_remove, $args);
}
