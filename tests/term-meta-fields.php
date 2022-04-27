<?php

// RB_Term_Meta_Fields::add_field(array(
//     "meta_key"      => "sidebar_test_meta",
//     "taxonomy"      => "category",
//     "single"        => true,
//     "type"          => "object",
//     "panel"         => array(
//         "title"         => "Galerias",
//     ),
//     "field"        => $test_fields_data,
// ));

RB_Term_Meta_Fields::add_field(array(
    // https://make.wordpress.org/core/2019/10/03/wp-5-3-supports-object-and-array-meta-types-in-the-rest-api/
    "meta_key"      => "sidebar_test_meta",
    "taxonomy"      => "category",
    "single"        => true,
    "panel"         => array(
        "title"         => "Single Value Field",
        "icon"          => "format-gallery",
        "position"      => "document-settings-panel",
    ),
    "field"        => array(
        "type"          => "string",
        // "label"         => "Test Single",
        // "description"   => "Test Single",
        "component"     => "text",
    ),
));

RB_Term_Meta_Fields::add_field(array(
    "meta_key"      => "meta_test",
    "taxonomy"      => "category",
    "single"        => true,
    "type"          => "object",
    "panel"         => array(
        "title"         => "Galerias",
        "icon"          => "format-gallery",
        "position"      => "document-settings-panel",
    ),
    "field"        => $test_fields_data,
));
