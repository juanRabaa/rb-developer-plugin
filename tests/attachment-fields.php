<?php

add_action( "rb-fields-object-post-ready", function() use ($test_fields_data, $simple_gallery_field){
    RB_Post_Meta_Fields_Manager::add_field(array(
        "meta_key"      => "sidebar_test_meta",
        "post_type"     => "attachment",
        "single"        => true,
        "type"          => "object",
        "column"		=> array(
            "title"			=> "Main Gallery",
            // "content"		=> "Columna",
        ),
        "panel"         => array(
            "title"         => "Galerias",
            "icon"          => "format-gallery",
            "position"      => "sidebar",
        ),
        "field"        => $test_fields_data,
    ));

    RB_Post_Meta_Fields_Manager::add_field(array(
        // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
        "meta_key"      => "single_meta_field",
        "post_type"     => "attachment",
        "single"        => true,
        "panel"         => array(
            "title"         => "Single Value Field",
            "icon"          => "format-gallery",
            "position"      => "document-settings-panel",
        ),
        "field"        => array(
            "type"          => "string",
            "label"         => "Test Single",
            "description"   => "Test Single",
            "component"     => "text",
        ),
    ));
});
