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
