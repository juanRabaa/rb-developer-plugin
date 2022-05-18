<?php

add_action( "rb-fields-object-post-ready", function() use ($test_fields_data, $simple_gallery_field){
    RB_Post_Meta_Fields_Manager::add_field(array(
        // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
        "meta_key"      => "aaaaaaaa",
        "post_type"     => "nav_menu_item",
        "single"        => true,
        "panel"         => array(
            "title"         => "Single Value Field",
            "icon"          => "format-gallery",
            "position"      => "document-settings-panel",
        ),
        "field"        => array(
            "type"          => "string",
            "label"         => "Test Single",
            // "description"   => "Test Single",
            "component"     => "text",
        ),
    ));

    RB_Post_Meta_Fields_Manager::add_field(array(
        // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
        "meta_key"      => "menu_item_gallery",
        "post_type"     => "nav_menu_item",
        "object_kind"   => ["post", "custom"], // custom are custom link items
        "single"        => true,
        "panel"         => array(
            "title"         => "Single Value Field",
            "icon"          => "format-gallery",
            "position"      => "document-settings-panel",
        ),
        "field"        	=> array_merge($simple_gallery_field, array(
            "label"         => "Galeria",
        )),
    ));
});
